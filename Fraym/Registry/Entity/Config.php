<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Registry\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;

/**
 * Class Config
 * @package Fraym\Registry\Entity
 * @ORM\Table(name="config")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Config extends \Fraym\Entity\BaseEntity
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
     * @ORM\Column(name="description", type="text", nullable=true)
     * @FormField(label="Description", type="description")
     */
    protected $description;

    /**
     * @var text $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     * @FormField(label="Name", readOnly=true, validation={"notEmpty", "unique"})
     */
    protected $name;

    /**
     * @var text $value
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     * @FormField(label="Value", type="textarea", validation={"notEmpty"})
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Registry\Entity\Registry", inversedBy="configurations")
     * @ORM\JoinColumn(name="registry_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $registry;

    /**
     * @var datetime $changeDate
     *
     * @ORM\Column(name="date_changed", type="datetime", nullable=true)
     */
    protected $dateChanged;

    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @ORM\PreUpdate
     */
    protected function prePersist()
    {
        $this->dateChanged = new \Datetime();
    }
}