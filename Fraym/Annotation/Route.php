<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
final class Route extends Annotation
{
    /**
     * @var null
     */
    public $name = null;

    /**
     * @var array
     */
    public $permission = [];

    /**
     * @var bool
     */
    public $regex = false;

    /**
     * @var bool
     */
    public $contextCallback = [];
}
