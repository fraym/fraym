<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\User\Entity;

use \Doctrine\ORM\Mapping as ORM;
use \Fraym\Annotation\FormField;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Group
 * @package Fraym\User\Entity
 * @ORM\Table(name="user_groups")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Group extends \Fraym\Entity\BaseEntity
{
    const GROUP_PREFIX = 'GROUP:';

    /**
     * @var integer $userId
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     * @FormField(label="Name", type="text", validation={"notEmpty"})
     */
    protected $name;

    /**
     * @ORM\Column(name="identifier", type="string", length=32, nullable=false, unique=true)
     */
    protected $identifier;

    /**
     * @ORM\ManyToMany(targetEntity="\Fraym\User\Entity\User", mappedBy="groups", fetch="EXTRA_LAZY")
     */
    protected $users;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if ($this->identifier === null) {
            $this->setIdentifier($this->name);
        }
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function setIdentifier($identifier) {
        $this->identifier = self::GROUP_PREFIX . $identifier;
        return $this;
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
