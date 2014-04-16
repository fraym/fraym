<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Site\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;

/**
 * Class Domain
 * @package Fraym\Site\Entity
 * @ORM\Table(name="domains")
 * @ORM\Entity
 */
class Domain extends \Fraym\Entity\BaseEntity
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
     * @ORM\Column(name="address", type="string", length=255, unique=true)
     * @FormField(label="Address", validation={"notEmpty"})
     */
    protected $address;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Site\Entity\Site", inversedBy="domains")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", onDelete="CASCADE")
     * @FormField(label="Website", type="select", validation={"notEmpty"}, createNew=true)
     */
    protected $site;

    public function __toString()
    {
        return $this->address;
    }
}
