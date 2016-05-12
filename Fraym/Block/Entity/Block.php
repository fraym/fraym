<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;
use Fraym\Annotation\LifecycleCallback;

/**
 * Class Block
 * @package Fraym\Block\Entity
 * @ORM\Table(name="blocks")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"block" = "Block", "change_set" = "ChangeSet"})
 * @LifecycleCallback(postPersist={"\Fraym\Block\Block"="clearCache"}, onFlush={"\Fraym\Block\Block"="clearCache"})
 */
class Block extends \Fraym\Entity\BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $contentId
     * @ORM\Column(name="content_id", type="string", length=255, nullable=false)
     */
    protected $contentId;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Block\Entity\Block", inversedBy="changeSets")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id")
     */
    protected $block;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var text $block
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    protected $config;

    /**
     * @var integer $position
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    protected $position;

    /**
     * @var $site
     *
     * @ORM\ManyToOne(targetEntity="\Fraym\Site\Entity\Site", inversedBy="blocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    protected $site;

    /**
     * If this is null the block will be shown on all pages that contains the block contentId
     *
     * @var Menus
     *
     * @ORM\ManyToOne(targetEntity="\Fraym\Menu\Entity\MenuItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     * })
     */
    protected $menuItem;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Menu\Entity\MenuItemTranslation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="menu_translation_id", referencedColumnName="id")
     * })
     */
    protected $menuItemTranslation;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Block\Entity\Extension", inversedBy="blocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="extension_id", referencedColumnName="id", nullable=false)
     * })
     */
    protected $extension;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Block\Entity\Block", inversedBy="refBlocks")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="id")
     */
    protected $byRef;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\User\Entity\User", inversedBy="blocks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Block", mappedBy="byRef", fetch="EXTRA_LAZY", cascade={"all"}, orphanRemoval=true)
     */
    protected $refBlocks;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\ChangeSet", fetch="EXTRA_LAZY", mappedBy="block", orphanRemoval=true)
     */
    protected $changeSets;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    public function __construct()
    {
        $this->refBlocks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->changeSets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created = new \DateTime();
    }

    public function getConfig($isAdmin = false)
    {
        if ($this->byRef) {
            if ($isAdmin && $this->byRef->changeSets->count()) {
                return $this->byRef->changeSets->last()->config;
            }
            return $this->byRef->config;
        }
        return $this->config;
    }

    public function setConfig($value)
    {
        if ($this->byRef) {
            $this->byRef->config = $value;
        }
        $this->config = $value;
    }
}
