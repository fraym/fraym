<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */

chdir(realpath(dirname(__FILE__)));

require 'Vendor/DI/functions.php';
require 'Fraym/ClassLoader/ClassLoader.php';

$classLoader = new Fraym\ClassLoader\ClassLoader();
$classLoader->register();

if (is_file('Config.php')) {
    require 'Config.php';
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo '<a href="/install.php">Please install Fraym.</a>';
    exit(0);
}

date_default_timezone_set(TIMEZONE);

$diContainer = new \DI\ContainerBuilder();

define('APC_ENABLED', extension_loaded('apc') && ini_get('apc.enabled'));
define('CACHE_DI_PATH', 'Cache/DI');
define('CACHE_DOCTRINE_PROXY_PATH', 'Cache/DoctrineProxies');
define('CACHE_DOCTRINE_MODULE_FILE', 'Cache/doctrine_module_dir.cache');

if (\Fraym\Core::ENV_STAGING === ENV || \Fraym\Core::ENV_PRODUCTION === ENV) {

    error_reporting(0);
    ini_set("display_errors", 0);

    if (!is_dir(CACHE_DI_PATH)) {
        mkdir(CACHE_DI_PATH, 0755);
    }

    $builder = new \DI\ContainerBuilder();
    if (APC_ENABLED) {
        $cache = new Doctrine\Common\Cache\ApcCache();
    } else {
        $cache = new Doctrine\Common\Cache\ArrayCache();
    }
    $cache->setNamespace('Fraym_instance_' . FRAYM_INSTANCE);
    $builder->setDefinitionCache($cache);

    $builder->writeProxiesToFile(true, CACHE_DI_PATH);
    $diContainer = $builder->build();
    define('GLOBAL_CACHING_ENABLED', true);
} else {
    error_reporting(-1);
    ini_set("display_errors", 1);

    $builder = new \DI\ContainerBuilder();

    if (APC_ENABLED && \Fraym\Core::ENV_TESTING === ENV) {
        $cache = new Doctrine\Common\Cache\ApcCache();
    } else {
        $cache = new Doctrine\Common\Cache\ArrayCache();
    }

    $builder->setDefinitionCache($cache);
    $diContainer = $builder->build();
    define('GLOBAL_CACHING_ENABLED', false);
}

$diContainer->set('db.options', array('driver' => DB_DRIVER,
                                      'user' =>     DB_USER,
                                      'password' => DB_PASS,
                                      'host' =>     DB_HOST,
                                      'dbname' =>   DB_NAME,
                                      'charset' => DB_CHARSET,
));


if (defined('IMAGE_PROCESSOR') && IMAGE_PROCESSOR === 'Imagick') {
    $diContainer->set('Imagine', DI\link('Imagine\Imagick\Imagine'));
} elseif (defined('IMAGE_PROCESSOR') && IMAGE_PROCESSOR === 'Gmagick') {
    $diContainer->set('Imagine', DI\link('Imagine\Gmagick\Imagine'));
} else {
    $diContainer->set('Imagine', DI\link('Imagine\Gd\Imagine'));
}