<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */

namespace Test\HtmlEditor;

class HtmlEditorTest extends \PHPUnit_Framework_TestCase
{
    private $diContainer = null;
    /**
     * @var \Fraym\Database\Database
     */
    private $db = null;

    public function setUp()
    {
        global $diContainer;
        $this->diContainer = $diContainer;
        $this->db = $this->diContainer->get('Fraym\Database\Database')->connect();
    }

    public function testBuildMenuItemArray()
    {
        /** @var $obj \Extension\HtmlEditor\HtmlEditor */
        $obj = $this->diContainer->get('Extension\HtmlEditor\HtmlEditor');
        $this->assertTrue(is_array($obj->buildMenuItemArray()));
    }
}