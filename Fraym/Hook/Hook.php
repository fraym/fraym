<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Hook;

/**
 * Class Hook
 * @package Fraym\Hook
 * @Injectable(lazy=true)
 */
class Hook
{

    /**
     * @Inject
     * @var \Fraym\FileManager\FileManager
     */
    protected $fileManager;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @var array
     */
    private $_hooks = [];

    /**
     * @return $this
     */
    public function load()
    {
        $phpFiles = $this->fileManager->findFiles('Hook' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($phpFiles as $phpFile) {
            require_once($phpFile);
            $loadedClasses = get_declared_classes();
            $class = end($loadedClasses);
            $reflector = new \ReflectionClass($class);
            $parentClass = $reflector->getParentClass();
            if ($parentClass) {
                $shortNameClass = ltrim($reflector->getName(), 'Hook\\');
                $shortNameClassHook = $parentClass->getName();

                if ($shortNameClass === $shortNameClassHook) {
                    $this->serviceLocator->set($parentClass->name, $this->serviceLocator->get($class));
                    $this->_hooks[$class] = $parentClass->name;
                }
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getHooks()
    {
        return $this->_hooks;
    }
}
