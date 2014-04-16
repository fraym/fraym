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
            $this->cache = new \Doctrine\Common\Cache\ApcCache();
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
                $reflClass = new \ReflectionClass($class);
                $classAnnotation = $this->getAnnotationReader()->getClassAnnotation(
                    $reflClass,
                    'Fraym\Annotation\Registry'
                );
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
    public function getUnregisteredExtensions()
    {
        $unregisteredExtensions = array();
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
                    $reflClass = new \ReflectionClass($class);
                    $classAnnotation = $this->getAnnotationReader()->getClassAnnotation(
                        $reflClass,
                        'Fraym\Annotation\Registry'
                    );
                    if ($classAnnotation) {
                        $registryEntry = $this->db->getRepository(
                            '\Fraym\Registry\Entity\Registry'
                        )->findOneByClassName($class);
                        if ($registryEntry === null) {
                            $unregisteredExtensions[$class] = (array)$classAnnotation;
                            $unregisteredExtensions[$class]['file'] = $file;
                            $unregisteredExtensions[$class]['fileHash'] = md5($file);
                        }
                    }
                }
            }
        }
        return $unregisteredExtensions;
    }

    /**
     * @param $repositoryKey
     */
    public function updateExtension($repositoryKey) {
        $registry = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findOneByRepositoryKey($repositoryKey);

        $this->downloadExtension($repositoryKey);

        $reflClass = new \ReflectionClass($registry->className);
        $classAnnotation = $this->getAnnotationReader()->getClassAnnotation(
            $reflClass,
            'Fraym\Annotation\Registry'
        );

        $this->registerClass($registry->className, $reflClass->getFileName());

        if ($classAnnotation->onUpdate) {
            call_user_func_array(
                array($this->serviceLocator->get($registry->className), $classAnnotation->onUpdate),
                array($classAnnotation)
            );
        }
    }

    /**
     * @param Entity\Registry $registry
     * @return bool|string
     */
    public function buildPackage(Entity\Registry $registry) {
        $reflClass = new \ReflectionClass($registry->className);
        $classAnnotation = $this->getAnnotationReader()->getClassAnnotation(
            $reflClass,
            'Fraym\Annotation\Registry'
        );

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
            if(is_dir($file)) {
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
    public function downloadExtension($repositoryKey) {
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
    private function forceSchemaUpdate() {
        return file_put_contents(self::UPDATE_SCHEMA_TRIGGER_FILE, 0755);
    }

    /**
     * @param $class
     * @param $file
     * @return bool
     */
    public function registerClass($class, $file)
    {
        require_once($file);

        $reflClass = new \ReflectionClass($class);
        $classAnnotation = $this->getAnnotationReader()->getClassAnnotation(
            $reflClass,
            'Fraym\Annotation\Registry'
        );

        if ($classAnnotation) {
            $registryEntry = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findOneByClassName($class);

            $this->db->updateSchema();

            if ($registryEntry === null) {
                $registryEntry = new \Fraym\Registry\Entity\Registry();
            }

            $registryEntry = $this->updateRegistryEntry($registryEntry, $class, $classAnnotation);

            $this->createEntities($registryEntry, $classAnnotation);

            if ($classAnnotation->onRegister) {
                call_user_func_array(
                    array($this->serviceLocator->get($class), $classAnnotation->onRegister),
                    array($classAnnotation)
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
            $this->db->remove($entry);
            $this->db->flush();

            $unregisteredExtensions = $this->getUnregisteredExtensions();

            foreach ($unregisteredExtensions as $class => $classAnnotation) {
                $this->removeEntities($classAnnotation);

                if (isset($classAnnotation->onUnregister)) {
                    call_user_func_array(
                        array($this->serviceLocator->get($class), $classAnnotation->onUnregister),
                        array($classAnnotation)
                    );
                }
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
     * Create the table entries from the registry annotation
     *
     * @param $registry
     * @param $classAnnotation
     * @return $this
     */
    private function createEntities($registry, $classAnnotation)
    {

        if (count($classAnnotation->entity)) {
            foreach ($classAnnotation->entity as $className => $entries) {
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
                if(!isset($data['deletable']) || $data['deletable'] === true) {
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
