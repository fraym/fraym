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
 * Class MenuItemTranslation
 * @package Fraym\Menu\Entity
 * @ORM\Table(name="menu_item_translations", uniqueConstraints={@ORM\UniqueConstraint(name="unique_idx", columns={"menu_item_id", "locale_id"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 */
class MenuItemTranslation extends \Fraym\Entity\BaseEntity
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
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var string $subtitle
     *
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     */
    protected $subtitle;

    /**
     * @var string $pageTitle
     *
     * @ORM\Column(name="page_title", type="string", length=255, nullable=true)
     */
    protected $pageTitle;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * @var string $externalUrl
     *
     * @ORM\Column(name="external_url", type="boolean", nullable=false)
     */
    protected $externalUrl = false;

    /**
     * @var string $shortDesc
     *
     * @ORM\Column(name="short_desc", type="string", length=1000, nullable=true)
     */
    protected $shortDescription;

    /**
     * @var text $longDesc
     *
     * @ORM\Column(name="long_desc", type="text", nullable=true)
     */
    protected $longDescription;

    /**
     * @var text $keywords
     *
     * @ORM\Column(name="keywords", type="text", nullable=true)
     */
    protected $keywords;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Menu\Entity\MenuItem", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinColumn(name="menu_item_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $menuItem;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Locale\Entity\Locale", inversedBy="menuItemTranslations")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $locale;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Block", mappedBy="menuItemTranslation", cascade={"all"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $blocks;

    /**
     * @var boolean $visible
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     */
    protected $visible;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUrlIfEmpty()
    {
        if (empty($this->url) && $this->menuItem->parent !== null) {
            $urls = [$this->title];
            $menuItem = $this->menuItem;
            do {
                $menuItem = $menuItem->parent;
                if ($menuItem && $menuItem->getCurrentTranslation()) {
                     $urls[] = $menuItem->getCurrentTranslation()->url;
                }
            } while ($menuItem);
            $urls = array_reverse($urls);
            $url = '/' . implode('/', $urls);

            $this->setUrl($url);
        }
    }

    /**
     * @param $url
     */
    public function setUrl($url)
    {
        if (!empty($url) && $this->externalUrl === false) {
            /**
             * @var \Fraym\Database\Database $em
             */
            $route = $this->getServiceLocator()->get('Fraym\Route\Route');
            $parts = explode('/', $url);

            foreach ($parts as &$part) {
                $part = $route->createSlug($part, '-', true);
            }

            $this->url = '/' . trim(implode('/', $parts), '/');
        } else {
            $this->url = $url;
        }
    }

    public function __construct()
    {
        /**
         * @var \Fraym\Locale\Locale $locale
         */
        $locale = $this->getServiceLocator()->get('Fraym\Locale\Locale');
        $this->locale = $locale->getDefaultLocale();
        $this->externalUrl = false;
        $this->visible = true;
        $this->active = true;
    }
}
