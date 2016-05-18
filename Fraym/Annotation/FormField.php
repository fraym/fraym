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
final class FormField extends Annotation
{
    /**
     * field title
     *
     * @var string
     */
    public $label;

    /**
     * field type
     *
     * @var string
     */
    public $type = 'text';

    /**
     * sort for oneToMany
     *
     * @var array
     */
    public $sort = [];

    /**
     * field validations
     *
     * @var array
     */
    public $validation = [];

    /**
     * Field options for select, multiselect, radio or checkbox
     *
     * @var array
     */
    public $options = [];

    /**
     * is field editable
     *
     * @var bool
     */
    public $readOnly = false;

    /**
     * able to create a new entry in a new overlay
     *
     * @var bool
     */
    public $createNew = false;

    /**
     * able to create a new entry without a new overlay
     *
     * @var string
     */
    public $createNewInline = '';

    /**
     * rte custom config for this field
     *
     * @var string
     */
    public $rteConfig = '';

    /**
     * rte custom config tpl file
     *
     * @var string
     */
    public $rteConfigFile = '';

    /**
     * for type=file, to select only elements with a filetype
     *
     * @var string
     */
    public $fileFilter = '';

    /**
     * for type=file, to set only absolute path
     *
     * @var string
     */
    public $absolutePath = true;

    /**
     * for type=file to select multiple or a single file
     *
     * @var bool
     */
    public $singleFileSelect = true;
}
