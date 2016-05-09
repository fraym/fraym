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
final class Registry extends Annotation
{
    /**
     * DB Entries to create
     *
     * @var array
     */
    public $entity = array();

    /**
     * DB Entries to be updated
     *
     * @var array
     */
    public $updateEntity = array();

    /**
     * Registry config entries
     *
     * @var array
     */
    public $config = array();

    /**
     * Translations
     *
     * @var array
     */
    public $translation = array();

    /**
     * Files belongs to this package
     *
     * @var array
     */
    public $files = array();

    /**
     * Composer vendor dependencies
     *
     * @var array
     */
    public $composer = array();

    /**
     * Callback function that is called after registration
     *
     * @var null
     */
    public $onRegister = null;

    /**
     * Callback function that is called after all extension are registred
     *
     * @var null
     */
    public $afterRegister = null;

    /**
     * Callback function that is called after unregistration
     *
     * @var null
     */
    public $onUnregister = null;

    /**
     * Callback function that is called on update
     *
     * @var null
     */
    public $onUpdate = null;

    /**
     * Delete files on remove
     *
     * @var bool
     */
    public $cleanUpOnRemove = true;

    /**
     * If the package is uninstallable on the registry manager
     *
     * @var bool
     */
    public $deletable = true;

    /**
     * Package name
     *
     * @var string
     */
    public $name = '';

    /**
     * Package description
     *
     * @var string
     */
    public $description = '';

    /**
     * Package version
     *
     * @var string
     */
    public $version = '';

    /**
     * Package author
     *
     * @var string
     */
    public $author = '';

    /**
     * Package website
     *
     * @var string
     */
    public $website = '';

    /**
     * Unique repository key
     *
     * @var string
     */
    public $repositoryKey = null;

    /**
     * @var string
     */
    public $file = null;
}
