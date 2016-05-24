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
     * @Inject
     * @var \Fraym\Block\Block
     */
    protected $block;

    /**
     * @param $selectOptions
     * @param null $blockConfig
     */
    public function getBlockConfig($selectOptions, $blockConfig = null)
    {
        $this->view->assign('selectOptions', $selectOptions, false);
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->render('BlockConfig');
    }

    /**
     * @param $html
     * @param $locales
     * @param $variables
     */
    public function renderConfig($html, $locales, $variables)
    {
        $this->view->assign('locales', $locales);
        $this->view->assign('config', $variables);
        $this->view->render("string:$html");
    }

    /**
     * @param $template
     * @param $locale
     * @param $variables
     * @param $dataSource
     */
    public function render($template, $locale, $variables, $dataSource = null)
    {
        $this->view->assign('refreshBlock', $this->block->inEditMode() && $this->request->isXmlHttpRequest());
        $this->view->assign('locale', $locale);
        $this->view->assign('dataSource', $dataSource);
        $this->view->assign('config', $variables);
        if (!empty($template)) {
            $this->view->setTemplate($template);
        } else {
            $this->view->setTemplate("string:");
        }
    }
}
