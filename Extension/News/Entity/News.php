<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\News\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;
use Fraym\Annotation\LifecycleCallback;
use \Gedmo\Mapping\Annotation as Gedmo;
use \Gedmo\Translatable\Translatable;

/**
 * Class News
 * @package Extension\News\Entity
 * @ORM\Table(name="news")
 * @ORM\Entity
 */
class News extends \Fraym\Entity\BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", nullable=false)
     * @FormField(label="Title", validation={"notEmpty"})
     * @Gedmo\Translatable
     */
    protected $title;

    /**
     * @ORM\Column(name="subtitle", type="string", nullable=true)
     * @FormField(label="Subtitle")
     * @Gedmo\Translatable
     */
    protected $subtitle;

    /**
     * @ORM\Column(name="short_description", type="text", nullable=true)
     * @FormField(label="Short description", type="rte", rteConfigFile="Template/Default/Extension/News/RteConfig.tpl")
     * @Gedmo\Translatable
     */
    protected $shortDescription;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @FormField(label="Description", type="rte", rteConfigFile="Template/Default/Extension/News/RteConfig.tpl")
     * @Gedmo\Translatable
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\User\Entity\User", inversedBy="news")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @FormField(label="Author", type="select", createNew=true)
     */
    protected $author;

    /**
     * @ORM\Column(name="Image", type="string", nullable=true)
     * @FormField(label="Image", type="filepath")
     */
    protected $image;

    /**
     * @ORM\Column(name="date", type="datetime", nullable=false)
     * @FormField(label="Date", type="datetime", validation={"notEmpty", "date"})
     */
    protected $date;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\ManyToMany(targetEntity="\Extension\News\Entity\Category", cascade={"persist"})
     * @FormField(label="Category", type="multiselect", createNewInline="name")
     */
    protected $categories;

    /**
     * @ORM\ManyToMany(targetEntity="\Fraym\Site\Entity\Site", cascade={"persist"})
     * @FormField(label="Site", type="multiselect", createNew=true)
     */
    protected $sites;

    /**
     * @ORM\ManyToMany(targetEntity="\Extension\News\Entity\Tag", inversedBy="news", cascade={"persist"})
     * @FormField(label="Tags", type="multiselect", createNewInline="name")
     */
    protected $tags;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created = new \DateTime();
        $this->date = new \DateTime();
        $route = $this->getServiceLocator()->get('Fraym\Route\Route');
        if ($route->getCurrentMenuItem()) {
            $this->sites->add($route->getCurrentMenuItem()->site);
        }
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        if (is_object($date)) {
            $this->date = $date;
        } else {
            $this->date = empty($date) ? new \DateTime() : \DateTime::createFromFormat(
                'Y-m-d H:i',
                date('Y-m-d H:i', strtotime($date))
            );
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }
}
