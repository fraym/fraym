<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class BlockHistory
 * @package Fraym\Block\Entity
 * @ORM\Table(name="block_histories")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class BlockHistory extends \Fraym\Entity\BaseEntity
{
    const DELETED = 'deleted';
    const ADDED = 'added';
    const EDITED = 'edited';

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $contentId
     *
     * @ORM\Column(name="content_id", type="string", length=255, nullable=false)
     */
    protected $contentId;

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
     * @ORM\ManyToOne(targetEntity="\Fraym\Block\Entity\BlockExtension")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="extension_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $extension;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Block\Entity\Block", inversedBy="refBlocks")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="id")
     */
    protected $byRef;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Block", mappedBy="byRef")
     */
    protected $refBlocks;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Block\Entity\Block", inversedBy="histories")
     * @ORM\JoinColumn(name="from_block_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $from;

    /**
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\User\Entity\User", inversedBy="blockHistory", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /** @ORM\Column(type="string", length=7) */
    private $type;

    public function setType($type)
    {
        if (!in_array($type, array(self::DELETED, self::ADDED, self::EDITED))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->type = $type;
    }

    public function __construct()
    {
        $this->refBlocks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date = new \Datetime('NOW');
    }
}
