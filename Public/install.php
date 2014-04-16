<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
chdir(realpath(dirname(__FILE__). DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR));

require 'Fraym/ClassLoader/ClassLoader.php';

$classLoader = new Fraym\ClassLoader\ClassLoader();
$classLoader->register();

error_reporting(-1);
ini_set("display_errors", 1);

$builder = new \DI\ContainerBuilder();
$cache = new Doctrine\Common\Cache\ArrayCache();
$builder->setDefinitionCache($cache);
$diContainer = $builder->build();

define('GLOBAL_CACHING_ENABLED', false);
define('APC_ENABLED', false);

$core = $diContainer->get('Fraym\Core');

$diContainer->set('db.options', array('driver' => '',
                                      'user' =>     '',
                                      'password' => '',
                                      'host' =>     '',
                                      'dbname' =>   '',
                                      'charset' => '',
));

$core->init(\Fraym\Core::ROUTE_CUSTOM);

$installer = $diContainer->get('Fraym\Install\InstallController');

$installer->setup();

$core->response->finish(false, true);