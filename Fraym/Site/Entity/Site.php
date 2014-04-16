<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Site\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;
use Fraym\Menu\Entity\MenuItem;
use Fraym\Menu\Entity\MenuItemTranslation;

/**
 * Class Site
 * @package Fraym\Site\Entity
 * @ORM\Table(name="sites")
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 */
class Site extends \Fraym\Entity\BaseEntity
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
     * @var string $title
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @FormField(label="Name", validation={"notEmpty"})
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Site\Entity\Domain", mappedBy="site", cascade={"all"})
     */
    protected $domains;

    /**
     * @ORM\Column(name="template_dir", type="string", length=255, nullable=true)
     * @FormField(label="Template folder", type="filepath", absolutePath=false)
     */
    protected $templateDir;

    /**
     * @var boolean $caching
     *
     * @ORM\Column(name="caching", type="boolean", nullable=true)
     * @FormField(label="Caching", type="checkbox", validation={"notEmpty"})
     */
    protected $caching;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     * @FormField(label="Active", type="checkbox", validation={"notEmpty"})
     */
    protected $active;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Menu\Entity\MenuItem", mappedBy="site", cascade={"all"})
     */
    protected $menuItems;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Block", mappedBy="site", cascade={"all"})
     */
    protected $blocks;

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRootMenuItems()
    {
        $rootItems = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->menuItems as &$m) {
            if ($m->parent == null) {
                $rootItems->add($m);
            }
        }
        return $rootItems;
    }

    public function __construct()
    {
        $this->menuItems = new \Doctrine\Common\Collections\ArrayCollection();
        $this->domains = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocks = new \Doctrine\Common\Collections\ArrayCollection();
        $menuItemTranslation = new MenuItemTranslation();
        $menuItemTranslation->title = 'Home';
        $menuItemTranslation->subtitle = '';
        $menuItemTranslation->url = '';
        $menuItem = new MenuItem();
        $menuItemTranslation->menuItem = $menuItem;
        $menuItem->site = $this;
        $menuItem->translations->add($menuItemTranslation);
        $this->menuItems->add($menuItem);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
