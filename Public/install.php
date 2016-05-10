<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
chdir(realpath(dirname(__FILE__). DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR));
set_time_limit(0);

if(!is_file('Vendor/autoload.php') && !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
    echo file_get_contents('Template/Default/Fraym/Install/Preload.tpl');
    exit();
}

/**
 * Install composer
 */
if(!is_file('composer.phar')) {
    copy('https://getcomposer.org/composer.phar', 'composer.phar');
    echo json_encode(['message' => 'Downloading dependencies, this may take several minutes...', 'done' => true]);
    ob_flush();
    exit();
}

if(!is_file('Vendor/autoload.php')) {
    \Phar::loadPhar('composer.phar', 'composer.phar');
    require 'phar://composer.phar/src/bootstrap.php';
    $input = new Symfony\Component\Console\Input\ArrayInput(array('command' => 'install'));
    $application = new Composer\Console\Application();
    $application->setAutoExit(false);
    $application->run($input);
    echo json_encode(['message' => 'Done. Reloading installation...', 'done' => false]);
    ob_flush();
    exit();
}

require 'Vendor/autoload.php';

define('CACHE_DI_PATH', 'Cache/DI');
define('CACHE_DOCTRINE_PROXY_PATH', 'Cache/DoctrineProxies');
define('CACHE_DOCTRINE_MODULE_FILE', 'Cache/doctrine_module_dir.cache');

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