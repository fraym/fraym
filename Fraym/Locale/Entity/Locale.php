<?php

namespace Fraym\Locale\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;

/**
 * Locales
 *
 * @ORM\Table(name="locales")
 * @ORM\Entity
 */
class Locale extends \Fraym\Entity\BaseEntity
{
    /**
     * @var integer $localeId
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", length=50, nullable=true, unique=true)
     * @FormField(label="Locale string", validation={"notEmpty", "unique"})
     */
    protected $locale;

    /**
     * @var string $languageName
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @FormField(label="Language name", validation={"notEmpty"})
     */
    protected $name;

    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     * @FormField(label="Country", validation={"notEmpty", "unique"})
     */
    protected $country;

    /**
     * @var boolean $default
     *
     * @ORM\Column(name="default_locale", type="boolean")
     * @FormField(label="Is default locale", type="checkbox")
     */
    protected $default = false;

    /**
     * @ORM\Column(name="date_time_format", type="string", length=255, nullable=false)
     * @FormField(label="Date time format", validation={"notEmpty"})
     */
    protected $dateTimeFormat;

    /**
     * @ORM\Column(name="date_format", type="string", length=255, nullable=false)
     * @FormField(label="Date format", validation={"notEmpty"})
     */
    protected $dateFormat;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Menu\Entity\MenuItemTranslation",
     * mappedBy="locale", cascade={"all"}, fetch="EAGER")
     */
    protected $menuItemTranslations;

    public function __toString()
    {
        return (string)$this->name;
    }

    public function __construct()
    {
        $this->menuItemTranslations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dateTimeFormat = '%Y-%m-%d %T';
        $this->dateFormat = '%Y-%m-%d';
    }
}
