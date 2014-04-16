<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\EntityManager\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class Entity
 * @package Fraym\EntityManager\Entity
 * @ORM\Table(name="managed_entity_classes")
 * @ORM\Entity
 */
class Entity extends \Fraym\Entity\BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="class_name", type="string", length=255, nullable=false, unique=true)
     */
    protected $className;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\EntityManager\Entity\Group", inversedBy="entities", cascade={"all"})
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $group;

    public function __toString()
    {
        return $this->name;
    }
}
