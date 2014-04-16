<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\FlexSlider;

/**
 * @package Extension\FlexSlider
 * @Injectable(lazy=true)
 */
class FlexSliderController extends \Fraym\Core
{
    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @param mixed $blockConfig
     */
    public function getBlockConfig($blockConfig = null)
    {
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->render('BlockConfig.tpl');
    }

    /**
     * @param $blockId int
     * @param $numberOfSlides int
     * @param $config array
     */
    public function render($blockId, $numberOfSlides, $config)
    {
        $views = array();
        for ($i = 0; $i < intval($numberOfSlides); $i++) {
            $views[] = "flexslider-{$blockId}-" . ($i + 1);
        }
        $this->view->assign('config', $config);
        $this->view->assign('views', $views);
        $this->view->setTemplate('Block');
    }
}
