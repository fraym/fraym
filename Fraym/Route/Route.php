<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Route;

/**
 * Class Route
 * @package Fraym\Route
 * @Injectable(lazy=true)
 */
class Route
{
    /**
     * @var string
     */
    private $sitefullRoute = '';

    /**
     * @var string
     */
    private $addionalRoute = '';

    /**
     * @var bool
     */
    private $forcePageLoad = false;

    /**
     * holds all routes by url
     *
     * @var array
     */
    private $moduleRoutes = array();

    /**
     * @var bool
     */
    private $currentDomain = false;

    /**
     * @var bool|\Fraym\Menu\Entity\MenuItem
     */
    private $currentMenuItem = false;

    /**
     * @var bool|\Fraym\Menu\Entity\MenuItemTranslation
     */
    private $currentMenuItemTranslation = false;

    /**
     * @var bool
     */
    private $siteMenu = false;

    /**
     * @var array
     */
    private $virutalRoutes = array();

    /**
     * @var array
     */
    private $routeHooks = array();

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
     * @var \Fraym\Cache\Cache
     */
    protected $cache;

    /**
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

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
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    /**
     * @Inject
     * @var \Fraym\Response\Response
     */
    public $response;

    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManager
     */
    public $siteManager;

    /**
     * If we have a virtual URI we must set force page load to true, otherwise we get page note fount error
     *
     * @param bool $forcePageLoad
     */
    public function setForcePageLoad($forcePageLoad = true)
    {
        $this->forcePageLoad = $forcePageLoad;
    }

    /**
     * try to load the caching file if one is availible and caching is active
     *
     * @Inject
     * @param \Fraym\Database\Database $db
     */
    public function __construct(\Fraym\Database\Database $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param $url
     * @param string $header
     */
    public function redirectToURL($url, $header = 'HTTP/1.1 301 Moved Permanently')
    {
        header($header);
        header("Location: $url");
        exit(0);
    }

    /**
     * @param \Fraym\Menu\Entity\MenuItemTranslation $menuItemTranslation
     * @param string $header
     */
    public function redirectToPage($menuItemTranslation, $header = 'HTTP/1.1 301 Moved Permanently')
    {
        $url = ($menuItemTranslation->menuItem->https ? 'https://' : 'http://') . $this->getCurrentDomain() . $menuItemTranslation->url;

        header($header);
        header("Location: $url");
        exit(0);
    }

    /**
     * @return $this
     */
    private function initExtensionRoutes()
    {
        // Init only for admins
        if (!$this->user->isAdmin()) {
            return $this;
        }

        foreach ($this->db->getRepository('\Fraym\SiteManager\Entity\Extension')->findAll() as $extsension) {
            $this->addVirtualRoute(
                'siteManagerExt_' . $extsension->id,
                '/fraym/admin/extension/' . $extsension->id,
                array($extsension->class, $extsension->method)
            );
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isHTTPS()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? true : false;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->moduleRoutes;
    }

    /**
     * @static
     * @return mixed
     */
    public function prepareMenuQuery()
    {
        $dql = $this->db->createQueryBuilder()
            ->select("translation, locale, menu, template, site, domain")
            ->from('\Fraym\Menu\Entity\MenuItemTranslation', 'translation')
            ->join('translation.menuItem', 'menu')
            ->leftJoin("menu.template", 'template')
            ->join("menu.site", 'site')
            ->join("site.domains", 'domain')
            ->join("translation.locale", 'locale')
            ->setMaxResults(1)
            ->orderBy('translation.url', 'desc')
            ->addOrderBy('menu.checkPermission', 'desc');

        if ($this->user->isLoggedIn() === false) {
            $dql = $dql->andWhere('menu.checkPermission = 0');
        }

        return $dql;
    }

    /**
     * @param $domain
     */
    public function setCurrentDomain($domain)
    {
        $this->currentDomain = $domain;
    }

    /**
     * @return bool
     */
    public function getCurrentDomain()
    {
        return $this->currentDomain;
    }

    /**
     * @static
     * @return array|bool
     */
    private function parseRoute()
    {
        $fullMenuItemUrl = '';
        $requestedRoute = $this->getRequestRoute(true, false);
        $routePath = explode('/', $requestedRoute);

        $routeHost = $this->getHostname();

        $qb = $this->prepareMenuQuery()->where(
            "(domain.address = :domain AND
            translation.url IN (:url)) AND
            site.active = 1 AND
            menu.active = 1 AND
            translation.externalUrl = 0"
        );

        $concatPath = '';

        foreach ($routePath as &$path) {
            $concatPath .= ($path == '' ? '' : '/') . $path;
            $routeArray[] = $concatPath;
        }

        $routeArray = array_reverse($routeArray);

        $result = $qb->setParameter('url', $routeArray)
            ->setParameter('domain', $routeHost)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result !== null) {
            $fullMenuItemUrl = $routeHost . $result->url;
            $this->setCurrentDomain($routeHost);
        }

        $this->addionalRoute = str_replace($fullMenuItemUrl, '', $this->getRequestRoute());

        $this->db->clear();
        return $result;
    }

    /**
     * @return \Fraym\Menu\Entity\MenuItem
     */
    public function getCurrentMenuItem()
    {
        return $this->currentMenuItem;
    }

    /**
     * @return bool
     */
    public function getCurrentMenuItemTranslation()
    {
        return $this->currentMenuItemTranslation;
    }

    /**
     * @param $menuItem
     */
    public function setCurrentMenuItem($menuItem)
    {
        $this->currentMenuItem = $menuItem;
    }

    /**
     * @param $menuItem
     */
    public function setCurrentMenuItemTranslation($menuItem)
    {
        $this->currentMenuItemTranslation = $menuItem;
    }

    /**
     * @return bool
     */
    public function getMenu()
    {
        if (!$this->siteMenu) {
            $this->siteMenu = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneBy(
                array('parent' => null, 'site' => $this->getCurrentMenuItem()->site->id, 'active' => 1, 'visible' => 1)
            );
        }
        return $this->siteMenu;
    }

    /**
     * @return mixed
     */
    public function getMenuPath()
    {
        return $this->currentMenuItem->getUrl();
    }

    /**
     * @param $key
     * @param $route
     * @param $callback
     */
    public function addVirtualRoute($key, $route, $callback)
    {
        $stdClass = new \stdClass();
        $stdClass->route = $route;
        $stdClass->controller = $callback[0];
        $stdClass->action = $callback[1];
        $this->virutalRoutes[$key] = $stdClass;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getVirtualRoute($key)
    {
        $virtualRoute = $this->virutalRoutes[$key];
        return $virtualRoute;
    }

    /**
     * @return bool
     */
    public function checkVirtualRoute()
    {
        foreach ($this->virutalRoutes as $data) {
            $isRelativeRoute = substr($data->route, 0, 1) === '/' ? false : true;
            $route = rtrim($this->getSiteBaseURI(false), '/') . $data->route;

            if (($this->core->isCLI() && $route == $this->getRequestRoute(false, false)) ||
                ($isRelativeRoute === true && ltrim($this->getAddionalURI(), '/') == $data->route ||
                    ($isRelativeRoute === false && $route == $this->getRequestRoute(false, false)))
            ) {
                $controller = $data->controller;
                $action = $data->action;
                $instance = $this->serviceLocator->get($controller);

                return $instance->$action();
            }
        }
        return false;
    }

    /**
     * @param $callback
     * @param array $paramArr
     */
    public function addRouteHook($callback, $paramArr = array())
    {
        $this->routeHooks[] = array($callback, $paramArr);
    }

    /**
     * @return bool|mixed
     */
    private function checkRouteHook()
    {
        foreach ($this->routeHooks as $hook) {
            list($callback, $paramArr) = $hook;
            $result = call_user_func_array($callback, $paramArr);
            if ($result !== false) {
                $this->addionalRoute = $this->getRequestRoute(true, false);
                return $result;
            }
        }
        return false;
    }

    /**
     * Trys to load a site by the requested URI.
     *
     * @return bool
     */
    public function loadSite()
    {
        // Connect database after tring to load the cache
        $this->db->connect();
        $this->initExtensionRoutes();

        $this->db->setUpTranslateable()->setUpSortable();

        if ($menuItem = $this->checkRouteHook()) {
            $this->renderSite(true, $menuItem);
        } else {
            $this->renderSite(true, $this->parseRoute());
        }
    }

    /**
     *
     */
    public function loadVirtualRoutes()
    {
        $routes = $this->db->connect()->getRepository('\Fraym\Route\Entity\VirtualRoute')->findAll();
        foreach ($routes as $route) {
            $this->addVirtualRoute($route->key, $route->route, array($route->class, $route->method));
        }
    }

    /**
     * @return mixed
     */
    public function getRequestDomain()
    {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * @param bool $routeInit
     * @param null $menuItemTranslation
     */
    public function renderSite($routeInit = true, $menuItemTranslation = null)
    {

        $tpl = $this->template;

        if (is_object($menuItemTranslation)) {

            $this->loadVirtualRoutes();

            $menuItemTranslation = $this->db->getRepository('\Fraym\Menu\Entity\MenuItemTranslation')->findOneById(
                $menuItemTranslation->id
            );

            if (!$this->getCurrentDomain()) {
                $this->setCurrentDomain($this->getRequestDomain());
            }

            // must set before check route, because the locale must be set
            $this->setupPage($menuItemTranslation);

            $virtualRouteContent = $this->checkVirtualRoute();

            if ($virtualRouteContent === false &&
                $this->request->isXmlHttpRequest() === false &&
                $this->isHTTPS() === false &&
                $this->currentMenuItem->https === true
            ) {
                $this->redirectToURL('https://' . $this->getRequestRoute());
            }

            $this->sitefullRoute = rtrim($this->buildFullUrl($this->currentMenuItem), '/');

            if ($routeInit == true) {

                if ($virtualRouteContent !== false) {
                    // virtual route content
                    $this->response->send($virtualRouteContent, true);
                }

                if ($this->currentMenuItem->checkPermission === true &&
                    $this->currentMenuItem->parent !== null &&
                    $this->user->isLoggedIn() === false
                ) {

                    return $this->menuItemNotFound();
                }

                // read the template content
                $mainTemplateString = ($menuItemTranslation->menuItem->template ?
                    $menuItemTranslation->menuItem->template->html :
                    $this->getDefaultMenuItemTemplate());

                if ($this->getFoundURI(false) != trim($this->getRequestRoute(false, false), '/')) {
                    $this->blockParser->setCheckRouteError(true);
                    $tpl->setTemplate('string:' . $mainTemplateString);
                    $content = $tpl->prepareTemplate();

                    $routeExistModules = $this->blockParser->moduleRouteExist($content);

                    if ($routeExistModules === false) {
                        return $this->menuItemNotFound();
                    } else {
                        if ($this->request->isXmlHttpRequest() === true) {
                            // only exec modules where we find the route
                            $this->core->response->send($this->blockParser->parse($routeExistModules));
                        } else {
                            $this->siteManager->addAdminPanel();
                            echo $this->blockParser->parse($content);
                        }
                    }
                } else {
                    $this->siteManager->addAdminPanel();
                    $tpl->renderString($mainTemplateString);
                }

                // cache page if cache enable
                $this->cache->setCacheContent();
            } else {
                $tpl->renderString($menuItemTranslation->menuItem->template->html);
            }
        } else {
            $this->menuItemNotFound();
        }
    }

    /**
     * @return string
     */
    private function getDefaultMenuItemTemplate() {
        return '<html><head><block type="css" sequence="outputFilter" consolidate="false"></block><block type="js" sequence="outputFilter" consolidate="false"></block></head><body>Please add a template to the root menu item</body></html>';
    }

    /**
     *
     */
    public function menuItemNotFound()
    {
        $page404 = null;

        if ($this->currentMenuItem) {
            $localeId = $this->session->get('localeId', false);

            $page404 = $this->db->createQueryBuilder()
                ->select("menuItemTranslation, menuItem, template, site, locale")
                ->from('\Fraym\Menu\Entity\MenuItemTranslation', 'menuItemTranslation')
                ->join('menuItemTranslation.menuItem', 'menuItem')
                ->leftJoin("menuItem.template", 'template')
                ->join("menuItem.site", 'site')
                ->join("menuItemTranslation.locale", 'locale')
                ->setMaxResults(1)
                ->setParameter('site', $this->currentMenuItem->site->id)
                ->where("site.id = :site AND menuItem.is404 = 1 AND menuItem.active = 1");

            if ($localeId) {
                $page404 = $page404->andWhere('locale.id = :locale')->setParameter('locale', $localeId);
            } else {
                $page404 = $page404->andWhere('locale.default = 1');
            }
            $page404 = $page404
                ->getQuery()
                ->getOneOrNullResult();
        }

        // call site note found view
        if (is_object($page404)) {
            $this->render404Site($page404);
            // cache the 404 page
            $this->cache->setCacheContent();
        } else {

            error_log('Menuitem not found or template not set!');
            $this->response->sendHTTPStatusCode(500);
        }
        $this->response->finish(true, true);
    }

    /**
     * @param $menuItemTranslation
     * @return bool
     */
    private function setupPage($menuItemTranslation)
    {
        try {
            $this->currentMenuItemTranslation = $menuItemTranslation;
            $this->currentMenuItem = $menuItemTranslation->menuItem;
            $this->locale->setLocale($menuItemTranslation->locale);
            $this->template->setSiteTemplateDir($menuItemTranslation->menuItem->site->templateDir);

            $pageTitle = ((string)$menuItemTranslation->pageTitle === '' ?
                    $menuItemTranslation->title :
                    $menuItemTranslation->pageTitle) . ' | ' . $menuItemTranslation->menuItem->site->name;

            $this->template->setPageTitle($pageTitle);
            $this->template->setPageDescription($menuItemTranslation->longDescription);
            $this->template->setKeywords(explode(',', $menuItemTranslation->keywords));

            setlocale(LC_ALL, $this->locale->getLocale()->locale);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param \Fraym\Menu\Entity\MenuItemTranslation $page404
     * @return bool|void
     */
    private function render404Site(\Fraym\Menu\Entity\MenuItemTranslation $page404)
    {
        $this->setupPage($page404);
        $this->response->sendHTTPStatusCode(404);
        if ($this->request->isXmlHttpRequest() === true) {
            return false;
        }
        $this->siteManager->addAdminPanel();
        return $this->template->renderString($this->currentMenuItem->template->html);
    }

    /**
     * @return string
     */
    public function getAddionalURI()
    {
        return $this->addionalRoute;
    }

    /**
     * @return array
     */
    public function getAddionalURIParams()
    {
        $addional_uri = trim($this->getAddionalURI(), '?');
        $addional_uri = str_replace(array('=', '&'), '/', $addional_uri);
        $addional_uri = trim($addional_uri, '/');
        $addional_uri = explode('/', $addional_uri);

        $result_arr = array();
        if ($addional_uri !== false) {
            foreach ($addional_uri as $key => $value) {
                if (!empty($value) && !($key % 2)) {
                    $result_arr[$value] = isset($addional_uri[$key + 1]) ? $addional_uri[$key + 1] : null;
                }
            }

            foreach ($result_arr as $key => &$value) {
                if (is_array($qryStr = explode('?', $value)) || is_array($qryStr = explode('?', $key))) {
                    if (count($qryStr) > 1) {
                        parse_str($qryStr[1], $output);
                        $result_arr = array_merge($result_arr, $output);
                        $value = $qryStr[0];
                    }
                }
            }
        }
        return $result_arr;
    }

    /**
     * Gets the base URI of th current site. http://www.foo.bar or www.foo.bar or www.foo.bar/mysite.
     *
     * @static
     * @param bool $withProtocol
     * @param bool $with_root_page
     * @return string
     */
    public function getSiteBaseURI($withProtocol = true, $with_root_page = false)
    {
        $root_page_uri = '';
        $protocol = '';
        $rootMenu = $this->getCurrentMenuItemTranslation();
        $menu = $this->getCurrentMenuItem();

        if ($with_root_page && $menu) {
            $root_page_uri = $rootMenu->url;
        }

        if ($withProtocol) {
            $protocol = ($menu && $menu->https ? 'https://' : 'http://');
        }

        $url = ($menu !== false ? $this->getCurrentDomain() : '') . $root_page_uri;

        return empty($url) ? '/' : $protocol . $url;
    }

    /**
     * Gets the real server base URI
     *
     * @param bool $withProtocol
     * @return string
     */
    public function getHostnameWithBasePath($withProtocol = true)
    {
        $menu = $this->currentMenuItem;
        return ($withProtocol ? ($menu && $menu->https ? 'https://' : 'http://') : '') . $this->getHostname();
    }

    /**
     * Gets the full uri that was found in the db for example: www.foo.bar/mymenu
     *
     * @param bool $withProtocol
     * @return string
     */
    public function getFoundURI($withProtocol = true)
    {
        return ($this->sitefullRoute != '' ? ($withProtocol ? 'http://' : '') . $this->sitefullRoute : '');
    }

    /**
     * Gets the current HTTP host.
     *
     * @return string
     */
    public function getHostname()
    {
        return (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
    }

    /**
     * Returns the current request URI with or without hostname.
     *
     * @param bool $requst_only
     * @param bool $withParamater
     * @param bool $withProtocol
     * @return string
     */
    public function getRequestRoute($requst_only = false, $withParamater = true, $withProtocol = false)
    {
        $requestUri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
        $urlParts = parse_url($requestUri);
        $menu = $this->currentMenuItem;

        return ($withProtocol ?
                ($menu && $menu->https ? 'https://' : 'http://') : '') .
                ($requst_only ? '' : $this->getHostname()) .
                (isset($urlParts['path']) ? $urlParts['path'] : '') .
                ($withParamater &&
                isset($urlParts['query']) ? '?' . $urlParts['query'] : '');
    }

    /**
     * @param Fraym\Menu\Entity\MenuItem $menuItem
     * @param bool $withProtocol
     * @return string
     */
    public function buildFullUrl($menuItem, $withProtocol = false)
    {
        if ($menuItem->getCurrentTranslation() &&
            $menuItem->getCurrentTranslation()->externalUrl) {

            return $menuItem->getCurrentTranslation()->url;
        }
        $url = rtrim($this->getCurrentDomain(), '/') . '/' .
                ($menuItem->getCurrentTranslation() ?
                ltrim($menuItem->getCurrentTranslation()->url, '/')
                : '');
        if ($withProtocol === true) {
            $url = ($menuItem->https ? 'https://' : 'http://') . $url;
        }
        return $url;
    }

    /**
     * Converts a string to a URI string
     *
     * @param  $uri
     * @param string $separator
     * @return string
     */
    public function createSlug($uri, $separator = '-')
    {
        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $uri = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($uri, 'UTF-8'));
        // Replace all separator characters and whitespace by a single separator
        $uri = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $uri);
        // Trim separators from the beginning and end
        return trim($uri, $separator);
    }
}
