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
 * Class Group
 * @package Fraym\EntityManager\Entity
 * @ORM\Table(name="managed_entity_groups")
 * @ORM\Entity
 */
class Group extends \Fraym\Entity\BaseEntity
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
     * @ORM\OneToMany(targetEntity="\Fraym\EntityManager\Entity\Entity", mappedBy="group")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $entites;

    public function __construct()
    {
        $this->entites = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
