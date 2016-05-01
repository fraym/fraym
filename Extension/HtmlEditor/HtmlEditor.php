<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\HtmlEditor;

use Fraym\Block\BlockMetadata;
use \Fraym\Block\BlockXml as BlockXml;
use Fraym\Annotation\Registry;

/**
 * @package Extension\HtmlEditor
 * @Registry(
 * name="Html Editor",
 * description="Create your content with a rich text editor.",
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * repositoryKey="FRAYM_EXT_HTML",
 * entity={
 *      "\Fraym\Block\Entity\Extension"={
 *          {
 *           "name"="Html Editor",
 *           "description"="Create formated text elements with a WYSIWYG Editor.",
 *           "class"="\Extension\HtmlEditor\HtmlEditor",
 *           "configMethod"="getBlockConfig",
 *           "execMethod"="execBlock",
 *           "saveMethod"="saveBlockConfig"
 *           },
 *      }
 * },
 * files={
 *      "Extension/HtmlEditor/*",
 *      "Extension/HtmlEditor/",
 *      "Template/Default/Extension/HtmlEditor/*",
 *      "Template/Default/Extension/HtmlEditor/",
 *      "Public/js/fraym/extension/htmleditor/*",
 *      "Public/js/fraym/extension/htmleditor/",
 * }
 * )
 * @Injectable(lazy=true)
 */
class HtmlEditor
{
    /**
     * @Inject
     * @var \Extension\HtmlEditor\HtmlEditorController
     */
    protected $htmlEditorController;

    /**
     * @Inject
     * @var \Fraym\Route\Route
     */
    protected $route;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @Inject
     * @var \Fraym\Template\Template
     */
    protected $template;

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
     * @param BlockXml $blockXML
     * @return BlockXml
     */
    public function saveBlockConfig($blockId, \Fraym\Block\BlockXml $blockXML)
    {
        $blockConfig = $this->request->getGPAsObject();

        $customProperties = new \Fraym\Block\BlockXmlDom();
        foreach ($blockConfig->html as $localeId => $content) {
            $element = $customProperties->createElement('html');
            $domAttribute = $customProperties->createAttribute('locale');
            $domAttribute->value = $localeId;
            $element->appendChild($domAttribute);
            $element->appendChild($customProperties->createCDATASection($content));
            $customProperties->appendChild($element);
        }
        $blockXML->setCustomProperty($customProperties);
        return $blockXML;
    }

    /**
     * @param $xml
     * @return mixed
     */
    public function execBlock($xml)
    {
        $currentLocaleId = $this->route->getCurrentMenuItemTranslation()->locale->id;
        $locales = $xml->xpath('html[@locale="' . $currentLocaleId . '"]');
        $html = trim((string)current($locales));
        // set template content for custom templates
        $this->htmlEditorController->renderHtml($html);
    }

    /**
     * @param null $blockId
     */
    public function getBlockConfig($blockId = null)
    {
        $configXml = null;
        if ($blockId) {
            $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
            $configXml = $this->blockParser->getXmlObjectFromString($this->blockParser->wrapBlockConfig($block));
        }
        $this->htmlEditorController->getBlockConfig($configXml);
    }
}
