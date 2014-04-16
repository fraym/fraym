<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Translation\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;
use \Gedmo\Mapping\Annotation as Gedmo;
use \Gedmo\Translatable\Translatable;

/**
 * Class Translation
 * @package Fraym\Translation\Entity
 * @ORM\Table(name="translations",uniqueConstraints={@ORM\UniqueConstraint(name="unique_idx", columns={"translation_key"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 */
class Translation extends \Fraym\Entity\BaseEntity implements Translatable
{
    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $key
     *
     * @ORM\Column(name="translation_key", type="string", length=255, nullable=false)
     * @FormField(label="Key", validation={"notEmpty", "unique"})
     */
    protected $key;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="translation_value", type="text", nullable=true)
     * @FormField(label="Value", validation={"notEmpty"})
     */
    protected $value;

    /**
     * @var datetime $timestamp
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    protected $dateCreated;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return null
     */
    public function getDateCreated()
    {
        return $this->dateCreated ? $this->dateCreated->format('Y-m-d H:i:s') : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->key;
    }
}
