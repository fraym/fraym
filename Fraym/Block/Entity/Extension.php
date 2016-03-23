<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class Extension
 * @package Fraym\Block\Entity
 * @ORM\Table(name="block_extensions")
 * @ORM\Entity
 */
class Extension extends \Fraym\Entity\BaseEntity
{
    /**
     * @var integer $extensionId
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string $module
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     */
    protected $class;

    /**
     * @var string $configMethod
     *
     * @ORM\Column(name="config_method", type="string", length=255, nullable=true)
     */
    protected $configMethod;

    /**
     * @var string $saveMethod
     *
     * @ORM\Column(name="save_method", type="string", length=255, nullable=true)
     */
    protected $saveMethod;

    /**
     * @var string $execMethod
     *
     * @ORM\Column(name="exec_method", type="string", length=255, nullable=true)
     */
    protected $execMethod;

    /**
     * @var string $blockInformationMethod
     *
     * @ORM\Column(name="metadata_method", type="string", length=255, nullable=true)
     */
    protected $metadataMethod;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Block", mappedBy="extension", orphanRemoval=true, cascade={"all"})
     */
    protected $blocks;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Registry\Entity\Registry")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registry_id", referencedColumnName="id")
     * })
     */
    protected $registry;

    public function __toString()
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->blocks = new \Doctrine\Common\Collections\ArrayCollection();
    }

}