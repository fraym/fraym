<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
chdir(realpath(dirname(__FILE__). DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR));

require 'Fraym/ClassLoader/ClassLoader.php';

define('CACHE_DI_PATH', 'Cache/DI');
define('CACHE_DOCTRINE_PROXY_PATH', 'Cache/DoctrineProxies');
define('CACHE_DOCTRINE_MODULE_FILE', 'Cache/doctrine_module_dir.cache');

$classLoader = new Fraym\ClassLoader\ClassLoader();
$classLoader->register();

error_reporting(-1);
ini_set("display_errors", 1);

$builder = new \DI\ContainerBuilder();
$builder->useAnnotations(true);
$cache = new Doctrine\Common\Cache\ArrayCache();
$builder->setDefinitionCache($cache);
$builder->addDefinitions([
    'db.options' => array(
        'driver' => '',
        'user' =>     '',
        'password' => '',
        'host' =>     '',
        'dbname' =>   '',
        'charset' => ''
    )
]);
$diContainer = $builder->build();

define('GLOBAL_CACHING_ENABLED', false);
define('APC_ENABLED', false);

$core = $diContainer->get('Fraym\Core');

$core->init(\Fraym\Core::ROUTE_CUSTOM);

$installer = $diContainer->get('Fraym\Install\InstallController');

$installer->setup();

$core->response->finish(false, true);