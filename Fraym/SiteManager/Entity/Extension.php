<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\SiteManager\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class Extension
 * @package Fraym\SiteManager\Entity
 * @ORM\Table(name="site_manager_extensions")
 * @ORM\Entity
 */
class Extension extends \Fraym\Entity\BaseEntity
{

    public function __construct()
    {
        $this->configurations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @var integer $extensionId
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     */
    protected $class;

    /**
     * @ORM\Column(name="method", type="string", length=255, nullable=false)
     */
    protected $method;

    /**
     * @ORM\Column(name="icon_css_class", type="string", length=255, nullable=true)
     */
    protected $iconCssClass = 'fa fa-arrow-circle-o-right';

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active = true;

    /**
     * @ORM\Column(name="sorter", type="integer", nullable=false)
     */
    protected $sorter = 0;
}
