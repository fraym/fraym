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

/**
 * Class Tag
 * @package Extension\News\Entity
 * @ORM\Table(name="news_tags")
 * @ORM\Entity
 */
class Tag extends \Fraym\Entity\BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", nullable=true, unique=true)
     * @FormField(label="Name", validation={"notEmpty", "unique"})
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="\Extension\News\Entity\News", mappedBy="tags")
     */
    protected $news;

    public function __construct()
    {
        $this->news = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }
}
