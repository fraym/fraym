<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Route;

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Loader\AnnotationFileLoader;
use Symfony\Component\Routing\Router;

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
     * @var null
     */
    private $routeMatches = [];

    /**
     * @var bool
     */
    private $forcePageLoad = false;

    /**
     * holds all routes by url
     *
     * @var array
     */
    private $moduleRoutes = [];

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
    private $virutalRoutes = [];

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
     * @Inject
     * @var \Fraym\FileManager\FileManager
     */
    protected $fileManager;

    /**
     * Returns the regex matches from a virtual route
     *
     * @return array
     */
    public function getRouteMatches()
    {
        return $this->routeMatches;
    }

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
        $url = ($this->isHttps($menuItemTranslation->menuItem) ? 'https://' : 'http://') . $this->getCurrentDomain() . $menuItemTranslation->url;

        header($header);
        header("Location: $url");
        exit(0);
    }

    /**
     * @param null $menuItem
     * @return bool
     */
    public function isHttps($menuItem = null)
    {
        return ($menuItem !== null && $menuItem->https) ||
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
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
                [$extsension->class, $extsension->method]
            );
        }
        return $this;
    }

    /**
     * @param $class
     */
    private function initClassAnnotationRoutes($class)
    {
        $routeAnnotation = 'Fraym\Annotation\Route';

        $refClass = new \ReflectionClass($class);
        $classAnnotations = $this->db->getAnnotationReader()->getClassAnnotations(
            $refClass
        );

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof $routeAnnotation) {
                $route = $annotation->value;
                $key = $annotation->name;
                $regex = $annotation->regex;
                $permission = $annotation->permission;
                $contextCallback = empty($annotation->contextCallback) ? [] : [$class, $annotation->contextCallback];
                $callback = null;

                $this->addVirtualRoute(
                    $key,
                    $route,
                    $callback,
                    $contextCallback,
                    $regex,
                    $permission
                );
            }
        }
    }

    /**
     * @return $this
     */
    private function initAnnotationRoutes()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile($this->core->getApplicationDir() . '/Fraym/Annotation/Route.php');

        $coreFiles = $this->fileManager->findFiles(
            $this->core->getApplicationDir() . DIRECTORY_SEPARATOR . 'Fraym' . DIRECTORY_SEPARATOR . '*.php'
        );
        $extensionFiles = $this->fileManager->findFiles(
            $this->core->getApplicationDir() . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR . '*.php'
        );

        foreach (array_merge($coreFiles, $extensionFiles) as $file) {
            $classname = basename($file, '.php');
            $namespace = str_ireplace($this->core->getApplicationDir(), '', dirname($file));
            $namespace = str_replace('/', '\\', $namespace) . '\\';
            $class = $namespace . $classname;

            if (is_file($file)) {
                require_once($file);

                if (class_exists($class)) {
                    $this->initClassAnnotationRoutes($class);

                    foreach (get_class_methods($class) as $method) {
                        $key = null;

                        $refMethod = new \ReflectionMethod($class, $method);
                        $methodAnnotation = $this->db->getAnnotationReader()->getMethodAnnotation(
                            $refMethod,
                            'Fraym\Annotation\Route'
                        );

                        if (empty($methodAnnotation) === false) {
                            $route = $methodAnnotation->value;
                            $key = $methodAnnotation->name;
                            $regex = $methodAnnotation->regex;
                            $permission = $methodAnnotation->permission;
                            $callback = [$class, $method];
                            $contextCallback = null;

                            $this->addVirtualRoute(
                                $key,
                                $route,
                                $callback,
                                $contextCallback,
                                $regex,
                                $permission
                            );
                        }
                    }
                }
            }
        }
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
            ->orderBy('translation.url', 'desc')
            ->addOrderBy('menu.checkPermission', 'desc')
            ->where('site.active = 1 AND translation.active = 1 AND translation.externalUrl = 0')
            ->setMaxResults(1);

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

        $qb = $this->prepareMenuQuery()
            ->andWhere("(domain.address = :domain AND translation.url IN (:url))");

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
                ['parent' => null, 'site' => $this->getCurrentMenuItem()->site->id, 'active' => 1, 'visible' => 1]
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
     * @param bool $regex
     * @param array $contextCallback
     * @param array $permission
     */
    public function addVirtualRoute($key, $route, $callback, $contextCallback = null, $regex = false, $permission = [])
    {
        $stdClass = new \stdClass();
        $stdClass->route = $route;
        if (is_array($callback)) {
            $stdClass->controller = $callback[0];
            $stdClass->action = $callback[1];
            $stdClass->inContext = false;
        } else {
            $stdClass->inContext = true;
        }
        $stdClass->permission = $permission;
        $stdClass->regex = $regex;
        $stdClass->contextCallback = $contextCallback;
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
     * @param bool $inContext
     * @return bool
     */
    public function getVirtualRouteContent($inContext = false)
    {
        $requestRoute = $this->getRequestRoute(false, false);
        $requestRouteWithoutBase = $this->getRequestRoute(true, true);
        $addionalUri = $this->getAddionalURI();
        $siteBaseUri = $this->getSiteBaseURI(false);

        foreach ($this->virutalRoutes as $data) {
            if ($data->inContext !== $inContext) {
                continue;
            }
            $route = null;
            $callbackResult = false;

            if (is_array($data->route)) {
                $callback = [$this->serviceLocator->get(key($data->route)), reset($data->route)];
                $callbackResult = call_user_func_array($callback, [$data, $requestRouteWithoutBase]);
            } else {
                $route = rtrim($siteBaseUri, '/') . $data->route;
            }

            if ($callbackResult === true ||
                ($this->core->isCLI() && $route === $requestRoute) ||
                ($route === $requestRoute || ($data->regex === true && preg_match($data->route, $addionalUri, $this->routeMatches)))
            ) {
                $allowAccess = false;
                if (count($data->permission)) {
                    $className = key($data->permission);
                    $methodName = reset($data->permission);
                    $obj = $this->serviceLocator->get($className);
                    $allowAccess = $obj->$methodName();
                }

                if (count($data->permission) === 0 || $allowAccess) {
                    if ($inContext && is_array($data->contextCallback)) {
                        if (count($data->contextCallback) === 2) {
                            $menuItemTranslation = call_user_func([$this->serviceLocator->get($data->contextCallback[0]), $data->contextCallback[1]]);
                            if ($menuItemTranslation) {
                                $this->template->setMainTemplate($menuItemTranslation->menuItem->template->html);
                            }
                        }

                        return true;
                    } elseif ($inContext === false) {
                        $controller = $data->controller;
                        $action = $data->action;
                        $instance = $this->serviceLocator->get($controller);
                        return $instance->$action();
                    }
                }
            }
        }
        return false;
    }

    /**
     *
     */
    public function loadRoutes()
    {
        if ($this->core->isCLI() === false && $this->cache->isCachingActive() && $this->user->isAdmin() === false) {
            if (($routes = $this->cache->getDataCache('routes')) === false) {
                $this->initAnnotationRoutes();
                $this->cache->setDataCache('routes', $this->virutalRoutes);
            } else {
                $this->virutalRoutes = $routes;
            }
        } else {
            $this->initExtensionRoutes();
            $this->initAnnotationRoutes();
        }
    }

    /**
     * Trys to load a site by the requested URI.
     *
     * @return bool
     */
    public function loadSite()
    {
        // Connect database after tring to load the cache
        $this->db->connect()->setUpTranslateable()->setUpSortable();

        $this->renderSite($this->parseRoute());
    }

    /**
     * @return mixed
     */
    public function getRequestDomain()
    {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * @param null $menuItemTranslation
     */
    public function renderSite($menuItemTranslation = null)
    {
        $tpl = $this->template;

        if (is_object($menuItemTranslation)) {
            $this->loadRoutes();

            $menuItemTranslation = $this->db
                ->getRepository('\Fraym\Menu\Entity\MenuItemTranslation')
                ->findOneById($menuItemTranslation->id);

            // must set before check route, because the locale must be set
            $this->setupPage($menuItemTranslation);

            if (!$this->getCurrentDomain()) {
                $this->setCurrentDomain($this->getRequestDomain());
            }

            $virtualRouteContent = $this->getVirtualRouteContent();

            /**
             * Redirect http to https if enabled
             */
            if ($virtualRouteContent === false &&
                $this->request->isXmlHttpRequest() === false &&
                $this->isHttps() === false &&
                $this->currentMenuItem->https === true
            ) {
                $this->redirectToURL('https://' . $this->getRequestRoute());
            }

            $this->sitefullRoute = rtrim($this->buildFullUrl($this->currentMenuItem), '/');

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

            // Redirect URL to correct uri with or withour slash
            if ($this->getRequestRoute(true) !== '/' && $this->getFoundURI(false) === trim($this->getRequestRoute(false, false), '/') && $this->getFoundURI(false) !== $this->getRequestRoute(false, false)) {
                $this->redirectToURL($this->getFoundURI(true));
            }

            if ($this->getFoundURI(false) === trim($this->getRequestRoute(false, false), '/') || $this->getVirtualRouteContent(true)) {
                $this->siteManager->addAdminPanel();
                $tpl->renderMainTemplate();
            } else {
                return $this->menuItemNotFound();
            }

            if ($this->template->isCachingDisabled() === false) {
                // cache page if cache enable
                $this->cache->setCacheContent();
            }
        } else {
            $this->menuItemNotFound();
        }
    }

    /**
     * @return string
     */
    public function getDefaultMenuItemTemplate()
    {
        return '<html><head><block type="css" sequence="outputFilter" consolidate="false"></block><block type="js" sequence="outputFilter" consolidate="false"></block></head><body>Add a template to the menu item</body></html>';
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
                ->where("site.id = :site AND menuItem.is404 = 1 AND menuItemTranslation.active = 1");

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
            error_log('Menu item not found or template not set! Solutions: Set a menu item template with the menu editor, reinstall Fraym or check your webserver configuration.');
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
            $this->db->setTranslatableLocale($menuItemTranslation->locale->locale);
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
        return $this->template->renderString($this->template->getMainTemplate());
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
        $addional_uri = str_replace(['=', '&'], '/', $addional_uri);
        $addional_uri = trim($addional_uri, '/');
        $addional_uri = explode('/', $addional_uri);

        $result_arr = [];
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
            $protocol = ($this->isHttps($menu) ? 'https://' : 'http://');
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
        return ($withProtocol ? ($this->isHttps($menu) ? 'https://' : 'http://') : '') . $this->getHostname();
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
                ($this->isHttps($menu) ? 'https://' : 'http://') : '') .
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
            $url = ($this->isHttps($menuItem) ? 'https://' : 'http://') . $url;
        }
        return $url;
    }

    /**
     * Converts a string to a URI string
     *
     * @param $uri
     * @param string $separator
     * @param bool $keepAnchor
     * @return string
     */
    public function createSlug($uri, $separator = '-', $keepAnchor = false)
    {
        $uri = str_replace(',', '-', $uri);
        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $uri = preg_replace('![^' . ($keepAnchor === true ? preg_quote('#', '!') : '') . '' . preg_quote($separator, '!') . '\pL\pN\s]+!u', '', mb_strtolower($uri, 'UTF-8'));
        // Replace all separator characters and whitespace by a single separator
        $uri = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $uri);
        // Trim separators from the beginning and end
        return trim($uri, $separator);
    }
}
