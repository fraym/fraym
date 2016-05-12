<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Cache;

/**
 * Class Cache
 * @package Fraym\Cache
 * @Injectable(lazy=true)
 */
class Cache
{
    /**
     * Page cache dir
     */
    const DIR_PAGES = 'Cache/Pages/';

    /**
     * Page cache dir
     */
    const DIR_CUSTOM_DATA = 'Cache/Data/';
    
    /**
     * @var string
     */
    private $menuPermission = '';

    /**
     * @Inject
     * @var \Fraym\Template\Template
     */
    protected $template;

    /**
     * @Inject
     * @var \Fraym\Block\Block
     */
    protected $block;

    /**
     * @Inject
     * @var \Fraym\Route\Route
     */
    protected $route;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    protected $request;

    /**
     * @Inject
     * @var \Fraym\Session\Session
     */
    protected $session;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @Inject
     * @var \Fraym\Core
     */
    protected $core;

    /**
     * @Inject
     * @var \Fraym\FileManager\FileManager
     */
    protected $fileManager;

    /**
     * gets the user permission
     *
     * @return bool    true or false
     */
    public function getPermission()
    {
        return $this->menuPermission;
    }

    /**
     * @param $checkMenuPermission
     */
    public function setPermission($checkMenuPermission)
    {
        $this->menuPermission = $checkMenuPermission;
    }

    /**
     * if cacheing is active and a cached file is availible it outputs the cache and exits the script
     *
     * @return void
     */
    public function load()
    {
        // gets the current uri - example: /folder/folder2
        $filename = $this->getCacheName();
        // create the cache filename
        $cacheFilename = self::DIR_PAGES . $filename . '.cache.php';
        $cacheFilenamePhpData = self::DIR_PAGES . $filename . '.cache.config.php';
        $menuItemTranslation = false;
        $domain = false;
        $executedBlocks = [];

        if (defined('GLOBAL_CACHING_ENABLED') &&
            GLOBAL_CACHING_ENABLED &&
            !$this->request->isXmlHttpRequest() &&
            !$this->request->isPost() &&
            $this->user->isAdmin() === false && // don't load cache if the user is admin - to see changes on the site
            is_file($cacheFilename) &&
            is_file($cacheFilenamePhpData)
        ) {
            include($cacheFilenamePhpData);

            if ($menuItemTranslation) {
                $menuItemTranslation = json_decode($menuItemTranslation);

                if (is_object($menuItemTranslation)) {
                    if ($this->request->isXmlHttpRequest() === false &&
                        $this->route->isHttps() === false &&
                        $menuItemTranslation->menuItem->https === true
                    ) {
                        $this->route->redirectToURL('https://' . $this->route->getRequestRoute());
                    }

                    $this->locale->setLocale($menuItemTranslation->locale);
                    $this->template->setSiteTemplateDir($menuItemTranslation->menuItem->site->templateDir);
                    $this->route->setCurrentMenuItem($menuItemTranslation->menuItem);
                    $this->route->setCurrentMenuItemTranslation($menuItemTranslation);
                    $this->route->setCurrentDomain($domain);
                    if ($this->isCachingActive($menuItemTranslation->menuItem)) {
                        $this->blockParser->setExecutedBlocks(json_decode($executedBlocks));

                        // display the cached file to the client
                        $contents = file_get_contents($cacheFilename);
                        $this->blockParser->setParseCached(true);
                        $content = $this->blockParser->parse($contents, 'outputFilter');
                        echo $this->core->includeScript($content);
                        exit();
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    private function getCacheName()
    {
        $uri = $this->route->getRequestRoute();
        $user = $this->session->get('userId', '');
        return md5($uri . $user);
    }

    /**
     * save the output to cache file
     *
     * @return bool    ture or false
     */
    public function setCacheContent()
    {
        if (!is_dir(self::DIR_PAGES)) {
            mkdir(self::DIR_PAGES, 0755, true);
        }

        $filename = $this->getCacheName();

        // create the cache filename
        $cacheFilename = self::DIR_PAGES . $filename . '.cache.php';
        $cacheFilenamePhpData = self::DIR_PAGES . $filename . '.cache.config.php';
        $cacheInfo = "/*\n\n" . str_ireplace('*', '', print_r($_SERVER, true)) . "\n\n*/\n\n";
        $phpCode = '<?php ' . $cacheInfo;
        $currentMenuItem = $this->route->getCurrentMenuItemTranslation();

        if (!$this->request->isXmlHttpRequest() &&
            !$this->request->isPost() &&
            $this->user->isAdmin() === false && // prevent caching of admin panel
            (!is_file($cacheFilename) || !is_file($cacheFilenamePhpData))
            && $this->isCachingActive($currentMenuItem->menuItem)
        ) {
            // save cached file

            $source = $this->template->outputFilter(ob_get_contents());

            $phpCode .= '$menuItemTranslation = <<<\'EOT\'' . "\n" .
                json_encode(
                    $currentMenuItem->toArray()
                ) . "\n" . 'EOT;' . "\n";
            $phpCode .= '$domain = "' . $this->route->getCurrentDomain() . '";';
            $phpCode .= '$executedBlocks = <<<\'EOT\'' . "\n" .
                json_encode(
                    $this->blockParser->getExecutedBlocks()
                ) . "\n" . 'EOT;' . "\n";

            file_put_contents($cacheFilenamePhpData, $phpCode);
            file_put_contents($cacheFilename, $source . "<!-- CACHED : " . date('Y-m-d H:i:s') . " -->");

            // clean the output buffer
            ob_clean();
            // parse cached blocks for first output
            $this->blockParser->setParseCached(true);
            echo $this->blockParser->parse($source, false);

            return true;
        }

        $source = $this->template->outputFilter(ob_get_contents());
        // clean the output buffer
        ob_clean();
        // parse cached blocks for first output
        $this->blockParser->setParseCached(true);
        echo $this->blockParser->parse($source, false);

        return false;
    }

    /**
     * checks if caching is active. it checks global caching, site caching, and menu caching
     *
     * @param $menuItem
     * @return bool
     */
    public function isCachingActive($menuItem = null)
    {
        $menuCachingActive = $menuItem ? $menuItem->caching : null;
        $siteCachingActive = $menuItem ? $menuItem->site->caching : null;

        if (GLOBAL_CACHING_ENABLED) {
            if ($menuCachingActive === false) {
                return $menuCachingActive;
            } elseif ($menuCachingActive === null && $siteCachingActive === false) {
                return $siteCachingActive;
            } elseif ($menuCachingActive === null && $siteCachingActive === null) {
                return GLOBAL_CACHING_ENABLED;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function deleteCurrentFile()
    {
        $filename = $this->getCacheName();

        if (is_file($filename)) {
            unlink($filename);
            return true;
        }
        return false;
    }

    /**
     * @param string $url example fraym.org/url
     */
    public function deleteCache($url)
    {
        $filename = md5($url);
        $cacheFilename = self::DIR_PAGES . $filename . '.cache.php';
        $cacheFilenamePhpData = self::DIR_PAGES . $filename . '.cache.config.php';

        if (is_file($cacheFilename)) {
            unlink($cacheFilename);
        }
        if (is_file($cacheFilenamePhpData)) {
            unlink($cacheFilenamePhpData);
        }
    }

    /**
     * @param $key
     * @param $data
     * @return $this
     */
    public function setDataCache($key, $data)
    {
        if (is_array($data) || is_object($data)) {
            $data = serialize($data);
        }
        if (!is_dir(self::DIR_CUSTOM_DATA)) {
            mkdir(self::DIR_CUSTOM_DATA, 0755, true);
        }
        $cacheFilename = self::DIR_CUSTOM_DATA . md5($key) . '.cache';
        file_put_contents($cacheFilename, $data);
        return $this;
    }

    /**
     * @param $key
     * @return bool|mixed|string
     */
    public function getDataCache($key)
    {
        $cacheFilename = self::DIR_CUSTOM_DATA . md5($key) . '.cache';
        if (is_file($cacheFilename) && filesize($cacheFilename) > 0) {
            $data = file_get_contents($cacheFilename);
            if ($unSerializedData = unserialize($data)) {
                $data = $unSerializedData;
            }
            return $data;
        }
        return false;
    }

    /**
     * @param $key
     * @return bool|mixed|string
     */
    public function dataCacheExists($key)
    {
        $cacheFilename = self::DIR_CUSTOM_DATA . md5($key) . '.cache';
        return is_file($cacheFilename) && filesize($cacheFilename) > 0;
    }

    /**
     * @param $key
     * @return bool
     */
    public function deleteDataCache($key)
    {
        $cacheFilename = self::DIR_CUSTOM_DATA . md5($key) . '.cache';
        if (is_file($cacheFilename)) {
            unlink($cacheFilename);
            return true;
        }
        return false;
    }

    /**
     * @Fraym\Annotation\Route("cacheClearAll", name="cacheClearAll")
     */
    public function clearAll()
    {
        // clear PHP opcache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        // clear APC/APCu Cache
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache('user');
            apc_clear_cache();
        }

        if (defined('CACHE_DI_PATH')) {
            $this->fileManager->deleteFolder(CACHE_DI_PATH);
        }
        if (defined('CACHE_DOCTRINE_PROXY_PATH')) {
            $this->fileManager->deleteFolder(CACHE_DOCTRINE_PROXY_PATH);
        }

        $this->fileManager->deleteFolder(self::DIR_PAGES);
        $this->fileManager->deleteFolder(self::DIR_CUSTOM_DATA);
        $this->fileManager->deleteFolder('Public' . CONSOLIDATE_FOLDER);
    }
}
