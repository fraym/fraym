<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Image;

/**
 * Class Image
 * @package Fraym\Image
 * @Injectable(lazy=true)
 */
class Image
{
    /**
     * @Inject
     * @var \Fraym\Image\ImageController
     */
    protected $imageController;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

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
     * @param $blockId
     * @param \Fraym\Block\BlockXml $blockXML
     * @return \Fraym\Block\BlockXml
     */
    public function saveBlockConfig($blockId, \Fraym\Block\BlockXml $blockXML)
    {
        $data = $this->request->post('image');
        $data['auto_size'] = isset($data['auto_size']) && $data['auto_size'] == '1' ? true : false;

        $customProperties = new \Fraym\Block\BlockXmlDom();

        foreach ($data as $prop => $val) {
            if (!empty($val)) {
                $element = $customProperties->createElement('image_' . $prop);
                $element->nodeValue = $val;
                $customProperties->appendChild($element);
            }
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
        return $this->imageController->render($xml);
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
        $this->imageController->getBlockConfig($configXml);
    }
}
