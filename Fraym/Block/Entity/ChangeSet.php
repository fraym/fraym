<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block\Entity;

use \Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class ChangeSet
 * @package Fraym\Block\Entity
 * @ORM\Entity
 */
class ChangeSet extends Block
{
    const DELETED = 0;
    const ADDED = 1;
    const EDITED = 2;
    const MOVED = 3;

    /**
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="\Fraym\User\Entity\User", inversedBy="changeSets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    public function getHistory()
    {
        $blocks = [];
        do {
            $parent = $this->block;
            if ($parent) {
                $blocks[] = $parent;
            }
        } while ($parent);
        return $blocks;
    }
}
