<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * Class BlockParser
 * @package Fraym\Block
 * @Injectable(lazy=true)
 */
class BlockParser
{
    /**
     * execute the block xml directly
     */
    const PARSE_XML = 'xml';

    /**
     * extract block xml first
     */
    const PARSE_HTML = 'html';

    /**
     * @var array
     */
    private $customBlockTypes = array();

    /**
     * Holds the current parsing block.
     * Used for view elements with a unique content id.
     *
     * @var null
     */
    private $currentParsingBlockId = null;

    /**
     * all Template placeholders
     *
     * @var array
     */
    private $placeholderReplacement = array();

    /**
     * @var array
     */
    private $executedBlocks = array();

    /**
     * current block sequence
     *
     * @var bool
     */
    private $sequence = false;

    /**
     * current parsing Xml string
     *
     * @var string
     */
    private $xmlString = '';

    /**
     * status of testing whether a virutal route was found
     *
     * @var bool
     */
    private $checkRouteError = false;

    /**
     * flag for module route checking to prevent module execution
     *
     * @var bool
     */
    private $execModule = true;

    /**
     * all block modules that acceppt the requested route
     *
     * @var string
     */
    private $foundRouteModules = '';

    /**
     * the current parsing mode for cached block elements
     *
     * @var bool
     */
    private $cached = false;

    /**
     * Defines the module types that can be edited in frontend. The empty type are the default "module" type.
     *
     * @var array
     */
    private $editModeTypes = array('module', 'content', '');

    /**
     * @Inject
     * @var \Fraym\Core
     */
    protected $core;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @Inject
     * @var \Fraym\Registry\Config
     */
    protected $config;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Route\Route
     */
    protected $route;

    /**
     * @Inject
     * @var \Fraym\Template\Template
     */
    protected $template;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @Inject
     * @var \Fraym\Block\Block
     */
    protected $block;

    /**
     * @Inject
     * @var \Fraym\Block\BlockController
     */
    protected $blockController;

    /**
     * @Inject
     * @var \Fraym\FileManager\FileManager
     */
    protected $fileManager;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    public function setParseCached($val) {
        $this->cached = $val;
        return $this;
    }

    /**
     * @param string $type Block type name
     * @param array $customBlockType Callback method
     */
    public function addCustomBlockType($type, $customBlockType)
    {
        $this->customBlockTypes[$type] = $customBlockType;
    }

    /**
     * Remove a block from a string.
     *
     * @param $content
     * @param $id
     * @return string
     */
    public function removeBlockById($content, $id)
    {
        $blocks = $this->getAllBlocks($content);
        $newContent = '';

        foreach ($blocks as $block) {
            $xml = $this->getXMLObjectFromString($block);
            if ($this->getXMLAttr($xml, 'id') != $id) {
                $newContent .= $block;
            }
        }
        return $newContent;
    }

    /**
     * Get a block from string.
     *
     * @param $content
     * @param $id
     * @return bool
     */
    public function getBlockById($content, $id)
    {
        $blocks = $this->getAllBlocks($content);

        foreach ($blocks as $block) {
            $xml = $this->getXMLObjectFromString($block);
            if ($this->getXMLAttr($xml, 'id') == $id) {
                return $block;
            }
        }
        return false;
    }

    /**
     * Gets the block config from a block xml string
     *
     * @param $string
     * @return string
     */
    public function getBlockConfig($string)
    {
        $dom = new \DOMDocument;
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXML($string);
        $dom->appendChild($fragment);
        $result = '';
        foreach ($dom->getElementsByTagName('block')->item(0)->childNodes as $node) {
            $result .= trim($dom->saveXML($node));
        }
        return trim($result);
    }

    /**
     * Get all blocks from a string.
     *
     * @param $content
     * @return array
     */
    public function getAllBlocks($content)
    {
        preg_match_all('#<block(?:\s+[^>]+)?>(.*?)</block>#si', $content, $matches);
        if (isset($matches[0])) {
            $blocks = array();
            foreach ($matches[0] as $match) {
                $blocks[] = $match;
            }
            return $blocks;
        }
    }

    /**
     * @param $elementName
     * @param $callbackFunction
     * @param $xml
     * @return mixed
     */
    public function replaceXMLTags($elementName, $callbackFunction, $xml)
    {
        return preg_replace_callback(
            '#<' . $elementName . '(?:\s+[^>]+)?>(.*?)</' . $elementName . '>#si',
            array($this, "$callbackFunction"),
            trim($xml)
        );
    }

    /**
     * @param $xmlString
     * @return \SimpleXMLElement
     */
    public function getXMLObjectFromString($xmlString)
    {
        $xmlString = $this->removeXmlHeader((string)$xmlString);
        libxml_use_internal_errors(true);
        $xmlHeaderTag = '<?xml version="1.0" encoding="utf-8"?>';
        $xml = simplexml_load_string($xmlHeaderTag . $xmlString, null, LIBXML_NOCDATA);
        return $xml;
    }

    /**
     * @param $type
     * @param $html
     * @return array
     */
    public function getBlockOfType($type, $html)
    {
        $blocks = array();
        foreach ($this->getAllBlocks($html) as $match) {
            $xml = $this->getXMLObjectFromString($match);
            if ($xml && $this->getXMLAttr($xml, 'type') == $type) {
                $blocks[] = $match;
            }
            unset($xml);
        }
        return $blocks;
    }

    /**
     * Calls the checkRoute xml function from an XML Block attribute.
     * If the method from the block return true the xml of this block will returned.
     *
     * @param $xml
     * @return mixed|string
     */
    private function checkBlockRouteByXml($xml)
    {
        if ($xml) {
            $class = (string)$xml->class;
            $checkRoute = (string)$xml->checkRoute;

            if (!empty($class) && !empty($checkRoute)) {
                $instance = $this->serviceLocator->get($class);

                if ($this->checkRouteError === true &&
                    (!empty($checkRoute) && $instance->$checkRoute() === true)
                ) {
                    $xmlString = $xml->asXML();
                    return $this->removeXmlHeader($xmlString);
                }
            }
        }
        return '';
    }

    public function removeXmlHeader($xmlString)
    {
        return preg_replace('#<\?xml.*?\?>#is', '', $xmlString);
    }


    /**
     * Check the block elements of a string to call the "checkBlockRouteByXml"
     * method an check if a modules accept the custom route.
     *
     * @param $html
     * @return bool|string
     */
    public function moduleRouteExist($html)
    {
        // the function method of the module xml will not be executed if this is false
        $this->execModule = false;
        $this->foundRouteModules = '';

        if ($this->user->isAdmin()) {
            $adminBlock = "<block type='module'><class>Fraym\Block\BlockController</class><method>ajaxHandler</method><checkRoute>checkRoute</checkRoute></block>";
            $xml = $this->getXMLObjectFromString($adminBlock);
            $this->foundRouteModules .= $this->checkBlockRouteByXml($xml);
        }

        $blocks = $this->getXMLTags('block', $html);
        if (isset($blocks[0])) {
            foreach ($blocks[0] as $block) {
                $xml = $this->getXMLObjectFromString($block);
                if ($this->isBlockEnable($xml) === false) {
                    continue;
                }
                $this->foundRouteModules .= $this->checkBlockRouteByXml($xml);
            }
        }

        $blocks = $this->db->createQueryBuilder()
            ->select("block, byRef")
            ->from('\Fraym\Block\Entity\Block', 'block')
            ->leftJoin('block.byRef', 'byRef')
            ->where("block.menuItem IS NULL OR block.menuItem = :menuId")
            ->andWhere("block.menuItemTranslation IS NULL OR block.menuItemTranslation = :menuTranslationId")
            ->setParameter('menuId', $this->route->getCurrentMenuItem()->id)
            ->setParameter('menuTranslationId', $this->route->getCurrentMenuItemTranslation()->id)
            ->getQuery()
            ->getResult();

        foreach ($blocks as $block) {
            $xml = $this->getXMLObjectFromString($this->wrapBlockConfig($block));
            if ($this->isBlockEnable($xml) === false) {
                continue;
            }
            $this->foundRouteModules .= $this->checkBlockRouteByXml($xml);
        }

        if (!empty($this->foundRouteModules)) {
            $this->checkRouteError = false;
        }

        $this->execModule = true;

        return (($this->isRouteError() && $this->block->inEditMode() === false) ||
            empty($this->foundRouteModules)) ? false : $this->foundRouteModules;
    }

    public function getXMLTags($elementName, $string)
    {
        $matches = array();
        if (preg_match_all('#<' . $elementName . '(?:\s+[^>]+)?>(.*?)</' . $elementName . '>#si', $string, $matches)) {
            return $matches;
        }
        return $matches;
    }

    /**
     * Check the block user view permission.
     *
     * @param $blockId
     * @return bool
     */
    public function checkPermission($blockId)
    {
        if ($this->cached && isset($this->executedBlocks[$blockId])) {
            $xml = $this->getXMLObjectFromString($this->executedBlocks[$blockId]);
        } else {
            $block = $this->db
                ->getEntityManager()
                ->createQuery('select b from \Fraym\Block\Entity\Block b WHERE b.id = :id')
                ->setParameter('id', $blockId)
                ->useResultCache(true)
                ->getOneOrNullResult();

            $xml = $this->getXMLObjectFromString($this->wrapBlockConfig($block));
        }
        $user = $this->user;
        $allow = true;

        if ($user->isLoggedIn()) {

            $userGroupIdentifiers = $user->getIdentifiersFromGroups();
            $userIdentifier = $user->identifier;

            if (isset($xml->permissions)) {
                $allow = false;
                foreach ($xml->permissions->permission as $permission) {
                    $identifier = $this->getXMLAttr($permission, 'identifier');
                    if ($userIdentifier === $identifier || in_array($identifier, $userGroupIdentifiers)) {
                        $allow = true;
                        break;
                    }
                }
            }
        } else {
            if (isset($xml->permissions)) {
                $allow = false;
            }
        }

        return $allow;
    }

    /**
     * Check whether an element should be displayed that has been configured for a specific period
     *
     * @param $startDate
     * @param $endDate
     * @return bool
     */
    public function checkDate($startDate, $endDate)
    {
        $allow = true;
        if (!empty($startDate)) {
            $now = new \DateTime('now');
            $date = \DateTime::createFromFormat('Y-m-d H:i', $startDate);
            if ($now < $date) {
                $allow = false;
            }
        }
        if (!empty($endDate)) {
            $now = new \DateTime();
            $date = \DateTime::createFromFormat('Y-m-d H:i', $endDate);
            if ($now <= $date) {
                $allow = true;
            } else {
                $allow = false;
            }
        }

        return $allow;
    }

    /**
     * @param $xml
     * @return bool
     */
    public function isBlockEnable($xml)
    {
        if (isset($xml->active) && $xml->active == '0'
            && $this->block->inEditMode() === false
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param $xml
     * @return bool
     */
    public function isAllowedDevice($xml)
    {
        if (isset($xml->excludedDevices)) {
            $detection = $this->serviceLocator->get('Detection\MobileDetect');
            $excluded = array();
            foreach ($xml->excludedDevices->device as $device) {
                $excluded[] = $this->getXMLAttr($device[0], 'type');
            }

            if ($this->block->inEditMode() === false &&
               (($detection->isMobile() && in_array('mobile', $excluded)) ||
               ($detection->isTablet() && in_array('tablet', $excluded)) ||
               ($detection->isTablet() === false && $detection->isMobile() === false && in_array('desktop', $excluded)))
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $xmlString
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function exec($xmlString)
    {
        $xmlString = is_array($xmlString) ? $xmlString[0] : $xmlString;
        $this->xmlString = $xmlString;
        $xml = $this->getXMLObjectFromString($this->xmlString);

        if ($xml === false) {
            throw new \Exception('XML Error. XML Block is not supported: ' . $this->xmlString);
        } elseif ($this->isBlockEnable($xml) === false) {
            return '';
        } elseif ($this->isBlockCached($xml) === true) {
            return $this->xmlString;
        } elseif ($this->isAllowedDevice($xml) === false) {
            return '';
        }

        if ($this->getXMLAttr($xml, 'id')) {
            $this->core->startTimer('blockExecution_' . $this->getXMLAttr($xml, 'id'));
        };

        if ($this->getXMLAttr($xml, 'cached') == '1') {
            $this->db->connect();
        }

        $blockType = strtolower($this->getXMLAttr($xml, 'type'));

        switch ($blockType) {
            case 'css':
                return $this->execBlockOfTypeCSS($xml);
                break;
            case 'js':
                return $this->execBlockOfTypeJS($xml);
                break;
            case 'module':
                $blockHtml = $this->execBlockOfTypeModule($xml);
                break;
            case 'content':
                $blockHtml = $this->execBlockOfTypeContent($xml);
                break;
            case 'cache':
                $blockHtml = $this->execBlockOfTypeCache($xml);
                break;
            case 'php':
                $blockHtml = $this->execBlockOfTypePhp($xml);
                break;
            case 'image':
                $blockHtml = $this->execBlockOfTypeImage($xml);
                break;
            case 'dcontent':
                $blockHtml = $this->execBlockOfTypeDynamicContent($xml);
                break;
            default: // extensions & custom block types

                if (isset($this->customBlockTypes[$blockType])) {
                    $blockHtml = call_user_func($this->customBlockTypes[$blockType]);
                } else {
                    $blockHtml = $this->execBlockOfTypeExtension($xml);
                }
                break;
        }

        return $this->processBlockAttributes($xml, $blockHtml);
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypeExtension($xml)
    {
        $ext = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneBy(
            array('class' => $xml->class, 'execMethod' => $xml->method)
        );
        $blockHtml = '';
        if ($ext) {
            $class = $ext->class;
            if (class_exists($class)) {
                $classInstance = $this->serviceLocator->get($class, '\\');
                $function = $ext->execMethod;
                $return = $classInstance->$function($xml);
                if ($return !== false) {
                    $blockHtml = $this->setBlockTemplateWrap($xml);
                }
            }
        }

        return $blockHtml;
    }

    /**
     * @param $xml
     * @return bool
     */
    private function isBlockCached($xml)
    {
        return !$this->request->isPost() && $this->getXMLAttr($xml, 'cached') &&
        $this->cached === false &&
        GLOBAL_CACHING_ENABLED === true &&
        $this->route->getCurrentMenuItem()->caching === true;
    }

    /**
     * @param $xml
     * @param $blockHtml
     * @return mixed|\SimpleXMLElement|string
     */
    private function processBlockAttributes($xml, $blockHtml)
    {
        $blockType = strtolower($this->getXMLAttr($xml, 'type'));
        if ($this->request->isXmlHttpRequest() === false) {
            if ($this->block->inEditMode() && in_array($blockType, $this->editModeTypes)) {
                $block = null;

                if (($this->getXMLAttr($xml, 'type') === 'extension' ||
                        $this->getXMLAttr(
                            $xml,
                            'type'
                        ) === null) &&
                    ($id = $this->getXMLAttr($xml, 'id'))
                ) {
                    $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($id);
                }
                $editable = $this->getXMLAttr($xml, 'editable');
                if ($editable === true || $editable === null) {
                    $blockHtml = $this->blockController->addBlockInfo($block, $blockHtml, $xml);
                }
            }

            // Disable cache on block attribute or on element level
            if ($this->getXMLAttr($xml, 'cached') != '1' &&
                (
                    $this->getXMLAttr(
                        $xml,
                        'cache'
                    ) === false ||
                    (isset($xml->cache) && $xml->cache == 0 && !$this->request->isPost() && GLOBAL_CACHING_ENABLED === true &&
                        $this->route->getCurrentMenuItem()->caching === true)
                )
            ) {
                $blockHtml = $this->disableBlockCaching($xml);
            } else {
                // add block state to check permission or end/start date on caching
                $blockHtml = $this->addBlockCacheState($xml, $blockHtml);
            }

            if ($this->getXMLAttr($xml, 'placeholder') !== null) {
                return $this->addPlaceholderReplacement($xml, $blockHtml);
            }
        }
        return $blockHtml;
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypeCache($xml)
    {
        if (($this->sequence === false && !$this->getXMLAttr($xml, 'sequence')) ||
            ($this->sequence !== false && $this->getXMLAttr($xml, 'cached'))
        ) {
            $GLOBALS["TEMPLATE"] = $this->template;
            $templateVarString = '$TEMPLATE = ' . '$GLOBALS["TEMPLATE"];';
            $content = $this->core->includeScript("<?php {$templateVarString} {$xml}");
            unset($GLOBALS["TEMPLATE"]);
            return $content;
        }
        return $this->xmlString;
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypePhp($xml)
    {
        if (($this->sequence === false && !$this->getXMLAttr($xml, 'sequence')) ||
            ($this->sequence !== false && $this->sequence === $this->getXMLAttr($xml, 'sequence'))
        ) {
            $GLOBALS["TEMPLATE"] = $this->template;
            $templateVarString = '$TEMPLATE = ' . '$GLOBALS["TEMPLATE"];';
            $content = $this->core->includeScript("<?php {$templateVarString} {$xml}");
            unset($GLOBALS["TEMPLATE"]);

            return $content;
        }
        return $this->xmlString;
    }

    /**
     * @param $xml
     * @param $blockHtml
     * @return string
     */
    private function addBlockCacheState($xml, $blockHtml)
    {
        if ($this->block->inEditMode() === false &&
            (isset($xml->permissions) || isset($xml->startDate) || isset($xml->endDate))
        ) {
            return "<block type=\"cache\" cache=\"false\">" .
            "<![CDATA[if(\$TEMPLATE->getInstance('\Fraym\Block\BlockParser')->checkDate('" .
            (string)$xml->startDate . "', '" .
            (string)$xml->endDate . "') " .
            "&& \$TEMPLATE->getInstance('\Fraym\Block\BlockParser')->checkPermission(" . $xml->attributes(
            )->id . ")) { echo '" . str_replace("'", "\'", $blockHtml) . "'; }]]></block>";
        }
        return $blockHtml;
    }

    /**
     * @param $xml
     * @return null|string
     * @throws \Exception
     */
    private function getBlockTemplateString($xml)
    {
        $attr = strtolower($this->getXMLAttr($xml->children()->template, 'type'));
        $template = trim((string)$xml->children()->template);
        if ($attr == 'string') {
            return $template;
        } elseif (is_numeric($attr)) {
            $blockTemplate = $this->db->getRepository('\Fraym\Block\Entity\BlockTemplate')->findOneById($attr);
            if ($blockTemplate && is_readable($blockTemplate->template)) {
                return file_get_contents($blockTemplate->template);
            } else {
                error_log("Error loading block template with id: " . $attr);
            }
        } elseif ($attr == 'file' && !empty($template)) {
            $templateFile = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $template);
            if (is_file($templateFile) && is_readable($templateFile)) {
                $templateContent = file_get_contents($templateFile);
                return $templateContent;
            } elseif ($template != '') {
                throw new \Exception('Templatefile do not exists or is not readable: ' . $template);
            }
        }
        return null;
    }

    /**
     * @param $xml
     * @return bool|mixed|string
     */
    private function setBlockTemplateWrap($xml)
    {
        $templateContent = $this->getBlockTemplateString($xml);
        if (empty($templateContent)) {
            $this->template->setTemplate();
        } else {
            $this->template->setTemplate('string:' . $templateContent);
        }

        return $this->template->fetch();
    }

    /**
     * @param $xml
     * @param $value
     * @return mixed
     */
    private function addPlaceholderReplacement($xml, $value)
    {
        $attr = $this->getXMLAttr($xml, 'placeholder');
        $this->placeholderReplacement[$attr] = $value;
        return;
    }

    /**
     * @param $xml
     * @return \SimpleXMLElement
     */
    private function disableBlockCaching($xml)
    {
        unset($xml['cache']);
        /**
         * Auskommentiert da im bearbeitungs modus die blockinfo nicht richtig geladen wird
         */
//        unset($xml['id']);
        $xml['cached'] = true;
        $block = $this->removeXmlHeader($xml->asXml());
        return $block;
    }

    /**
     * @param $menuId
     * @param $contentId
     * @return mixed
     */
    public function findBlocks($menuId, $contentId)
    {
        $result = $this->db->createQueryBuilder()
            ->select("block, byRef")
            ->from('\Fraym\Block\Entity\Block', 'block')
            ->leftJoin('block.byRef', 'byRef')
            ->orderBy('block.position', 'asc')
            ->where("block.contentId = :contentId")
            ->andWhere("block.menuItem IS NULL OR block.menuItem = :menuId")
            ->andWhere("block.site = :site")
            ->andWhere("block.menuItemTranslation IS NULL OR block.menuItemTranslation = :menuTranslationId")
            ->setParameter('menuId', $menuId)
            ->setParameter('menuTranslationId', $this->route->getCurrentMenuItemTranslation()->id)
            ->setParameter('site', $this->route->getCurrentMenuItem()->site->id)
            ->setParameter('contentId', $contentId)
            ->getQuery()
            ->getResult();

        return $result;
    }


    /**
     * @param null $contentId
     * @return mixed|string
     */
    private function getDataFromBlocksByContentId($contentId = null)
    {
        $html = '';
        $blocks = $this->findBlocks($this->route->getCurrentMenuItem()->id, $contentId);
        if ($blocks && is_array($blocks)) {
            foreach ($blocks as $block) {
                $html .= $this->parseBlock($block);
            }
        }
        return $html;
    }

    /**
     * @return mixed
     */
    public function getCurrentParsingBlockId()
    {
        return $this->currentParsingBlockId;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setCurrentParsingBlockId($id)
    {
        $this->currentParsingBlockId = $id;
        return $this;
    }

    /**
     * @param $blocksXml
     * @return $this
     */
    public function setExecutedBlocks($blocksXml)
    {
        $this->executedBlocks = (array)$blocksXml;
        return $this;
    }

    /**
     * @return array
     */
    public function getExecutedBlocks()
    {
        return $this->executedBlocks;
    }

    /**
     * Replace unsecure template tags
     *
     * @param $blockXmlString
     * @return mixed
     */
    public function cleanUpBlockTemplate($blockXmlString)
    {
        // clean default php tags from template
        $blockXmlString = preg_replace('/<\?.*?\?>/is', '', $blockXmlString);
        // clean default template tags from template
        // preg_replace('/\{[^\s].*[^\s]\}/is', '', $blockXmlString) replace template tags? Security?
        return $blockXmlString;
    }

    /**
     * @param $block
     * @return string
     */
    public function wrapBlockConfig($block)
    {
        $blockConfigXml = $block->config;

        $dom = new \DOMDocument;
        $blockElement = $dom->createElement("block");

        if (empty($blockConfigXml) === false) {
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML($blockConfigXml);
            $blockElement->appendChild($fragment);
        }

        $class = $dom->createElement('class', $block->extension->class);
        $function = $dom->createElement('method', $block->extension->execMethod);
        $blockElement->appendChild($class);
        $blockElement->appendChild($function);

        $dom->appendChild($blockElement);

        return $dom->saveXML();
    }

    /**
     * @param $id
     * @param $xmlHtmlString
     * @return mixed
     */
    public function addIdToXmlBlock($id, $xmlHtmlString)
    {
        $xml = simplexml_load_string($xmlHtmlString, null, LIBXML_NOCDATA);
        if ($xml) {
            $xml->addAttribute('id', $id);
            $xmlHtmlString = preg_replace('#<\?xml.*?\?>#is', '', $xml->asXML());
        }
        return $xmlHtmlString;
    }

    private function createImagePlaceholder($configArr)
    {
        if ($configArr['phfont'] === null) {
            $defaultFont = 'Public/fonts/arial.ttf';
            if (!is_file($configArr['phfont']) &&
                is_file($defaultFont)) {
                $configArr['phfont'] = $defaultFont;
            } else {
                trigger_error('Font file not found! Use the phfont attribute to setup a font file.', E_USER_ERROR);
            }
        }

        if ($configArr['phwidth'] === null || $configArr['phheight'] === null) {
            trigger_error('Image placeholder attribute phwidth / phheight not set.', E_USER_NOTICE);
            $configArr['phwidth'] = $configArr['phheight'] = 100;
        }

        // If no background color is set, set default
        if ($configArr['phbgcolor'] === null) {
            $configArr['phbgcolor'] = 'fff';
        }

        // If no color is set, set default
        if ($configArr['phcolor'] === null) {
            $configArr['phcolor'] = '000';
        }

        // If no text is set, set default
        if ($configArr['phtext'] === null) {
            $configArr['phtext'] = 'Placeholder';
        }

        // If no font size is set, set default
        if ($configArr['phfontsize'] === null) {
            $configArr['phfontsize'] = '16';
        }

        $fileConfigHash = md5(serialize($configArr));
        $savePath = $this->getImageSavePath($fileConfigHash, $configArr['phwidth'], $configArr['phheight']);

        if (is_file($savePath)) {
            return $savePath;
        }

        /** @var \Imagine\Gd\Imagine $imagine */
        $imagine = $this->serviceLocator->get('Imagine');
        $bgBox = new \Imagine\Image\Box($configArr['phwidth'], $configArr['phheight']);
        $img = $imagine->create($bgBox, new \Imagine\Image\Color($configArr['phbgcolor'], 0));

        $descriptionBoxImg = new \Imagine\Gd\Font(realpath($configArr['phfont']), $configArr['phfontsize'], new \Imagine\Image\Color($configArr['phcolor']));
        $descriptionBoxImg = $descriptionBoxImg->box($configArr['phtext'], 0)->getWidth();

        // set the point to start drawing text, depending on parent image width
        $descriptionPositionCenter = ceil(($img->getSize()->getWidth() - $descriptionBoxImg) / 2);

        if ($descriptionPositionCenter < 0) {
            $descriptionPositionCenter = 0;
        }

        $img->draw()->text(
            $configArr['phtext'],
            new \Imagine\Gd\Font(realpath($configArr['phfont']), $configArr['phfontsize'], new \Imagine\Image\Color($configArr['phcolor'])),
            new \Imagine\Image\Point($descriptionPositionCenter, $img->getSize()->getHeight() / 2 - ($configArr['phfontsize']/2)),
            0
        );

        $img->save($savePath);
        return $savePath;
    }


    private function getImageSavePath($filename, $width, $height, $ext = 'png')
    {
        $convertedImageFileName = trim($this->config->get('IMAGE_PATH')->value, '/');

        if (!is_dir('Public' . DIRECTORY_SEPARATOR . $convertedImageFileName)) {
            mkdir('Public' . DIRECTORY_SEPARATOR . $convertedImageFileName, 0755, true);
        }

        $convertedImageFileName .= DIRECTORY_SEPARATOR . $filename . '_' . $width . 'x' . $height . '.' . $ext;

        return 'Public' . DIRECTORY_SEPARATOR . trim($this->fileManager->convertDirSeparator($convertedImageFileName), '/');
    }

    /**
     * @param $xml
     * @return string
     * @throws \Exception
     */
    public function execBlockOfTypeImage($xml)
    {
        $imageTags = array(
            'width' => $this->getXMLAttr($xml, 'width'),
            'height' => $this->getXMLAttr($xml, 'height'),
            'alt' => $this->getXMLAttr($xml, 'alt'),
            'class' => $this->getXMLAttr($xml, 'class'),
            'align' => $this->getXMLAttr($xml, 'align'),
            'id' => $this->getXMLAttr($xml, 'id'),
            'itemprop' => $this->getXMLAttr($xml, 'itemprop'),
            'ismap' => $this->getXMLAttr($xml, 'ismap'),
            'crossoriginNew' => $this->getXMLAttr($xml, 'crossoriginNew'),
            'usemap' => $this->getXMLAttr($xml, 'usemap'),
        );

        $placeHolderConfig = array(
            'phtext' => $this->getXMLAttr($xml, 'phtext'),
            'phwidth' => $this->getXMLAttr($xml, 'phwidth'),
            'phheight' => $this->getXMLAttr($xml, 'phheight'),
            'phcolor' => $this->getXMLAttr($xml, 'phcolor'),
            'phbgcolor' => $this->getXMLAttr($xml, 'phbgcolor'),
            'phfont' => $this->getXMLAttr($xml, 'phfont'),
            'phfontsize' => $this->getXMLAttr($xml, 'phfontsize'),
        );

        $src = $this->getXMLAttr($xml, 'src');
        $src = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $src);

        if (empty($src)) {
            $srcFilePath = $src = $this->createImagePlaceholder($placeHolderConfig);
        } else {
            if (substr($src, 0, 1) === '/' || strpos($src, ':') !== false) {
                // absolute path
                $srcFilePath = $src;
            } else {
                // relative path
                $srcFilePath = $this->core->getApplicationDir() . DIRECTORY_SEPARATOR . ltrim($src, '/');
            }

            if (is_file($srcFilePath) === false) {
                return '';
            }
        }

        $imagine = $this->serviceLocator->get('Imagine');
        $image = $imagine->open($srcFilePath);
        $pathInfo = pathinfo($srcFilePath);

        $allowedMethods = array('thumbnail', 'resize', 'crop', '');
        // methods fit / resize / none
        $imageQuality = intval($this->getXMLAttr($xml, 'quality') ? : '80');
        $method = $this->getXMLAttr($xml, 'method') ? : '';
        $mode = $this->getXMLAttr($xml, 'mode') ? : 'outbound';

        if (!in_array($method, $allowedMethods)) {
            throw new \Exception("Image method '{$method}' is not allowed.");
        }

        if (!empty($method)) {

            $imageBox = $this->getImageBox($imageTags, $image);
            $imagePath = $this->getImageSavePath($pathInfo['filename'] . '_' . md5_file($srcFilePath) , $imageBox->getWidth(), $imageBox->getHeight(), $pathInfo['extension']);

            if (!is_file($imagePath)) {

                if ($method == 'resize') {
                    $image->resize($imageBox);
                } elseif($method == 'crop') {
                    $image->crop(new \Imagine\Image\Point(0, 0), new \Imagine\Image\Box($imageTags['width'], $imageTags['height']));
                } else {
                    if ($mode == 'outbound') {
                        $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                    } else {
                        $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
                    }
                    $image = $image->thumbnail(
                        new \Imagine\Image\Box($imageTags['width'], $imageTags['height']),
                        $mode
                    ); // new Box($imageTags['width'], $imageTags['height']));
                }

                $image->save($imagePath, array('quality' => $imageQuality));
                $imageTags['width'] = $image->getSize()->getWidth();
                $imageTags['height'] = $image->getSize()->getHeight();
            } else {
                $image = $imagine->open($imagePath);
                $imageTags['width'] = $image->getSize()->getWidth();
                $imageTags['height'] = $image->getSize()->getHeight();
            }
            // remove Public folder
            $convertedImageFileName = substr($imagePath, strpos($imagePath, DIRECTORY_SEPARATOR)+1);
        } else {
            $convertedImageFileName = ltrim(str_replace('\\', '/', str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($src))), '/');

            $imageTags['width'] = $image->getSize()->getWidth();
            $imageTags['height'] = $image->getSize()->getHeight();
        }

        if ($this->getXMLAttr($xml, 'autosize')) {
            unset($imageTags['width']);
            unset($imageTags['height']);
        }

        $attributes = '';
        foreach ($imageTags as $tag => $val) {
            if (empty($val) === false) {
                $attributes .= "$tag=\"$val\" ";
            }
        }

        if ($src === null) {
            unlink($srcFilePath);
        }

        return '<img src="/' . str_replace(array('\\', '/'), '/', $convertedImageFileName) . '" ' . $attributes . ' />';
    }

    /**
     * @param $imageTags
     * @param $imagine
     * @return \Imagine\Image\Box
     */
    private function getImageBox($imageTags, $imagine)
    {

        if (empty($imageTags['width']) && !empty($imageTags['height'])) {
            return $imagine->getSize()->heighten($imageTags['height']);
        } elseif (empty($imageTags['height']) && !empty($imageTags['width'])) {
            return $imagine->getSize()->widen($imageTags['width']);
        } elseif (!empty($imageTags['height']) && !empty($imageTags['width'])) {
            return new \Imagine\Image\Box($imageTags['width'], $imageTags['height']);
        }

        return new \Imagine\Image\Box($imagine->getSize()->getWidth(), $imagine->getSize()->getHeight());
    }

    /**
     * Generate a unique contentId for dynamic View's
     *
     * @param $xml
     * @return bool|null|string
     */
    private function getContentId(&$xml)
    {
        $contentId = $this->getXMLAttr($xml, 'id');
        $unique = $this->getXMLAttr($xml, 'unique') === true ? true : false;
        if ($unique) {
            $contentId .= '-' . $this->currentParsingBlockId;
        }
        $xml->attributes()->id = $contentId;
        return $contentId;
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypeContent($xml)
    {
        $html = '';
        foreach ($xml->children() as $child) {
            $contentId = $this->getContentId($child);

            $blocks = $this->getDataFromBlocksByContentId($contentId);

            // In Editmode we want to render all views to insert content
            if ((empty($blocks) && $this->getXMLAttr($child, 'hideEmpty') !== false) && $this->block->inEditMode() === false) {
                continue;
            }

            $result = $this->contentChildViews($child);
            if (count($result) > 0) {
                $content = (isset($result['beforeContent']) ? $result['beforeContent'] : '') . $this->core->includeScript(
                    $blocks
                ) . (isset($result['afterContent']) ? $result['afterContent'] : '');
            } else {
                $content = $this->core->includeScript($this->parse($blocks));
            }

            $html .= $this->blockController->createEditViewContentDIV($child, $content);
        }

        return $html;
    }

    /**
     * @param $xml
     * @return array|string
     */
    private function contentChildViews($xml)
    {
        $childsHtml = array();
        foreach ($xml->children() as $child) {
            $contentId = $this->getContentId($child);

            $blocks = $this->getDataFromBlocksByContentId($contentId);

            // In Editmode we want to render all views to insert content
            if ((empty($blocks) && $this->getXMLAttr($child, 'hideEmpty') !== false) && $this->block->inEditMode() === false) {
                continue;
            }
            // result returns an array
            $result = $this->contentChildViews($child);
            $addContent = $this->getXMLAttr($child, 'add') ? : 'afterContent';

            if (!isset($childsHtml[$addContent])) {
                $childsHtml[$addContent] = '';
            }

            if (count($result) > 0) {
                $blockhtml = (isset($result['beforeContent']) ? $result['beforeContent'] : '') .
                    $this->core->includeScript(
                        $blocks
                    ) .
                    (isset($result['afterContent']) ? $result['afterContent'] : '');

                if (($this->getXMLAttr($child, 'hideEmpty') === null ||
                        $this->getXMLAttr(
                            $child,
                            'hideEmpty'
                        ) === true) &&
                    $this->block->inEditMode() === false &&
                    trim($blockhtml) == ''
                ) {
                    $childsHtml[$addContent] .= (isset($childsHtml[$addContent]) ? $childsHtml[$addContent] : '');
                } else {
                    $childsHtml[$addContent] .= (isset($childsHtml[$addContent]) ? $childsHtml[$addContent] : '') .
                        $this->blockController->createEditViewContentDIV(
                            $child,
                            $blockhtml
                        );
                }
            } else {
                $blockhtml = $this->core->includeScript($blocks);

                if (($this->getXMLAttr($child, 'hideEmpty') === null ||
                        $this->getXMLAttr(
                            $child,
                            'hideEmpty'
                        ) === true) &&
                    $this->block->inEditMode() === false &&
                    trim($blockhtml) == ''
                ) {
                    $childsHtml[$addContent] .= '';
                } else {
                    $childsHtml[$addContent] .= $this->blockController->createEditViewContentDIV($child, $blockhtml);
                }
            }
        }

        return $childsHtml;
    }

    /**
     * @param $block
     * @return bool|mixed|string
     */
    public function parseBlock($block)
    {
        $blockXmlString = $this->addIdToXmlBlock($block->id, $this->wrapBlockConfig($block));
        $blockXmlString = $this->cleanUpBlockTemplate($blockXmlString);
        $html = $this->parse($blockXmlString, false, self::PARSE_XML);

        $this->executedBlocks[$block->id] = $blockXmlString;
        return $html;
    }

    /**
     * @return bool
     */
    public function isRouteError()
    {
        return $this->checkRouteError;
    }

    /**
     * @param $bool
     */
    public function setCheckRouteError($bool)
    {
        $this->checkRouteError = $bool;
    }

    /**
     * Parse a string for block elements and replace them with their content.
     *
     * @param $string
     * @param mixed $sequence
     * @param string $parseType
     * @return bool|mixed|string
     */
    public function parse($string, $sequence = false, $parseType = self::PARSE_HTML)
    {
        // sequence tell us if we want to render content before or after the sub content modules
        $this->sequence = $sequence;
        if ($parseType === self::PARSE_XML) {
            $string = $this->exec($string);
        } else {
            $string = $this->replaceXMLTags('block', 'exec', $string);
        }

        foreach ($this->placeholderReplacement as $placeholder => $value) {
            $string = str_replace($placeholder, $value, $string);
        }
        $this->sequence = false;

        return $string;
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypeModule($xml)
    {
        $html = '';

        if ($this->execModule === true) {
            $class = (string)$xml->class;
            $function = (string)$xml->method;
            $result = null;

            ob_start();

            if (empty($function) === false) {
                $instance = $this->serviceLocator->get($class);
                $result = $instance->$function($xml);
            }

            if ($result === true || $result === null) {
                $html = ob_get_clean();
            } else {
                ob_clean();
            }
        }

        return $html;
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypeCSS($xml)
    {
        $consolidatedContent = '';

        if (($this->sequence === false &&
                !$this->getXMLAttr(
                    $xml,
                    'sequence'
                )) ||
            ($this->sequence !== false && $this->sequence === $this->getXMLAttr($xml, 'sequence'))
        ) {
            $cssReplace = '';
            $group = $this->getXMLAttr($xml, 'group') ? : 'default';

            foreach ($this->template->getCssFiles($group) as $cssFile) {
                if ($isUrl = preg_match(
                    "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie",
                    $cssFile
                )
                ) {
                    $file = $cssFile;
                } else {
                    $file = (substr($cssFile, 0, 1) == '/' ? $cssFile : rtrim(CSS_FOLDER, '/') . '/' . $cssFile);
                }
                if ($this->getXMLAttr($xml, 'consolidate') === true) {
                    $consolidatedContent .= file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($file, '/'));
                } else {
                    $fileHash = hash_file('crc32', 'Public/' . $file);
                    $cssReplace .= '<link rel="stylesheet" type="text/css" href="' . $file . '?' . $fileHash . '" />';
                }
            }
            if ($this->getXMLAttr($xml, 'consolidate') === true) {
                $cssReplace = $this->consolidateCss($consolidatedContent);
            }
            if ($this->getXMLAttr($xml, 'placeholder')) {
                $this->addPlaceholderReplacement($xml, $cssReplace);
                return '';
            }

            return $cssReplace;
        }

        return $this->xmlString;
    }

    /**
     * @param $consolidatedContent
     * @return string
     */
    public function consolidateCss($consolidatedContent)
    {
        $temp = tmpfile();
        fwrite($temp, $consolidatedContent);
        $fileInfo = stream_get_meta_data($temp);
        $cssFilePublicPath = rtrim(CONSOLIDATE_FOLDER, '/') . '/' . md5_file($fileInfo['uri']) . '.css';
        $file = '/' . $cssFilePublicPath;

        if (is_file($fileInfo['uri'])) {
            $toFile = $_SERVER['DOCUMENT_ROOT'] . $file;
            if (!is_dir($toDir = dirname($toFile))) {
                mkdir($toDir, 0777);
            }
            copy($fileInfo['uri'], $toFile);
        }
        fclose($temp);
        return '<link rel="stylesheet" type="text/css" href="' . $cssFilePublicPath . '" />';
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypeJS($xml)
    {
        $consolidatedContent = '';

        if (($this->sequence === false &&
                !$this->getXMLAttr(
                    $xml,
                    'sequence'
                )) ||
            ($this->sequence !== false &&
                $this->sequence === $this->getXMLAttr($xml, 'sequence'))
        ) {
            $jsReplace = '';
            $isUrl = 0;
            $group = $this->getXMLAttr($xml, 'group') ? : 'default';

            foreach ($this->template->getJsFiles($group) as $jsFile) {
                if ($isUrl = preg_match(
                    "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie",
                    $jsFile
                )
                ) {
                    $file = $jsFile;
                } else {
                    $file = (substr($jsFile, 0, 1) == '/' ? $jsFile : rtrim(JS_FOLDER, '/') . '/' . $jsFile);
                }

                if ($isUrl == 0 && $this->getXMLAttr($xml, 'consolidate') === true) {
                    $consolidatedContent .= file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($file, '/'));
                } else {
                    $fileHash = hash_file('crc32', 'Public/' . $file);
                    $jsReplace .= '<script type="text/javascript" src="' . $file . '?' . $fileHash . '"></script>';
                }
            }

            if ($isUrl == 0 && $this->getXMLAttr($xml, 'consolidate') === true) {
                $jsReplace = $this->consolidateJs($consolidatedContent);
            }
            if ($this->getXMLAttr($xml, 'placeholder')) {
                $this->addPlaceholderReplacement($xml, $jsReplace);
                return '';
            }
            return $jsReplace;
        }

        return $this->xmlString;
    }

    /**
     * @param $consolidatedContent
     * @return string
     */
    private function consolidateJs($consolidatedContent)
    {
        $temp = tmpfile();
        fwrite($temp, $consolidatedContent);
        $fileInfo = stream_get_meta_data($temp);
        $jsFilePublicPath = rtrim(CONSOLIDATE_FOLDER, '/') . '/' . md5_file($fileInfo['uri']) . '.js';
        $file = '/' . $jsFilePublicPath;

        if (is_file($fileInfo['uri'])) {
            $toFile = $_SERVER['DOCUMENT_ROOT'] . $file;
            if (!is_dir($toDir = dirname($toFile))) {
                mkdir($toDir, 0777);
            }
            copy($fileInfo['uri'], $toFile);
            @unlink($fileInfo['uri']);
        }
        fclose($temp);
        return '<script type="text/javascript" src="' . $jsFilePublicPath . '"></script>';
    }

    /**
     * Gets an XML attribute.
     *
     * @param $xml
     * @param $attr
     * @return bool|null|string
     */
    public function getXMLAttr($xml, $attr)
    {
        if ($xml && get_class($xml) === 'SimpleXMLElement' && isset($xml->attributes()->$attr)) {
            $attrValue = (string)$xml->attributes()->$attr;
            if (strtolower($attrValue) === 'false') {
                return false;
            } elseif (strtolower($attrValue) === 'true') {
                return true;
            }
            return $attrValue;
        }
        return null;
    }

    /**
     * @param $xml
     * @param array $options
     * @return array
     */
    public function xmlToArray($xml, $options = array())
    {
        $defaults = array(
            'namespaceSeparator' => ':', //you may want this to be something other than a colon
            'attributePrefix' => '@', //to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(), //array of xml tag names which should always become arrays
            'autoArray' => true, //only create arrays for tags which appear more than once
            'textContent' => '$', //key used for the text content of elements
            'autoText' => true, //skip textContent key if node has no attributes or child nodes
            'keySearch' => false, //optional search and replace on tag and attribute names
            'keyReplace' => false //replace values for above search values (as passed to str_replace())
        );
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch']) {
                    $attributeName =
                        str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                }
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = $this->xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                //replace characters in tag name
                if ($options['keySearch']) {
                    $childTagName =
                        str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                }
                //add namespace prefix, if any
                if ($prefix) {
                    $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
                }

                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? array($childProperties) : $childProperties;
                } elseif (is_array($tagsArray[$childTagName]) &&
                    array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        //get text content of node
        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') {
            $textContentArray[$options['textContent']] = $plainText;
        }

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return array(
            $xml->getName() => $propertiesArray
        );
    }
}
