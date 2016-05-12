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
final class LifecycleCallback extends Annotation
{
    /**
     * @var array
     */
    public $preRemove = [];

    /**
     * @var array
     */
    public $postRemove = [];

    /**
     * @var array
     */
    public $prePersist = [];

    /**
     * @var array
     */
    public $postPersist = [];

    /**
     * @var array
     */
    public $preUpdate = [];

    /**
     * @var array
     */
    public $postUpdate = [];

    /**
     * @var array
     */
    public $postLoad = [];

    /**
     * @var array
     */
    public $onFlush = [];
}
