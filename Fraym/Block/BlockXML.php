<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * Class BlockXml
 * @package Fraym\Block
 */
class BlockXml
{
    /**
     * @var array
     */
    private $templatesTypes = ['file', 'string'];

    /**
     * @var null
     */
    private $class = null;

    /**
     * @var null
     */
    private $method = null;

    /**
     * @var string
     */
    private $type = 'extension';

    /**
     * @var null
     */
    private $checkRouteFunction = null;

    /**
     * @var null
     */
    private $dom = null;

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @var bool
     */
    private $cache = true;

    /**
     * @var null
     */
    private $startDate = null;

    /**
     * @var null
     */
    private $endDate = null;

    /**
     * @var null
     */
    private $template = null;

    /**
     * @var array
     */
    private $permissions = [];

    /**
     * @var array
     */
    private $excludedDevices = [];

    /**
     * @var bool
     */
    private $returnValidBlock = true;

    /**
     * @var string
     */
    private $templateType = 'file';

    /**
     * @var string
     */
    private $customProperty = '';

    /**
     * @param string $blockString
     * @return BlockXmlDom
     */
    public function init($blockString = '')
    {
        $this->dom = $blockString;
        $dom = new BlockXmlDom();
        if ($blockString === '') {
            // load empty block
            $dom->loadXML('<?xml version="1.0" encoding="utf-8"?><block>' . $this->customProperty . '</block>');
        } elseif ($blockString !== '') {
            // load exsiting
            $dom->loadXML('<?xml version="1.0" encoding="utf-8"?><block>' . $blockString . '</block>');
        }
        return $dom;
    }

    /**
     * @return mixed
     */
    public function toObject()
    {
        $simpleXmlObj = simplexml_load_string($this->dom);

        $recursiv = function ($xmlObj, $children = false) use (&$recursiv) {
            $result = new \stdClass();
            $childArr = [];
            foreach ($xmlObj as $elementName => $elementValue) {
                $result->{(string)$elementName} = new \stdClass();

                if (count($elementValue->attributes()) > 0) {
                    $result->$elementName->attributes = [];
                    foreach ($elementValue->attributes() as $attrName => $attrVal) {
                        if (!isset($result->$elementName)) {
                            $result->$elementName = new \stdClass();
                        }
                        $result->$elementName->attributes[$attrName] = (string)$attrVal;
                    }
                }
                if (count($elementValue->children()) > 0) {
                    $result->$elementName->children = $recursiv($elementValue->children(), true);
                } else {
                    $result->{(string)$elementName}->value = (string)$elementValue;
                }

                $childArr[] = [$elementName => $result->{(string)$elementName}];
            }

            if ($children === true) {
                return $childArr;
            } else {
                return $result;
            }
        };

        return $recursiv($simpleXmlObj);
    }

    /**
     * @return bool|string
     */
    private function buildValidBlock()
    {
        $this->dom = $this->init();
        switch ($this->type) {
            case 'extension':
                return $this->buildXmlExtensionBlock();
                break;
        }
        return false;
    }

    /**
     * @param BlockXmlDom $data
     */
    public function setCustomProperty(\Fraym\Block\BlockXmlDom $data)
    {
        $this->customProperty = $data;
    }

    /**
     * @return string
     */
    private function buildXmlExtensionBlock()
    {
        $elBlock = $this->dom->getElementsByTagName('block')->item(0);
        $attr = $this->dom->createAttribute('type');
        $elBlock->appendChild($attr);
        $elBlock->setAttribute('type', $this->type);

        $elBlock->setAttribute('type', $this->type);

        if (!empty($this->checkRouteFunction)) {
            $elBlock->appendChild($this->dom->createElement('checkRouteFunction', $this->checkRouteFunction));
        }

        $elBlock->appendChild($this->dom->createElement('active', $this->active));
        $elBlock->appendChild($this->dom->createElement('cache', $this->cache));

        if (is_object($this->startDate)) {
            $elBlock->appendChild($this->dom->createElement('startDate', $this->startDate->format("Y-m-d H:i")));
        }
        if (is_object($this->endDate)) {
            $elBlock->appendChild($this->dom->createElement('endDate', $this->endDate->format("Y-m-d H:i")));
        }

        $elTemplate = $this->dom->createElement('template');
        $elTemplate->appendChild($this->dom->createCDATASection($this->template));
        $elTemplate->appendChild($this->dom->createAttribute('type'));
        $elTemplate->setAttribute('type', $this->templateType);

        $elPerms = $this->dom->createElement('permissions');
        foreach ($this->permissions as $identifier) {
            if (empty($identifier)) {
                continue;
            }
            $elPerm = $this->dom->createElement('permission');
            $elPerm->setAttribute('identifier', $identifier);
            $elPerms->appendChild($elPerm);
        }

        if ($elPerms->childNodes->length) {
            $elBlock->appendChild($elPerms);
        }

        $elPerms = $this->dom->createElement('excludedDevices');
        foreach ($this->excludedDevices as $device) {
            if (empty($device)) {
                continue;
            }
            $elPerm = $this->dom->createElement('device');
            $elPerm->setAttribute('type', $device);
            $elPerms->appendChild($elPerm);
        }

        if ($elPerms->childNodes->length) {
            $elBlock->appendChild($elPerms);
        }
        $elBlock->appendChild($elTemplate);
        $this->dom->appendChild($elBlock);
        return (string)$this->dom;
    }

    /**
     * @return null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $function
     */
    public function setMethod($function)
    {
        $this->method = $function;
    }

    /**
     * @return null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @param $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = (array)$permissions;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return array
     */
    public function getExcludedDevices()
    {
        return $this->excludedDevices;
    }

    /**
     * @param $excludedDevices
     */
    public function setExcludedDevices($excludedDevices)
    {
        $this->excludedDevices = (array)$excludedDevices;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->returnValidBlock) {
            $xmlString = $this->buildValidBlock();
        } else {
            $xmlString = (string)$this->dom;
        }

        if ($xmlString) {
            return substr($xmlString, strpos($xmlString, "\n"));
        }
        return '';
    }

    /**
     * @return null
     */
    public function getCheckRouteFunction()
    {
        return $this->checkRouteFunction;
    }

    /**
     * @param $checkRouteFunction
     */
    public function setCheckRouteFunction($checkRouteFunction)
    {
        $this->checkRouteFunction = $checkRouteFunction;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplateType()
    {
        return $this->templateType;
    }

    /**
     * @param $templateType
     * @return bool
     */
    public function setTemplateType($templateType)
    {
        if (in_array($templateType, $this->templatesTypes) || is_numeric($templateType)) {
            $this->templateType = $templateType;
            return true;
        }
        return false;
    }

    /**
     * @param $validateBlock
     */
    public function setReturnValidBlock($validateBlock)
    {
        $this->returnValidBlock = $validateBlock;
    }
}
