<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\FlexSlider;

use \Fraym\Block\BlockXML as BlockXML;
use Fraym\Annotation\Registry;

/**
 * @package Extension\FlexSlider
 * @Registry(
 * name="Flex Slider",
 * description="Create a responsive slider element.",
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://fraym.org",
 * repositoryKey="FRAYM_EXT_FLEXSLIDER",
 * entity={
 *      "\Fraym\Block\Entity\BlockExtension"={
 *          {
 *           "name"="Flex Slider",
 *           "description"="Create a responsive image slider.",
 *           "class"="\Extension\FlexSlider\FlexSlider",
 *           "configMethod"="getBlockConfig",
 *           "execMethod"="execBlock",
 *           "saveMethod"="saveBlockConfig"
 *           },
 *      }
 * },
 * files={
 *      "Extension/FlexSlider/*",
 *      "Extension/FlexSlider/",
 *      "Template/Default/Extension/FlexSlider/*",
 *      "Template/Default/Extension/FlexSlider/",
 *      "Public/js/fraym/extension/flexslider/*",
 *      "Public/js/fraym/extension/flexslider/",
 *      "Public/css/fraym/extension/flexslider/*",
 *      "Public/css/fraym/extension/flexslider/",
 * }
 * )
 * @Injectable(lazy=true)
 */
class FlexSlider
{
    /**
     * @Inject
     * @var \Extension\FlexSlider\FlexSliderController
     */
    protected $flexSliderController;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    /**
     * @param $blockId
     * @param BlockXML $blockXML
     * @return BlockXML
     */
    public function saveBlockConfig($blockId, \Fraym\Block\BlockXML $blockXML)
    {
        $blockConfig = $this->request->getGPAsArray();
        $customProperties = new \Fraym\Block\BlockXMLDom();
        $config = $customProperties->createElement('sliderConfig');
        foreach ($blockConfig['sliderConfig'] as $field => $value) {
            $element = $customProperties->createElement($field);
            $element->nodeValue = $value;
            $config->appendChild($element);
        }

        $customProperties->appendChild($config);
        $blockXML->setCustomProperty($customProperties);
        return $blockXML;
    }

    /**
     * @param $xml
     */
    public function execBlock($xml)
    {
        $blockId = (string)$xml->attributes()->id;
        $config = (array)$xml->sliderConfig;
        $numberOfSlides = (string)$xml->sliderConfig->numberOfSlides;
        foreach ($config as $k => $val) {
            if ($k != 'numberOfSlides') {
                $config[$k] = (string)$val;
            }
        }

        return $this->flexSliderController->render($blockId, $numberOfSlides, $config);
    }

    /**
     * @param null $blockId
     */
    public function getBlockConfig($blockId = null)
    {
        $configXml = null;
        if ($blockId) {
            $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
            $configXml = $this->blockParser->getXMLObjectFromString($this->blockParser->wrapBlockConfig($block));
        }
        $this->flexSliderController->getBlockConfig($configXml);
    }
}
