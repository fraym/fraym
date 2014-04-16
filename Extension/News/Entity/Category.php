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
use \Gedmo\Mapping\Annotation as Gedmo;
use \Gedmo\Translatable\Translatable;

/**
 * Class Category
 * @package Extension\News\Entity
 * @ORM\Table(name="news_categories")
 * @ORM\Entity
 */
class Category extends \Fraym\Entity\BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", nullable=true)
     * @FormField(label="Name", validation={"notEmpty", "unique"})
     */
    protected $name;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @ORM\OneToMany(targetEntity="\Extension\News\Entity\News", mappedBy="categories", fetch="EXTRA_LAZY")
     */
    protected $news;

    public function __construct()
    {
        $this->news = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->name ? : '';
    }
}
