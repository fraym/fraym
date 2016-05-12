<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\User\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Fraym\Annotation\FormField;

/**
 * Class User
 * @package Fraym\User\Entity
 * @ORM\Table(name="users")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User extends \Fraym\Entity\BaseEntity
{
    const USER_PREFIX = 'USER:';

    /**
     * @var integer $userId
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $email
     * @ORM\Column(name="email", type="string", length=255, nullable=false, unique=true)
     * @FormField(label="E-Mail", validation={"notEmpty","email"})
     */
    protected $email;

    /**
     * @var string $username
     * @ORM\Column(name="username", type="string", length=255, nullable=false, unique=true)
     * @FormField(label="Username", validation={"unique", "notEmpty"})
     */
    protected $username;

    /**
     * @var string $password
     * @ORM\Column(name="password", type="string", length=64, nullable=false)
     * @FormField(label="Password", type="password", validation={"minLength" = 6})
     */
    private $password;

    /**
     * @var string $profilePicture
     * @ORM\Column(name="profile_picture", type="string", length=255, nullable=true)
     * @FormField(label="Profile picture", type="filepath")
     */
    protected $profilePicture;

    /**
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @FormField(label="First name")
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @FormField(label="Last name")
     */
    protected $lastName;

    /**
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     * @FormField(label="Company")
     */
    protected $company;

    /**
     * @ORM\Column(name="zip", type="string", length=255, nullable=true)
     * @FormField(label="Zip")
     */
    protected $zip;

    /**
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @FormField(label="City")
     */
    protected $city;

    /**
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     * @FormField(label="Street")
     */
    protected $street;

    /**
     * @ORM\Column(name="street_number", type="string", length=50, nullable=true)
     * @FormField(label="Street Number")
     */
    protected $streetNumber;

    /**
     * @ORM\Column(name="country", type="string", length=100, nullable=true)
     * @FormField(label="Country")
     */
    protected $country;

    /**
     * @var string $password
     *
     * @ORM\Column(name="salt", type="string", length=32, nullable=false)
     */
    protected $salt;

    /**
     * @var boolean $status
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     * @FormField(label="Active", type="checkbox", validation={"notEmpty"})
     */
    protected $active;

    /**
     * @var boolean $isOnline
     *
     * @ORM\Column(name="is_online", type="boolean", nullable=false)
     */
    protected $isOnline;

    /**
     * @var datetime $dateCreated
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    protected $dateCreated;

    /**
     * @ORM\Column(name="date_last_login", type="datetime", nullable=false)
     */
    protected $lastLogin;

    /**
     * @ORM\ManyToMany(targetEntity="\Fraym\User\Entity\Group", inversedBy="users", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="users_groups",
     *   joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     * @FormField(label="Groups", type="multiselect", validation={"notEmpty"}, createNew=true)
     */
    protected $groups;

    /**
     * @ORM\Column(name="identifier", type="string", length=32, nullable=false, unique=true)
     */
    protected $identifier;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\Block", mappedBy="user", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $blocks;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Block\Entity\ChangeSet", mappedBy="user", cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $changeSets;

    /**
     * @param $identifier
     * @return $this
     */
    public function setIdentifier($identifier) {
        $this->identifier = self::USER_PREFIX . $identifier;
        return $this;
    }

    /**
     * @return array
     */
    public function getIdentifiersFromGroups()
    {
        $groups = [];
        foreach ($this->groups as $group) {
            $groups[] = $group->identifier;
        }
        return $groups;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if ($this->identifier === null) {
            $this->setIdentifier($this->username);
        }
    }

    /**
     *
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime('NOW');
        $this->lastLogin = new \DateTime('NOW');
        $this->salt = md5(microtime());
        $this->active = true;
        $this->blocks = new ArrayCollection();
        $this->changeSets = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->isOnline = false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->username;
    }

    /**
     * @return null|string
     */
    public function getDateCreated()
    {
        return $this->dateCreated ? $this->dateCreated->format('Y-m-d H:i:s') : null;
    }

    /**
     * @return null|string
     */
    public function getLastLogin()
    {
        return $this->lastLogin ? $this->lastLogin->format('Y-m-d H:i:s') : null;
    }

    /**
     * @param $password
     * @return User
     */
    public function setPassword($password)
    {
        if (!empty($password)) {
            $this->password = hash('sha256', $password . $this->salt);
        }
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return $this->password === hash('sha256', $password . $this->salt);
    }
}
