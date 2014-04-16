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
    public $preRemove = array();

    /**
     * @var array
     */
    public $postRemove = array();

    /**
     * @var array
     */
    public $prePersist = array();

    /**
     * @var array
     */
    public $postPersist = array();

    /**
     * @var array
     */
    public $preUpdate = array();

    /**
     * @var array
     */
    public $postUpdate = array();

    /**
     * @var array
     */
    public $postLoad = array();

    /**
     * @var array
     */
    public $onFlush = array();
}
