<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Route\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class VirtualRoute
 * @package Fraym\Route\Entity
 * @ORM\Table(name="virtual_routes")
 * @ORM\Entity
 */
class VirtualRoute extends \Fraym\Entity\BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="`key`", type="string", length=255, unique=true)
     */
    protected $key;

    /**
     * @ORM\Column(name="route", type="string", length=255, unique=true)
     */
    protected $route;

    /**
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     */
    protected $class;

    /**
     * @ORM\Column(name="method", type="string", length=255, nullable=false)
     */
    protected $method;
}
