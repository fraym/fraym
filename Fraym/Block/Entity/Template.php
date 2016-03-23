<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;

/**
 * Class Template
 * @package Fraym\Block\Entity
 * @ORM\Table(name="block_templates")
 * @ORM\Entity
 */
class Template extends \Fraym\Entity\BaseEntity
{

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @FormField(label="Template name", validation={"notEmpty","unique"})
     */
    protected $name;

    /**
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     * @FormField(label="Template file", validation={"notEmpty"}, type="filepath", fileFilter="*.tpl", singleFileSelect=true)
     */
    protected $template;

    public function __toString()
    {
        return $this->name;
    }
}
