<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\ServiceLocator;

/**
 * Class ServiceLocator
 * @package Fraym\ServiceLocator
 */
class ServiceLocator
{
    /**
     * @var null
     */
    private $diContainer = null;

    public function __construct()
    {
        $this->diContainer = $GLOBALS['diContainer'];
        return $this;
    }

    /**
     * Call default doctrine entity manager methods
     *
     * @param $method
     * @param $param
     * @return bool|mixed
     */
    public function __call($method, $param)
    {
        if (is_object($this->diContainer) && method_exists($this->diContainer, $method)) {
            return call_user_func_array([&$this->diContainer, $method], $param);
        }
        return null;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->diContainer->get(ltrim($name, '\\'));
    }
}
