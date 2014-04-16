<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Image;

/**
 * Class ImageController
 * @package Fraym\Image
 * @Injectable(lazy=true)
 */
class ImageController extends \Fraym\Core
{
    /**
     * @param null $blockConfig
     */
    public function getBlockConfig($blockConfig = null)
    {
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->render('BlockConfig.tpl');
    }

    /**
     * @param $xml
     */
    public function render($xml)
    {
        foreach ($xml as $field => $val) {
            $this->view->assign($field, (string)$xml->$field);
        }
        $this->view->setTemplate('Block');
    }
}
