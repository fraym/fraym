<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\HtmlEditor;

/**
 * Class HtmlEditorController
 * @package Extension\HtmlEditor
 * @Injectable(lazy=true)
 */
class HtmlEditorController extends \Fraym\Core
{
    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Extension\HtmlEditor\HtmlEditor
     */
    protected $htmlEditor;

    /**
     * @param null $blockConfig
     */
    public function getBlockConfig($blockConfig = null)
    {
        $locales = $this->db->getRepository('\Fraym\Locale\Entity\Locale')->findAll();
        $htmlData = array();

        if ($blockConfig !== null) {
            $result = $blockConfig->xpath('html');
            while (list(, $node) = each($result)) {
                $attr = $node->attributes();
                $locale = (string)$attr->locale;
                $htmlData[$locale] = (string)(string)$node;
            }
        }
        $this->view->assign('blockConfig', $htmlData);
        $this->view->assign('locales', $locales);
        $this->view->render('BlockConfig');
    }

    /**
     * @param $html
     */
    public function renderHtml($html)
    {
        $this->view->assign('content', $html);
        $this->view->setTemplate('Block');
    }
}