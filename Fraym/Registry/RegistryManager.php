<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Registry;

/**
 * Class RegistryManager
 * @package Fraym\Registry
 * @Injectable(lazy=true)
 */
class RegistryManager
{
    const REPOSITORY_URL = 'http://fraym.org/repository';
    const UPDATE_SCHEMA_TRIGGER_FILE = '.UPDATE_SCHEMA';

    /**
     * @Inject
     * @var \Fraym\FileManager\FileManager
     */
    protected $fileManager;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @Inject
     * @var \Fraym\Core
     */
    protected $core;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    protected $request;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @var null
     */
    private $cache = null;

    /**
     * @var null
     */
    private $annotationReader = null;

    /**
     * @var null
     */
    private $cachedAnnotationReader = null;

    /**
     * @return $this
     */
    public function init()
    {
        if (APC_ENABLED && ENV !== \Fraym\Core::ENV_DEVELOPMENT) {
            $this->cache = new \Doctrine\Common\Cache\ApcuCache();
        } else {
            $this->cache = new \Doctrine\Common\Cache\ArrayCache;
        }

        $this->annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
        $this->cachedAnnotationReader = new \Doctrine\Common\Annotations\CachedReader($this->annotationReader, $this->cache);

        \Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
            'Fraym\Annotation',
            $this->core->getApplicationDir()
        );
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(
            function ($class) {
                return class_exists($class, true);
            }
        );
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
            $this->core->getApplicationDir() . "/Fraym/Annotation/Registry.php"
        );

        // For extension update, run always update schema
        if (is_file(self::UPDATE_SCHEMA_TRIGGER_FILE)) {
            $this->db->connect()->updateSchema();
            unlink(self::UPDATE_SCHEMA_TRIGGER_FILE);
        }

        return $this;
    }

    /**
     * @param $class
     * @return null|object
     */
    private function getRegistryConfig($class)
    {
        $reflClass = new \ReflectionClass($class);
        $classAnnotation = $this->getAnnotationReader()->getClassAnnotation(
            $reflClass,
            'Fraym\Annotation\Registry'
        );

        if (is_object($classAnnotation) && $classAnnotation->file !== null) {
            $filePath = substr($classAnnotation->file, 0, 1) === '/' ?
                $classAnnotation->file :
                dirname($reflClass->getFileName()) . DIRECTORY_SEPARATOR . $classAnnotation->file;

            if (is_file($filePath)) {
                $config = require($filePath);
                if (is_array($config)) {
                    $config = array_merge($this->getRegistryProperties(), $config);
                    return (object)$config;
                }
            }
        }

        return $classAnnotation;
    }

    /**
     * @return array
     */
    private function getRegistryProperties()
    {
        $reflRegClass = new \ReflectionClass('Fraym\Annotation\Registry');
        $properties = $reflRegClass->getProperties(\ReflectionProperty::IS_PUBLIC);
        $result = array();
        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue(new \Fraym\Annotation\Registry(array()));
        }
        return $result;
    }

    /**
     * Register all unregistred extensions
     */
    public function registerExtensions()
    {
        $unregisteredExtensions = $this->getUnregisteredExtensions();

        foreach ($unregisteredExtensions as $class => $classAnnotation) {
            require_once($classAnnotation['file']);
            if (class_exists($class)) {
                $this->registerClass($class, $classAnnotation['file']);
            }
        }

        foreach ($unregisteredExtensions as $class => $classAnnotation) {
            if (class_exists($class)) {
                $classAnnotation = $this->getRegistryConfig($class);

                if ($classAnnotation) {
                    if ($classAnnotation->afterRegister) {
                        call_user_func_array(
                            array($this->serviceLocator->get($class), $classAnnotation->afterRegister),
                            array($classAnnotation)
                        );
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getUnregisteredExtensions() {
        $unregExtensions = array();
        foreach($this->getExtensions() as $class => $ext) {
            if($ext['registred'] === false) {
                $unregExtensions[$class] = $ext;
            }
        }
        return $unregExtensions;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        $extensions = array();
        $coreFiles = $this->fileManager->findFiles(
            $this->core->getApplicationDir() . DIRECTORY_SEPARATOR . 'Fraym' . DIRECTORY_SEPARATOR . '*.php'
        );
        $extensionFiles = $this->fileManager->findFiles(
            $this->core->getApplicationDir() . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR . '*.php'
        );
        $files = array_merge($coreFiles, $extensionFiles);

        foreach ($files as $file) {
            $classname = basename($file, '.php');
            $namespace = str_ireplace($this->core->getApplicationDir(), '', dirname($file));
            $namespace = str_replace('/', '\\', $namespace) . '\\';
            $class = $namespace . $classname;

            if (is_file($file)) {
                require_once($file);

                if (class_exists($class)) {
                    $classAnnotation = $this->getRegistryConfig($class);
                    if ($classAnnotation) {
                        $registryEntry = $this->db->getRepository(
                            '\Fraym\Registry\Entity\Registry'
                        )->findOneByClassName($class);
                        $extensions[$class] = (array)$classAnnotation;
                        $extensions[$class]['file'] = $file;
                        $extensions[$class]['fileHash'] = md5($file);
                        $extensions[$class]['registred'] = $registryEntry !== null;
                    }
                }
            }
        }
        return $extensions;
    }

    /**
     * @param $repositoryKey
     */
    public function updateExtension($repositoryKey)
    {
        $registry = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findOneByRepositoryKey($repositoryKey);

        $reflClass = new \ReflectionClass($registry->className);
        $classAnnotation = $this->getRegistryConfig($registry->className);

        $this->registerClass($registry->className, $reflClass->getFileName(), $registry);

        if ($classAnnotation->onUpdate) {
            call_user_func_array(
                array($this->serviceLocator->get($registry->className), $classAnnotation->onUpdate),
                array($classAnnotation, $registry)
            );
        }
    }

    /**
     * @param Entity\Registry $registry
     * @return bool|string
     */
    public function buildPackage(Entity\Registry $registry)
    {
        $classAnnotation = $this->getRegistryConfig($registry->className);

        $files = array();

        foreach ($classAnnotation->files as $path) {
            $files = array_merge($files, $this->fileManager->findFiles($path));
        }

        $filename = tempnam(sys_get_temp_dir(), 'ext');
        $zip = new \ZipArchive();

        if ($zip->open($filename, \ZipArchive::CREATE) !== true) {
            return false;
        }

        foreach ($files as $file) {
            if (is_dir($file)) {
                $zip->addEmptyDir($file);
            } else {
                $zip->addFile($file);
            }
        }
        $zip->close();
        return $filename;
    }

    /**
     * @param $repositoryKey
     * @return bool
     */
    public function downloadExtension($repositoryKey)
    {
        $download = $this->repositoryRequest(
            'getDownloadLink',
            array('repositoryKey' => $repositoryKey)
        );

        $extensionContent = file_get_contents($download->downloadLink);
        $tmpFile = tempnam(sys_get_temp_dir(), 'ext');
        file_put_contents($tmpFile, $extensionContent);
        $zip = new \ZipArchive;
        if ($zip->open($tmpFile) === true) {
            if ($zip->extractTo('./') === false) {
                error_log('Zip error!');
            }
            $zip->close();
            unlink($tmpFile);

            $this->forceSchemaUpdate();

            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    private function forceSchemaUpdate()
    {
        return file_put_contents(self::UPDATE_SCHEMA_TRIGGER_FILE, 0755);
    }

    /**
     * Inlcude composer
     */
    public function loadComposer() {
        if(!class_exists(\Composer\Console\Application::class)) {
            \Phar::loadPhar('composer.phar', 'composer.phar');
            require_once 'phar://composer.phar/src/bootstrap.php';
        }
    }

    /**
     * @param $extension
     */
    public function composerRequire($extension) {
        $this->loadComposer();
        if(isset($extension->composer) && isset($extension->composer['require'])) {
            $input = new \Symfony\Component\Console\Input\ArrayInput(array('command' => 'require', 'packages' => $extension->composer['require']));
            $application = new \Composer\Console\Application();
            $application->setAutoExit(false);
            $application->run($input);
        }
    }

    /**
     * Update composer dependencies
     */
    public function composerUpdate() {
        $this->loadComposer();
        $input = new \Symfony\Component\Console\Input\ArrayInput(array('command' => 'update'));
        $application = new \Composer\Console\Application();
        $application->setAutoExit(false);
        $application->run($input);
    }

    /**
     * Remove composer dependencies
     *
     * @param $extension
     */
    public function composerRemove($extension) {
        $this->loadComposer();
        if(isset($extension->composer) && isset($extension->composer['require'])) {
            $input = new \Symfony\Component\Console\Input\ArrayInput(array('command' => 'remove', 'packages' => $extension->composer['require']));
            $application = new \Composer\Console\Application();
            $application->setAutoExit(false);
            $application->run($input);
        }
    }

    /**
     * @param $class
     * @param $file
     * @param null $oldRegistryEntry
     * @return bool
     */
    public function registerClass($class, $file, $oldRegistryEntry = null)
    {
        require_once($file);

        $extension = $this->getRegistryConfig($class);

        if ($extension) {
            $this->composerRequire($extension);

            $registryEntry = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findOneByClassName($class);

            $this->db->updateSchema();

            if ($registryEntry === null) {
                $registryEntry = new \Fraym\Registry\Entity\Registry();
            }

            $registryEntry = $this->updateRegistryEntry($registryEntry, $class, $extension);

            $this->createEntities($registryEntry, $extension, $oldRegistryEntry);

            if ($extension->onRegister) {
                call_user_func_array(
                    array($this->serviceLocator->get($class), $extension->onRegister),
                    array($extension, $oldRegistryEntry)
                );
            }

            return true;

        }
        return false;
    }

    /**
     * @param Entity\Registry $registryEntry
     * @param $className
     * @param $classAnnotation
     * @return Entity\Registry
     */
    private function updateRegistryEntry(Entity\Registry $registryEntry, $className, $classAnnotation)
    {
        $registryEntry->className = $className;
        $registryEntry->deletable = $classAnnotation->deletable;
        $registryEntry->name = $classAnnotation->name;
        $registryEntry->version = $classAnnotation->version;
        $registryEntry->author = $classAnnotation->author;
        $registryEntry->website = $classAnnotation->website;
        $registryEntry->repositoryKey = $classAnnotation->repositoryKey;

        $this->db->persist($registryEntry);
        $this->db->flush();
        return $registryEntry;
    }

    /**
     * @param $id
     * @return bool
     */
    public function unregisterExtension($id)
    {
        $entry = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findOneById($id);
        if ($entry) {
            $className = $entry->className;
            $this->db->remove($entry);
            $this->db->flush();

            $unregisteredExtensions = $this->getUnregisteredExtensions();
            $extension = $unregisteredExtensions[$className];
            $this->composerRemove($extension);
            $this->removeEntities($extension);
            if (isset($extension->onUnregister)) {
                call_user_func_array(
                    array($this->serviceLocator->get($className), $extension->onUnregister),
                    array($extension)
                );
            }

            return true;
        }
        return false;
    }
    
    /**
     * @param $extension
     * @return $this
     */
    public function removeExtensionFiles($extension)
    {
        foreach ($extension['files'] as $path) {
            foreach (glob($path) as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        foreach ($extension['files'] as $path) {
            foreach (glob($path) as $file) {
                if (is_dir($file)) {
                    rmdir($file);
                }
            }
        }
        return $this;
    }

    /**
     * @param $updateData
     * @param $orgData
     * @return array
     */
    private function getEntityDataForUpdate($updateData, $orgData)
    {
        foreach ($orgData as $entityData) {
            $diff = array_diff($entityData, $updateData);
            if (count($entityData) - count($diff) === count($updateData)) {
                return $entityData;
            }
        }
        return array();
    }

    /**
     * Create the table entries from the registry annotation
     *
     * @param $registry
     * @param $classAnnotation
     * @param $oldRegistryEntry
     * @return $this
     */
    private function createEntities($registry, $classAnnotation, $oldRegistryEntry = null)
    {
        if (count($classAnnotation->entity)) {
            foreach ($classAnnotation->entity as $className => $entries) {
                if ($oldRegistryEntry !== null && isset($classAnnotation->updateEntity[$className])) {
                    foreach ($classAnnotation->updateEntity[$className] as $entryData) {
                        $entry = $this->getEntity($className, $entryData);
                        if ($entry) {
                            $entryDataWithSubEntries['registry'] = $registry->id;
                            $data = $this->getEntityDataForUpdate($entryData, $classAnnotation->entity[$className]);
                            $entry->updateEntity($data);
                        }
                    }
                } elseif ($oldRegistryEntry === null) {
                    foreach ($entries as $entryData) {
                        $entryDataWithSubEntries = $this->getSubEntries($entryData, $registry);
                        if ($this->getEntity($className, $entryDataWithSubEntries) === null) {
                            $entry = new $className();
                            $entryDataWithSubEntries['registry'] = $registry->id;
                            $entry->updateEntity($entryDataWithSubEntries);
                        }
                    }
                }
            }
        }

        if (count($classAnnotation->config)) {
            foreach ($classAnnotation->config as $configName => $data) {
                $className = '\Fraym\Registry\Entity\Config';
                $data['name'] = $configName;
                $data['registry'] = $registry;
                $entity = $this->db->getRepository($className)->findOneByName($configName);
                if ($entity === null) {
                    $entry = new $className();
                    $entry->updateEntity($data);
                }
            }
        }

        if (count($classAnnotation->translation)) {
            foreach ($classAnnotation->translation as $translation) {
                $defaultValue = $translation[0];
                $key = isset($translation[1]) ? $translation[1] : $defaultValue;
                $locale = isset($translation[2]) ? $translation[2] : 'en_US';
                $this->translation->createTranslation($key, $defaultValue, $this->locale->getLocale()->locale, $locale);
            }
        }
        return $this;
    }

    /**
     * @param $entryData
     * @param $registry
     * @return mixed
     */
    private function getSubEntries($entryData, $registry)
    {
        foreach ($entryData as &$val) {
            if (is_array($val)) {
                foreach ($val as $className => &$subEntryData) {
                    $subEntry = $this->getEntity($className, $subEntryData);
                    if ($subEntry === null) {
                        $entry = new $className();
                        $subEntryData['registry'] = $registry->id;
                        $entry->updateEntity($subEntryData);
                        $val = $entry->id;
                        break;
                    } else {
                        $val = $subEntry->id;
                        break;
                    }
                }
            }
        }

        return $entryData;
    }

    /**
     * @param $classAnnotation
     * @return $this
     */
    private function removeEntities($classAnnotation)
    {
        $classAnnotation = (object)$classAnnotation;
        if (count($classAnnotation->entity)) {
            foreach ($classAnnotation->entity as $className => $entries) {
                foreach ($entries as $entryData) {
                    if ($entry = $this->getEntity($className, $entryData)) {
                        $this->db->remove($entry);
                    }
                }
            }
        }

        if (count($classAnnotation->config)) {
            $className = '\Fraym\Registry\Entity\Config';
            foreach ($classAnnotation->config as $configName => $data) {
                if (!isset($data['deletable']) || $data['deletable'] === true) {
                    $entry = $this->db->getRepository($className)->findOneByName($configName);
                    if ($entry) {
                        $this->db->remove($entry);
                    }
                }
            }
        }
        $this->db->flush();
        return $this;
    }

    /**
     * Get a entity with parameters
     *
     * @param $className
     * @param $entryData
     * @return mixed
     */
    private function getEntity($className, $entryData)
    {
        foreach($entryData as $k => &$data) {
            if(is_array($data)) {
                $subEntryData = reset($data);
                $subEntryClassName = key($data);
                $data = $this->db->getRepository($subEntryClassName)->findOneBy($subEntryData);
            }
        }
        return $this->db->getRepository($className)->findOneBy($entryData);
    }

    /**
     * API request do get all modules
     */
    public function repositoryRequest($cmd = 'listModules', $params = array())
    {
        $query = array(
            'cmd' => $cmd,
            'channel' => 'stable',
            'client_info' => array(
                'version' => \Fraym\Core::VERSION,
                'php_version' => phpversion(),
                'os' => php_uname('s'),
                'apc_enabled' => APC_ENABLED,
                'image_processor' => IMAGE_PROCESSOR,
                'env' => ENV,
            )
        );

        $query = array_merge($query, $params);
        return $this->sendRepositoryRequest($query);
    }

    /**
     * @param null $params
     * @return bool|mixed|\SimpleXMLElement|string
     */
    public function sendRepositoryRequest($params = null)
    {
        $url = self::REPOSITORY_URL;
        return $this->request->send($url, $params);
    }

    /**
     * @return \Doctrine\Common\Annotations\CachedReader
     */
    public function getAnnotationReader()
    {
        return $this->cachedAnnotationReader;
    }

    /**
     * @param $extensions
     * @return bool|mixed|\SimpleXMLElement|string
     */
    public function getUpdates($extensions)
    {
        $extensionsKeys = array();
        foreach ($extensions as $extension) {
            if ($extension->repositoryKey) {
                $extensionsKeys[$extension->repositoryKey] = $extension->version;
            }
        }

        return $this->repositoryRequest('getUpdates', array('extensions' => $extensionsKeys));
    }
}
