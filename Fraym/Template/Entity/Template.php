<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Template\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Fraym\Annotation\FormField;

/**
 * Class Template
 * @package Fraym\Template\Entity
 * @ORM\Table(name="templates")
 * @ORM\Entity
 */
class Template extends \Fraym\Entity\BaseEntity
{
    /**
     * @var integer $templateId
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @FormField(label="Name", validation={"notEmpty"})
     */
    protected $name;

    /**
     * @ORM\Column(name="file_path", type="string", length=300, nullable=true)
     * @FormField(label="Template", type="filepath", fileFilter="*.tpl,*.html", absolutePath=false, validation={"notEmpty"})
     */
    protected $filePath;

    /**
     * @ORM\OneToMany(targetEntity="\Fraym\Menu\Entity\MenuItem", mappedBy="template")
     */
    protected $menuItems;

    public function getHtml()
    {
        $filePath = realpath(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->filePath));
        if ($filePath) {
            if (is_file($filePath) && is_readable($filePath)) {
                return file_get_contents($filePath);
            } else {
                throw new \Exception('Template file not found: ' . $filePath . ' or it is not readable.');
            }
        }
        throw new \Exception('Template file not found: ' . $this->filePath);
    }

    public function __toString()
    {
        return $this->name;
    }
}
