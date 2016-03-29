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
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @param null $blockConfig
     */
    public function getBlockConfig($blockConfig = null)
    {
        $imageLink = '';
        $imageLinkId = isset($blockConfig->image_link) ? (string)$blockConfig->image_link : null;

        if (is_numeric($imageLinkId)) {
            $menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($imageLinkId);
            if ($menuItem) {
                $imageLink = $menuItem->getCurrentTranslation()->title;
            }
        }
        $this->view->assign('imageLink', $imageLink);
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->render('BlockConfig');
    }

    /**
     * @param $xml
     */
    public function render($xml)
    {
        foreach ($xml as $field => $val) {
            $val = (string)$val;
            if ($field === 'image_link') {
                if (is_numeric($val)) {
                    $menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($val);
                    if ($menuItem) {
                        $val = $this->route->buildFullUrl($menuItem, true);
                    } else {
                        $val = null;
                    }
                }
            }
            $this->view->assign($field, $val);
        }
        $this->view->setTemplate('Block');
    }
}
