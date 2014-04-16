<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\ClassLoader;

/**
 * Class ClassLoader
 * @package Fraym\ClassLoader
 */
class ClassLoader
{
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Load a module and returns the module instance
     *
     * @param $className
     * @return bool
     */
    public function loadClass($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';

        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        $fileVendors = getcwd() . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . $fileName;
        $fileCore = getcwd() . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($fileVendors)) {
            include_once($fileVendors);
        }

        if (is_file($fileCore)) {
            include_once($fileCore);
        }
    }
}
