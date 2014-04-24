<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Install;

use Fraym\Annotation\Registry;

/**
 * Class InstallController
 * @package Fraym\Install
 * @Registry(
 * name="Fraym Core",
 * version="0.9.0",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * entity={
 *      "\Fraym\SiteManager\Entity\Extension"={
 *          {
 *           "name"="Menu editor",
 *           "class"="\Fraym\Menu\MenuController",
 *           "method"="getContent",
 *           "active"="1",
 *           "description"="EXT_EXTENSION_SITEMENUEDITOR_DESC"
 *           },
 *          {
 *           "name"="Data Manager",
 *           "class"="\Fraym\EntityManager\EntityManagerController",
 *           "method"="getContent",
 *           "active"="1",
 *           "description"="EXT_EXTENSION_ENTITYMANAGER_DESC"
 *           },
 *          {
 *           "name"="File Manager",
 *           "class"="\Fraym\FileManager\FileManagerController",
 *           "method"="getContent",
 *           "active"="1",
 *           "description"="EXT_EXTENSION_FILEMANAGER_DESC"
 *           },
 *          {
 *           "name"="Package Manager",
 *           "class"="\Fraym\Registry\RegistryManagerController",
 *           "method"="getContent",
 *           "active"="1",
 *           "description"="EXT_EXTENSION_PACKAGEMANAGER_DESC"
 *           },
 *      },
 *      "\Fraym\Route\Entity\VirtualRoute"={
 *         {
 *           "key"="menuControllerAjax",
 *           "route"="/fraym/admin/menu/ajax",
 *           "class"="\Fraym\Menu\MenuController",
 *           "method"="ajaxHandler"
 *         },
 *         {
 *           "key"="block",
 *           "route"="/fraym/admin/block",
 *           "class"="\Fraym\Block\BlockController",
 *           "method"="renderBlock"
 *         },
 *         {
 *           "key"="adminPanel",
 *           "route"="/fraym/admin/adminpanel",
 *           "class"="\Fraym\SiteManager\SiteManagerController",
 *           "method"="getAdminPanel"
 *         },
 *         {
 *           "key"="fileViewer",
 *           "route"="/fraym/admin/fileViewer",
 *           "class"="\Fraym\FileManager\FileManagerController",
 *           "method"="fileViewer"
 *         },
 *         {
 *           "key"="fileManager",
 *           "route"="/fraym/admin/filemanager",
 *           "class"="\Fraym\FileManager\FileManagerController",
 *           "method"="getContent"
 *         },
 *         {
 *           "key"="menuSelection",
 *           "route"="/fraym/admin/menu/selection",
 *           "class"="\Fraym\Menu\MenuController",
 *           "method"="getContent"
 *         },
 *         {
 *           "key"="adminLogin",
 *           "route"="/fraym",
 *           "class"="\Fraym\User\UserController",
 *           "method"="renderAdminPage"
 *         },
 *         {
 *           "key"="registryManagerDownload",
 *           "route"="/fraym/registry/download",
 *           "class"="\Fraym\Registry\RegistryManagerController",
 *           "method"="downloadPackage"
 *         },
 *      },
 *      "\Fraym\EntityManager\Entity\Entity"={
 *          {
 *           "className"="\Fraym\User\Entity\User",
 *           "name"="User entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="User"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\User\Entity\Group",
 *           "name"="Usergroup entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="User"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\Template\Entity\Template",
 *           "name"="Menu template entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="Template"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\Block\Entity\BlockTemplate",
 *           "name"="Block template entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="Template"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\Site\Entity\Site",
 *           "name"="Website entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="Website"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\Site\Entity\Domain",
 *           "name"="Domain entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="Website"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\Translation\Entity\Translation",
 *           "name"="Translation entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="Translation"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\Registry\Entity\Config",
 *           "name"="Config entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="Config"
 *                      }
 *                   },
 *           },
 *          {
 *           "className"="\Fraym\Locale\Entity\Locale",
 *           "name"="Locale",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="Website"
 *                      }
 *                   },
 *           }
 *      },
 *      "\Fraym\Block\Entity\BlockExtension"={
 *          {
 *           "name"="Menu",
 *           "description"="Add a website menu to your site.",
 *           "class"="\Fraym\Menu\MenuController",
 *           "configMethod"="getBlockConfig",
 *           "execMethod"="execBlock",
 *           "saveMethod"="saveBlockConfig"
 *           },
 *          {
 *           "name"="Container",
 *           "description"="Add a static content to your website.",
 *           "class"="\Fraym\Block\Block",
 *           "execMethod"="execBlock"
 *           },
 *          {
 *           "name"="User",
 *           "description"="Adds a LogIn / LogOut form to your website.",
 *           "class"="\Fraym\User\User",
 *           "configMethod"="getBlockConfig",
 *           "execMethod"="execBlock",
 *           "saveMethod"="saveBlockConfig"
 *           },
 *          {
 *           "name"="Image",
 *           "description"="Add a image to your website.",
 *           "class"="\Fraym\Image\Image",
 *           "configMethod"="getBlockConfig",
 *           "execMethod"="execBlock",
 *           "saveMethod"="saveBlockConfig"
 *           },
 *      }
 * },
 * config={
 *      "ADMIN_GROUP_IDENTIFIER"={"value"="GROUP:Administrator", "description"="The identifier for the Administrator group.", "deletable"=false},
 *      "ADMIN_LOCALE_ID"={"value"="1", "description"="The default administrator locale.", "deletable"=false},
 *      "TRANSLATION_AUTO"={"value"="1", "description"="Set to 1 for enable auto translation or to 0 to disable auto translation.", "deletable"=false},
 *      "IMAGE_PATH"={"value"="images/deposit", "description"="The save path of the converted images.", "deletable"=false},
 *      "FILEMANAGER_STORAGES"={"value"="Template,Public/images", "description"="The folders that are mapped in the file manager. You can seperate multiple folder with comma.", "deletable"=false},
 * },
 * files={
 *      "Fraym/*",
 *      "Fraym/",
 *      "Template/Default/Fraym/*",
 *      "Template/Default/Fraym/",
 *      "Test/Fraym/*",
 *      "Test/Fraym/",
 *      "Public/images/fraym/*",
 *      "Public/images/fraym/",
 *      "Public/css/fraym/*",
 *      "Public/css/fraym/",
 *      "Public/fonts/arial.ttf",
 *      "Public/css/install/*",
 *      "Public/css/install/",
 *      "Public/js/fraym/*",
 *      "Public/js/fraym/",
 *      "Public/index.php",
 *      "Public/install.php",
 *      "Bootstrap.php",
 *      "phpunit.xml",
 *      "CHANGELOG.txt",
 *      "COPYRIGHT.txt",
 *      "LICENSE.txt",
 *      "README.txt",
 *      "Vendor/Detection/*",
 *      "Vendor/Detection/",
 *      "Vendor/DI/*",
 *      "Vendor/DI/",
 *      "Vendor/Interop/*",
 *      "Vendor/Interop/",
 *      "Vendor/Doctrine/*",
 *      "Vendor/Doctrine/",
 *      "Vendor/DoctrineExtensions/*",
 *      "Vendor/DoctrineExtensions/",
 *      "Vendor/Elasticsearch/*",
 *      "Vendor/Elasticsearch/",
 *      "Vendor/Gedmo/*",
 *      "Vendor/Gedmo/",
 *      "Vendor/Guzzle/*",
 *      "Vendor/Guzzle/",
 *      "Vendor/Imagine/*",
 *      "Vendor/Imagine/",
 *      "Vendor/Monolog/*",
 *      "Vendor/Monolog/",
 *      "Vendor/MyCLabs/*",
 *      "Vendor/MyCLabs/",
 *      "Vendor/PhpDocReader/*",
 *      "Vendor/PhpDocReader/",
 *      "Vendor/PHPUnit/*",
 *      "Vendor/PHPUnit/",
 *      "Vendor/ProxyManager/*",
 *      "Vendor/ProxyManager/",
 *      "Vendor/Psr/*",
 *      "Vendor/Psr/",
 *      "Vendor/Swift/*",
 *      "Vendor/Swift/",
 *      "Vendor/Swift/*",
 *      "Vendor/Swift/",
 *      "Vendor/Symfony/*",
 *      "Vendor/Symfony/",
 *      "Vendor/Zend/*",
 *      "Vendor/Zend/",
 *      "Vendor/Pimple.php",
 * },
 * deletable=false,
 * repositoryKey="FRAYM"
 * )
 * @Injectable(lazy=true)
 */
class InstallController extends \Fraym\Core
{

    /**
     * @Inject
     * @var \Fraym\Mail\Mail
     */
    protected $mail;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Registry\RegistryManager
     */
    protected $registry;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

    /**
     * @Inject
     * @var \Fraym\Registry\Config
     */
    protected $config;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @var string
     */
    private $_configFile = 'Config.php';

    public function setup()
    {
        if (is_file($this->_configFile) && filesize($this->_configFile) > 0) {
            $this->response->send('Fraym is already installed!')->sendHTTPStatusCode(404)->finish();
        }

        $this->view->assign('timezones', $this->getTimezones());
        $this->view->assign('done', false);
        $this->view->assign('error', false);
        $this->view->assign('post', $this->request->getGPAsObject());

        if ($this->request->isPost()) {
            $cmd = $this->request->post('cmd');
            if ($cmd === 'checkDatabase') {
                $this->checkDatabase();
            } elseif ($result = $this->install()) {
                $this->view->assign('done', true);
            }
        }

        $this->view->setTemplate('Install')->render();
    }

    private function checkDatabase()
    {
        $post = $this->request->getGPAsObject();
        define('FRAYM_INSTANCE', time());
        define('DB_HOST', $post->database->host);
        define('DB_USER', $post->database->user);
        define('DB_PASS', $post->database->password);
        define('DB_DRIVER', $post->database->type);
        define('DB_CHARSET', 'UTF8');
        define('DB_PORT', $post->database->port);
        define('DB_NAME', $post->database->name);
        define('DB_TABLE_PREFIX', $post->database->prefix);

        $this->serviceLocator->set(
            'db.options',
            array(
                'driver' => DB_DRIVER,
                'user' => DB_USER,
                'password' => DB_PASS,
                'host' => DB_HOST,
                'dbname' => DB_NAME,
                'charset' => DB_CHARSET
            )
        );

        try {
            $this->db->connect()->getEntityManager()->getConnection()->connect();
        } catch (\Exception $e) {
            $this->response->sendAsJson(array('error' => $e->getMessage()));
        }

        $this->response->sendAsJson();
    }

    /**
     * @return mixed
     */
    public function getTimezones()
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

    private function install()
    {
        if ($this->writeConfig($this->_configFile)) {
            // Disable max script exec time, because creating database shema takes some time
            set_time_limit(0);
            include_once($this->_configFile);

            $this->serviceLocator->set(
                'db.options',
                array(
                    'driver' => DB_DRIVER,
                    'user' => DB_USER,
                    'password' => DB_PASS,
                    'host' => DB_HOST,
                    'dbname' => DB_NAME,
                    'charset' => DB_CHARSET
                )
            );

            @unlink($this->db->getModuleDirCacheFile());

            $this->db->connect()->getSchemaTool()->dropDatabase();
            $this->db->createSchema();
            if (($errors = $this->initConfigurations()) !== true) {
                $this->view->assign('error', implode('<br />', $errors));
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @return array|bool
     */
    private function initConfigurations()
    {

        $gp = $this->request->getGPAsObject();
        $errors = array();

        /**
         * create default language
         */
        $locale = new \Fraym\Locale\Entity\Locale();
        switch ($gp->locale) {
            case 'german':
                $locale->name = 'German';
                $locale->locale = 'de_DE';
                $locale->country = 'Germany';
                $locale->default = true;
                break;
            case 'french':
                $locale->name = 'French';
                $locale->locale = 'fr_FR';
                $locale->country = 'France';
                $locale->default = true;
                break;
            case 'swedish':
                $locale->name = 'swedish';
                $locale->locale = 'sv_SE';
                $locale->country = 'Sweden';
                $locale->default = true;
                break;
            case 'spanish':
                $locale->name = 'Spanish';
                $locale->locale = 'es_ES';
                $locale->country = 'Spain';
                $locale->default = true;
                break;
            default: // english
                $locale->name = 'English';
                $locale->locale = 'en_US';
                $locale->country = 'USA';
                $locale->default = true;
                break;
        }
        $this->db->persist($locale);
        $this->db->flush();

        $this->locale->setLocale($locale);
        $this->db->setUpTranslateable()->setUpSortable();

        /**
         * create site
         */
        $site = new \Fraym\Site\Entity\Site();
        $site->name = $gp->site->name;
        $site->caching = true;
        $site->active = true;
        $site->menuItems->clear();
        $this->db->persist($site);

        /**
         * create domain for site
         */
        $domain = new \Fraym\Site\Entity\Domain();
        $domain->site = $site;
        $domain->address = $gp->site->url;
        $this->db->persist($domain);

        $this->addMenuItems($site);

        $adminGroup = new \Fraym\User\Entity\Group();
        $adminGroup->name = $this->translation->autoTranslation('Administrator', 'en', $gp->locale);
        $adminGroup->identifier = 'Administrator';
        $this->db->persist($adminGroup);

        $adminUser = new \Fraym\User\Entity\User();
        $adminUser->updateEntity($gp->user, false);

        if (strlen($gp->user->password) < 8) {
            $errors[] = 'Password is too short.';
        } elseif ($gp->user->password === $gp->user->password_repeat) {
            $adminUser->password = $gp->user->password;
        } else {
            $errors[] = 'Passwords do not match.';
        }

        $adminUser->groups->add($adminGroup);

        $this->db->persist($adminUser);

        if (count($errors) === 0) {

            $this->db->flush();
            $this->db->clear();

            /**
             * Register extensions, default theme...
             */
            $this->registry->registerExtensions();

            /**
             * Set menuitem template -> default theme
             */
            $this->setupMenuItemTemplate();

            /**
             * Login admin user
             */
            $this->user->setUserId($adminUser->id);
            return true;
        }

        return $errors;
    }

    private function setupMenuItemTemplate()
    {
        /**
         * set default layout template
         */
        $tpl = $this->db->getRepository('\Fraym\Template\Entity\Template')->findOneById(1);
        if (!$tpl) {
            throw new \Exception('No default theme found! Please add a theme extension.');
        }

        $menuItems = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findAll();
        foreach ($menuItems as $menuItem) {
            $menuItem->template = $tpl;
        }
        return $this;
    }

    private function addMenuItems($site)
    {
        $gp = $this->request->getGPAsObject();

        /**
         * Root Page
         */
        $pageRoot = new \Fraym\Menu\Entity\MenuItem();
        $pageRoot->site = $site;
        $pageRoot->caching = true;
        $pageRoot->visible = true;
        $pageRoot->active = true;
        $pageRoot->https = false;
        $pageRoot->checkPermission = false;
        $pageRoot->is404 = false;

        $pageRootTranslation = new \Fraym\Menu\Entity\MenuItemTranslation();
        $pageRootTranslation->menuItem = $pageRoot;
        $pageRootTranslation->title = $this->translation->autoTranslation('Home', 'en', $gp->locale);
        $pageRootTranslation->subtitle = $this->translation->autoTranslation(
            'Welcome to my website.',
            'en',
            $gp->locale
        );
        $pageRootTranslation->url = "";
        $pageRootTranslation->shortDescription = $this->translation->autoTranslation(
            'My short website description',
            'en',
            $gp->locale
        );
        $pageRootTranslation->longDescription = $this->translation->autoTranslation(
            'My long website description',
            'en',
            $gp->locale
        );
        $pageRootTranslation->externalUrl = false;
        $this->db->persist($pageRootTranslation);

        /**
         * 404 Page
         */
        $newPage = new \Fraym\Menu\Entity\MenuItem();
        $newPage->site = $site;
        $newPage->caching = true;
        $newPage->visible = false;
        $newPage->active = true;
        $newPage->https = false;
        $newPage->checkPermission = false;
        $newPage->is404 = true;
        $newPage->parent = $pageRoot;

        $newPageTranslation = new \Fraym\Menu\Entity\MenuItemTranslation();
        $newPageTranslation->menuItem = $newPage;
        $newPageTranslation->title = $this->translation->autoTranslation('404 Page not found', 'en', $gp->locale);
        $newPageTranslation->subtitle = '';
        $newPageTranslation->url = '/' . $this->translation->autoTranslation('error', 'en', $gp->locale) . '-404';
        $newPageTranslation->shortDescription = $this->translation->autoTranslation(
            '404 Page not found',
            'en',
            $gp->locale
        );
        $newPageTranslation->longDescription = $this->translation->autoTranslation(
            '404 Page not found',
            'en',
            $gp->locale
        );
        $newPageTranslation->externalUrl = false;
        $this->db->persist($newPageTranslation);

        /**
         * Blog Page
         */
        $newPage = new \Fraym\Menu\Entity\MenuItem();
        $newPage->site = $site;
        $newPage->caching = true;
        $newPage->visible = true;
        $newPage->active = true;
        $newPage->https = false;
        $newPage->checkPermission = false;
        $newPage->is404 = false;
        $newPage->parent = $pageRoot;

        $newPageTranslation = new \Fraym\Menu\Entity\MenuItemTranslation();
        $newPageTranslation->menuItem = $newPage;
        $newPageTranslation->title = $this->translation->autoTranslation('Blog', 'en', $gp->locale);
        $newPageTranslation->subtitle = $this->translation->autoTranslation('This is my blog.', 'en', $gp->locale);
        $newPageTranslation->url = "blog";
        $newPageTranslation->shortDescription = $this->translation->autoTranslation(
            'Check out my blog.',
            'en',
            $gp->locale
        );
        $newPageTranslation->longDescription = $this->translation->autoTranslation(
            'Check out my blog.',
            'en',
            $gp->locale
        );
        $newPageTranslation->externalUrl = false;
        $this->db->persist($newPageTranslation);

        $this->db->flush();
        $this->db->clear();

        return $this;
    }

    private function writeConfig()
    {
        $post = $this->request->getGPAsObject();

        $configContent = "<?php
        define('DB_HOST', '{$post->database->host}');
        define('DB_USER', '{$post->database->user}');
        define('DB_PASS', '{$post->database->password}');
        define('DB_DRIVER', '{$post->database->type}');
        define('DB_CHARSET', 'UTF8');
        define('DB_PORT', '{$post->database->port}');
        define('DB_NAME', '{$post->database->name}');
        define('DB_TABLE_PREFIX', '{$post->database->prefix}');
        define('TIMEZONE', '{$post->timezone}');
        define('IMAGE_PROCESSOR', 'GD');
        define('FRAYM_INSTANCE', '" . sprintf("%u", crc32($this->getApplicationDir())) . "');
        if(!defined('ENV')) define('ENV', '{$post->environment}');";

        return file_put_contents($this->_configFile, $configContent);
    }
}