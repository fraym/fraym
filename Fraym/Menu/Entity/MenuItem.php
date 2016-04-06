<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Menu\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class MenuItem
 * @package Fraym\Menu\Entity
 * @ORM\Table(name="menu_items"))
 * @ORM\Entity
 */
class MenuItem extends \Fraym\Entity\BaseEntity
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
     * @ORM\ManyToOne(targetEntity="\Fraym\Menu\Entity\MenuItem", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Menu\Entity\MenuItem", mappedBy="parent", cascade={"all"})
     * @ORM\OrderBy({"sorter" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Menu\Entity\MenuItemTranslation", mappedBy="menuItem", cascade={"persist"})
     */
    protected $translations;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Block", mappedBy="menuItem", cascade={"all"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $blocks;

    /**
     * @var integer $sorter
     *
     * @ORM\Column(name="sorter", type="integer", nullable=true)
     */
    protected $sorter;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="\Fraym\Template\Entity\Template", inversedBy="menuItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="template_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    protected $template;

    /**
     * @var boolean $caching
     *
     * @ORM\Column(name="caching", type="boolean", nullable=false)
     */
    protected $caching;

    /**
     * @ORM\Column(name="is_404", type="boolean", nullable=false)
     */
    protected $is404;

    /**
     * @var boolean $https
     *
     * @ORM\Column(name="https", type="boolean", nullable=false)
     */
    protected $https;

    /**
     * @var boolean $checkPermission
     *
     * @ORM\Column(name="check_permission", type="boolean", nullable=false)
     */
    protected $checkPermission;

    /**
     * @var Locales
     *
     * @ORM\ManyToOne(targetEntity="\Fraym\Site\Entity\Site", inversedBy="menuItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    protected $site;

    /**
     * @return null
     */
    public function getCurrentTranslation()
    {
        if (get_class($this->translations) == 'Doctrine\ORM\PersistentCollection') {
            $locale = $this->getServiceLocator()->get('Fraym\Locale\Locale');
            $localeId = $locale->getLocale()->id;
            foreach ($this->translations as $translation) {
                if ($translation->locale->id === $localeId) {
                    return $translation;
                }
            }
        }

        return null;
    }

    /**
     * @param $localeId
     * @return mixed
     */
    public function getTranslation($localeId)
    {
        foreach ($this->translations as $translation) {
            if ($translation->locale->id === $localeId) {
                return $translation;
            }
        }
    }

    /**
     * @param null $route
     * @param bool $with_protocol
     * @return string
     */
    public function getUrl($route = null, $with_protocol = false)
    {
        if ($this->getCurrentTranslation() &&
            $this->getCurrentTranslation()->externalUrl) {

            return $this->getCurrentTranslation()->url;
        }
        $url = ($route ?
                rtrim($route->getCurrentDomain(), '/') : '') .
                ($this->getCurrentTranslation() ? '/' .
                    ltrim(
                        $this->getCurrentTranslation()->url,
                        '/'
                    )
                    : '');

        if ($with_protocol === true) {
            $url = ($this->https ? 'https://' : 'http://') . $url;
        }
        return $url;
    }

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->is404 = false;
        $this->caching = true;
        $this->https = false;
        $this->checkPermission = false;
    }
}
