<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Registry\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;

/**
 * Class Registry
 * @package Fraym\Registry\Entity
 * @ORM\Table(name="registry")
 * @ORM\Entity
 */
class Registry extends \Fraym\Entity\BaseEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="repository_key", type="string", length=32, unique=false, nullable=true)
     */
    protected $repositoryKey;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(name="class_name", type="string", length=255, nullable=false, unique=true)
     */
    protected $className;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\Column(name="deletable", type="boolean", nullable=false)
     */
    protected $deletable;

    /**
     * @var integer $version
     *
     * @ORM\Column(name="version", type="string", nullable=false)
     */
    protected $version;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Registry\Entity\Config", mappedBy="registry", orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $configurations;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Extension", mappedBy="registry", orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $blockExtensions;

    public function __construct()
    {
        $this->created = new \Datetime('NOW');
        $this->deletable = true;
        $this->configurations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blockExtensions = new \Doctrine\Common\Collections\ArrayCollection();
    }
}