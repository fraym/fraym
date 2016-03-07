<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Template;

/**
 * Class DynamicTemplateController
 * @package Fraym\Menu
 * @Injectable(lazy=true)
 */
class DynamicTemplateController extends \Fraym\Core
{
    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @param null $blockConfig
     */
    public function getBlockConfig($selectOptions, $blockConfig = null)
    {
        $this->view->assign('selectOptions', $selectOptions);
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->render('BlockConfig.tpl');
    }

    /**
     * @param $html
     */
    public function renderConfig($html, $variables)
    {
        $this->view->assign('config', $variables);
        $this->view->render("string:$html");
    }

    /**
     * @param $template
     * @param $variables
     */
    public function render($template, $variables)
    {
        $this->view->assign('config', $variables);
        if(!empty($template)) {
            $this->view->setTemplate($template);
        } else {
            $this->view->setTemplate("string:");
        }
    }
}
