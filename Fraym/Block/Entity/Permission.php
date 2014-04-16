<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Blocks
 *
 * @ORM\Table(name="block_permissions",uniqueConstraints={@ORM\UniqueConstraint(name="unique_idx", columns={"identifier", "extension_id"})})
 * @ORM\Entity
 */
class Permission extends \Fraym\Entity\BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="identifier", type="string", length=32, nullable=false)
     */
    protected $identifier;

    /** @ORM\Column(type="string", columnDefinition="SET('add', 'edit', 'move', 'delete', 'copy')") */
    protected $permission;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\Block\Entity\BlockExtension")
     * @ORM\JoinColumn(name="extension_id", referencedColumnName="id", nullable=false)
     */
    protected $extension;
}
