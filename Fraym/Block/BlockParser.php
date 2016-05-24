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
    private $customBlockTypes = [];

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
    private $placeholderReplacement = [];

    /**
     * @var array
     */
    private $executedBlocks = [];

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
    private $editModeTypes = ['module', 'content', ''];

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

    public function setParseCached($val)
    {
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
            $xml = $this->getXmlObjectFromString($block);
            if ($this->getXmlAttr($xml, 'id') != $id) {
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
            $xml = $this->getXmlObjectFromString($block);
            if ($this->getXmlAttr($xml, 'id') == $id) {
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
            $blocks = [];
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
    public function replaceXmlTags($elementName, $callbackFunction, $xml)
    {
        return preg_replace_callback(
            '#<' . $elementName . '(?:\s+[^>]+)?>(.*?)</' . $elementName . '>#si',
            [$this, "$callbackFunction"],
            trim($xml)
        );
    }

    /**
     * @param $xmlString
     * @return \SimpleXMLElement
     */
    public function getXmlObjectFromString($xmlString)
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
        $blocks = [];
        foreach ($this->getAllBlocks($html) as $match) {
            $xml = $this->getXmlObjectFromString($match);
            if ($xml && $this->getXmlAttr($xml, 'type') == $type) {
                $blocks[] = $match;
            }
            unset($xml);
        }
        return $blocks;
    }

    /**
     * @param $xmlString
     * @return mixed
     */
    public function removeXmlHeader($xmlString)
    {
        return preg_replace('#<\?xml.*?\?>#is', '', $xmlString);
    }

    /**
     * @param $elementName
     * @param $string
     * @return array
     */
    public function getXmlTags($elementName, $string)
    {
        $matches = [];
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
            $xml = $this->getXmlObjectFromString($this->executedBlocks[$blockId]);
        } else {
            $block = $this->db
                ->getEntityManager()
                ->createQuery('select b from \Fraym\Block\Entity\Block b WHERE b.id = :id')
                ->setParameter('id', $blockId)
                ->useResultCache(true)
                ->getOneOrNullResult();

            $xml = $this->getXmlObjectFromString($this->wrapBlockConfig($block));
        }
        $user = $this->user;
        $allow = true;

        if ($user->isLoggedIn()) {
            $userGroupIdentifiers = $user->getIdentifiersFromGroups();
            $userIdentifier = $user->identifier;

            if (isset($xml->permissions)) {
                $allow = false;
                foreach ($xml->permissions->permission as $permission) {
                    $identifier = $this->getXmlAttr($permission, 'identifier');
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
            $excluded = [];
            foreach ($xml->excludedDevices->device as $device) {
                $excluded[] = $this->getXmlAttr($device[0], 'type');
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
        $xml = $this->getXmlObjectFromString($this->xmlString);

        if ($xml === false) {
            throw new \Exception('XML Error. XML Block is not supported: ' . $this->xmlString);
        } elseif ($this->isBlockEnable($xml) === false) {
            return '';
        } elseif ($this->isBlockCached($xml) === true) {
            return $this->xmlString;
        } elseif ($this->isAllowedDevice($xml) === false) {
            return '';
        }

        if ($this->getXmlAttr($xml, 'id')) {
            $this->core->startTimer('blockExecution_' . $this->getXmlAttr($xml, 'id'));
        };

        if ($this->getXmlAttr($xml, 'cached') == '1') {
            $this->db->connect();
        }

        $blockType = strtolower($this->getXmlAttr($xml, 'type'));

        switch ($blockType) {
            case 'css':
                return $this->execBlockOfTypeCss($xml);
                break;
            case 'js':
                return $this->execBlockOfTypeJS($xml);
                break;
            case 'link':
                return $this->execBlockOfTypeLink($xml);
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
            case 'javascript':
                $blockHtml = $this->execBlockOfTypeJavascript($xml);
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
        $blockHtml = '';
        if(is_object($xml)) {
            $ext = $this->db->getRepository('\Fraym\Block\Entity\Extension')->findOneBy(
                ['class' => $xml->class, 'execMethod' => $xml->method]
            );
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
        }
        return $blockHtml;
    }

    /**
     * @param $xml
     * @return bool
     */
    private function isBlockCached($xml)
    {
        return !$this->request->isPost() && $this->getXmlAttr($xml, 'cached') &&
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
        $blockType = strtolower($this->getXmlAttr($xml, 'type'));
        if ($this->request->isXmlHttpRequest() === false) {
            if ($this->block->inEditMode() && in_array($blockType, $this->editModeTypes)) {
                $block = null;

                if (($this->getXmlAttr($xml, 'type') === 'extension' ||
                        $this->getXmlAttr(
                            $xml,
                            'type'
                        ) === null) &&
                    ($id = $this->getXmlAttr($xml, 'id'))
                ) {
                    $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($id);
                }
                $editable = $this->getXmlAttr($xml, 'editable');
                if ($editable === true || $editable === null) {
                    $blockHtml = $this->blockController->addBlockInfo($block, $blockHtml, $xml);
                }
            }

            // Disable cache on block attribute or on element level
            if ($this->getXmlAttr($xml, 'cached') != '1' &&
                (
                    $this->getXmlAttr(
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

            if ($this->getXmlAttr($xml, 'placeholder') !== null) {
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
        if (($this->sequence === false && !$this->getXmlAttr($xml, 'sequence')) ||
            ($this->sequence !== false && $this->getXmlAttr($xml, 'cached'))
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
        if (($this->sequence === false && !$this->getXmlAttr($xml, 'sequence')) ||
            ($this->sequence !== false && $this->sequence === $this->getXmlAttr($xml, 'sequence'))
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
        $attr = strtolower($this->getXmlAttr($xml->children()->template, 'type'));
        $template = trim((string)$xml->children()->template);
        if ($attr == 'string') {
            return $template;
        } elseif (is_numeric($attr)) {
            $blockTemplate = $this->db->getRepository('\Fraym\Block\Entity\Template')->findOneById($attr);
            if ($blockTemplate && is_readable($blockTemplate->template)) {
                return file_get_contents($blockTemplate->template);
            } else {
                error_log("Error loading block template with id: " . $attr);
            }
        } elseif ($attr == 'file' && !empty($template)) {
            $templateFile = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $template);
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
        $attr = $this->getXmlAttr($xml, 'placeholder');
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
        $queryBuilder = $this->db->createQueryBuilder();
        $menuTranslationId = $this->route->getCurrentMenuItemTranslation()->id;
        $siteId = $this->route->getCurrentMenuItem()->site->id;
        $blocks = [];

        $query = $queryBuilder
            ->select("block")
            ->from('\Fraym\Block\Entity\Block', 'block')
            ->andWhere('block INSTANCE OF \Fraym\Block\Entity\Block OR block.block IS NULL');

        if ($this->user->isAdmin() === false) {
            $query = $query->andWhere("block.menuItem IS NULL OR block.menuItem = :menuId")
                ->andWhere("block.site = :site")
                ->andWhere("block.menuItemTranslation IS NULL OR block.menuItemTranslation = :menuTranslationId")
                ->setParameter('menuId', $menuId)
                ->setParameter('menuTranslationId', $menuTranslationId)
                ->setParameter('site', $siteId);
        }

        $results = $query->addOrderBy('block.position', 'asc')
            ->addOrderBy('block.id', 'desc')
            ->getQuery()
            ->getResult();

        foreach ($results as $result) {
            if ($this->user->isAdmin() &&
                $result->changeSets->count() > 0) {
                $lastChange = $result->changeSets->last();

                if ($lastChange->contentId === $contentId &&
                    ($lastChange->menuItem === null || $lastChange->menuItem->id === $menuId) &&
                    ($lastChange->menuItemTranslation === null || $lastChange->menuItemTranslation->id === $menuTranslationId) &&
                    ($lastChange->site->id == $siteId) &&
                    $lastChange->type !== Entity\ChangeSet::DELETED) {

                    // Changed blocks
                    $lastChange = clone $lastChange;
                    $lastChange->id = $result->id;
                    $blocks[$result->id] = $lastChange;
                }
            } elseif ($this->user->isAdmin() &&
                $result->contentId === $contentId &&
                ($result->menuItem === null || $result->menuItem->id === $menuId) &&
                ($result->menuItemTranslation === null || $result->menuItemTranslation->id === $menuTranslationId) &&
                ($result->site->id == $siteId) &&
                get_class($result) === 'Fraym\Block\Entity\ChangeSet') {

                // New blocks
                $blocks[$result->id] = $result;
            } elseif ($contentId === $result->contentId &&
                ($result->menuItem === null || $result->menuItem->id === $menuId) &&
                ($result->menuItemTranslation === null || $result->menuItemTranslation->id === $menuTranslationId) &&
                ($result->site->id == $siteId) &&
                get_class($result) === 'Fraym\Block\Entity\Block') {

                // Old none changed blocks
                $blocks[$result->id] = $result;
            }
        }

        if ($this->user->isAdmin()) {
            uasort($blocks, function ($a, $b) {
               if ($a->position === $b->position) {
                   return $a->id < $b->id ? 1 : -1;
               }
               return $a->position > $b->position ? 1 : -1;
            });
        }

        return $blocks;
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
        $block = $block->changeSets->count() ? $block->changeSets->last() : $block;
        $blockConfigXml = $block->getConfig($this->user->isAdmin());

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

    /**
     * @param $configArr
     * @return string
     */
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
        $savePath = $this->getImageSavePath($fileConfigHash);

        if (is_file($savePath)) {
            return $savePath;
        }

        /** @var \Imagine\Gd\Imagine $imagine */
        $imagine = $this->serviceLocator->get('Imagine');
        $bgBox = new \Imagine\Image\Box($configArr['phwidth'], $configArr['phheight']);
        $imgColor = new \Imagine\Image\Palette\RGB();
        $img = $imagine->create($bgBox, $imgColor->color($configArr['phbgcolor']));

        $descriptionBoxImg = new \Imagine\Gd\Font(realpath($configArr['phfont']), $configArr['phfontsize'], $imgColor->color($configArr['phcolor']));
        $descriptionBoxImg = $descriptionBoxImg->box($configArr['phtext'], 0)->getWidth();

        // set the point to start drawing text, depending on parent image width
        $descriptionPositionCenter = ceil(($img->getSize()->getWidth() - $descriptionBoxImg) / 2);

        if ($descriptionPositionCenter < 0) {
            $descriptionPositionCenter = 0;
        }

        $img->draw()->text(
            $configArr['phtext'],
            new \Imagine\Gd\Font(realpath($configArr['phfont']), $configArr['phfontsize'], $imgColor->color($configArr['phcolor'])),
            new \Imagine\Image\Point($descriptionPositionCenter, $img->getSize()->getHeight() / 2 - ($configArr['phfontsize']/2)),
            0
        );

        $img->save($savePath);
        return $savePath;
    }

    /**
     * @param $filename
     * @param string $ext
     * @return string
     */
    private function getImageSavePath($filename, $ext = 'png')
    {
        $convertedImageFileName = trim($this->config->get('IMAGE_PATH')->value, '/');

        if (!is_dir('Public' . DIRECTORY_SEPARATOR . $convertedImageFileName)) {
            mkdir('Public' . DIRECTORY_SEPARATOR . $convertedImageFileName, 0755, true);
        }

        $convertedImageFileName .= DIRECTORY_SEPARATOR . $filename . '.' . $ext;

        return 'Public' . DIRECTORY_SEPARATOR . trim($this->fileManager->convertDirSeparator($convertedImageFileName), '/');
    }

    /**
     * @param $xml
     * @return string
     */
    public function execBlockOfTypeJavascript($xml)
    {
        $this->template->addFootData('<script type="text/javascript">' . (string)$xml . '</script>');
        return '';
    }

    /**
     * @param $xml
     * @return string
     * @throws \Exception
     */
    public function execBlockOfTypeImage($xml)
    {
        $imageTags = [
            'width' => $this->getXmlAttr($xml, 'width'),
            'height' => $this->getXmlAttr($xml, 'height'),
            'alt' => $this->getXmlAttr($xml, 'alt'),
            'class' => $this->getXmlAttr($xml, 'class'),
            'align' => $this->getXmlAttr($xml, 'align'),
            'id' => $this->getXmlAttr($xml, 'id'),
            'itemprop' => $this->getXmlAttr($xml, 'itemprop'),
            'ismap' => $this->getXmlAttr($xml, 'ismap'),
            'crossoriginNew' => $this->getXmlAttr($xml, 'crossoriginNew'),
            'usemap' => $this->getXmlAttr($xml, 'usemap'),
        ];

        $placeHolderConfig = [
            'phtext' => $this->getXmlAttr($xml, 'phtext'),
            'phwidth' => $this->getXmlAttr($xml, 'phwidth'),
            'phheight' => $this->getXmlAttr($xml, 'phheight'),
            'phcolor' => $this->getXmlAttr($xml, 'phcolor'),
            'phbgcolor' => $this->getXmlAttr($xml, 'phbgcolor'),
            'phfont' => $this->getXmlAttr($xml, 'phfont'),
            'phfontsize' => $this->getXmlAttr($xml, 'phfontsize'),
        ];

        $srcOnly = $this->getXmlAttr($xml, 'srcOnly') === true ? true : false;

        $src = $this->getXmlAttr($xml, 'src');
        if ($src === '') {
            return '';
        }

        if (filter_var($src, FILTER_VALIDATE_URL)) {
            $info = getimagesize($src);
            $fname = parse_url($src, PHP_URL_PATH);
            $fname = basename($fname);
            $fname = substr($fname, 0, strripos($fname, '.'));

            $pathInfo = [
                'extension' => trim(image_type_to_extension($info[2]), '.'),
                'filename' => $fname,
            ];
            $srcFilePath = $src;
        } else {
            $src = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $src);

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
            $pathInfo = pathinfo($srcFilePath);
        }

        $imagine = $this->serviceLocator->get('Imagine');
        $allowedMethods = ['thumbnail', 'resize', 'crop', ''];
        // methods fit / resize / none
        $imageQuality = intval($this->getXmlAttr($xml, 'quality') ? : ($this->config->get('IMAGE_QUALITY')->value ? : '80'));
        $method = $this->getXmlAttr($xml, 'method') ? : '';
        $mode = $this->getXmlAttr($xml, 'mode') ? : 'outbound';
        $crop = $this->getXmlAttr($xml, 'crop') ? explode(',', $this->getXmlAttr($xml, 'crop')) : null;

        if (!in_array($method, $allowedMethods)) {
            throw new \Exception("Image method '{$method}' is not allowed.");
        }

        $imagePath = $this->getImageSavePath($pathInfo['filename'] . '-' . md5(md5_file($srcFilePath).$xml->asXML()), $pathInfo['extension']);

        if (!is_file($imagePath)) {
            $image = $imagine->open($srcFilePath);
            $imageBox = $this->getImageBox($imageTags, $image);

            if ($method == 'resize') {
                $image->resize($imageBox);
            } elseif ($method == 'thumbnail') {
                $imageTags['width'] = $imageTags['width'] ? : $image->getSize()->getWidth();
                $imageTags['height'] = $imageTags['height'] ? : $image->getSize()->getHeight();

                if ($mode == 'outbound') {
                    $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                } else {
                    $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
                }

                $image = $image->thumbnail(
                    new \Imagine\Image\Box($imageTags['width'], $imageTags['height']),
                    $mode
                );
            } else {
                $imageTags['width'] = $imageTags['width'] ? : $image->getSize()->getWidth();
                $imageTags['height'] = $imageTags['height'] ? : $image->getSize()->getHeight();
            }

            if ($crop) {
                $image->crop(new \Imagine\Image\Point($crop[0], $crop[1]), new \Imagine\Image\Box($crop[2], $crop[3]));
                $imageTags['width'] = $image->getSize()->getWidth();
                $imageTags['height'] = $image->getSize()->getHeight();
            }

            $image->save($imagePath, ['quality' => $imageQuality]);
        } else {
            list($width, $height) = getimagesize($imagePath);
            $imageTags['width'] = $width;
            $imageTags['height'] = $height;
        }

        // remove Public folder
        $convertedImageFileName = substr($imagePath, strpos($imagePath, DIRECTORY_SEPARATOR)+1);

        if ($this->getXmlAttr($xml, 'autosize')) {
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

        $imagePath = '/' . str_replace(['\\', '/'], '/', $convertedImageFileName);
        return $srcOnly ? $imagePath : '<img src="' . $imagePath . '" ' . $attributes . ' />';
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
        $contentId = $this->getXmlAttr($xml, 'id');
        $unique = $this->getXmlAttr($xml, 'unique') === true ? true : false;
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
            $placeholder = (string)$child->children()->placeholder;
            $contentId = $this->getContentId($child);

            $blocks = $this->getDataFromBlocksByContentId($contentId);

            // In Editmode we want to render all views to insert content
            if ((empty($blocks) && $this->getXmlAttr($child, 'hideEmpty') !== false && empty($placeholder)) && $this->block->inEditMode() === false) {
                continue;
            }

            if ((empty($blocks) && !empty($placeholder)) && $this->block->inEditMode() === false) {
                $blocks = $placeholder;
            }

            $result = $this->contentChildViews($child);
            if (count($result) > 0) {
                $content = (isset($result['beforeContent']) ? $result['beforeContent'] : '') . $this->core->includeScript(
                        $blocks
                    ) . (isset($result['afterContent']) ? $result['afterContent'] : '');
            } else {
                $content = $this->core->includeScript($this->parse($blocks));
            }

            $html .= $this->blockController->createEditViewElement($child, $content);
        }

        return $html;
    }

    /**
     * @param $xml
     * @return array|string
     */
    private function contentChildViews($xml)
    {
        $childsHtml = [];
        foreach ($xml->children() as $child) {
            $contentId = $this->getContentId($child);

            $blocks = $this->getDataFromBlocksByContentId($contentId);

            // In Editmode we want to render all views to insert content
            if ((empty($blocks) && $this->getXmlAttr($child, 'hideEmpty') !== false && empty($placeholder)) && $this->block->inEditMode() === false) {
                continue;
            }

            if ((empty($blocks) && !empty($placeholder)) && $this->block->inEditMode() === false) {
                $blocks = $this->blockController->createEditViewElement($child, $placeholder);
            }

            // result returns an array
            $result = $this->contentChildViews($child);
            $addContent = $this->getXmlAttr($child, 'add') ? : 'afterContent';

            if (!isset($childsHtml[$addContent])) {
                $childsHtml[$addContent] = '';
            }

            if (count($result) > 0) {
                $blockhtml = (isset($result['beforeContent']) ? $result['beforeContent'] : '') .
                    $this->core->includeScript($blocks) .
                    (isset($result['afterContent']) ? $result['afterContent'] : '');

                if (trim($blockhtml) === '') {
                    $childsHtml[$addContent] .= (isset($childsHtml[$addContent]) ? $childsHtml[$addContent] : '');
                } else {
                    $childsHtml[$addContent] .= (isset($childsHtml[$addContent]) ? $childsHtml[$addContent] : '') .
                        $this->blockController->createEditViewElement(
                            $child,
                            $blockhtml
                        );
                }
            } else {
                $blockhtml = $this->core->includeScript($blocks);

                if (trim($blockhtml) === '') {
                    $childsHtml[$addContent] .= '';
                } else {
                    $childsHtml[$addContent] .= $this->blockController->createEditViewElement($child, $blockhtml);
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
            $string = $this->replaceXmlTags('block', 'exec', $string);
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
    private function execBlockOfTypeLink($xml)
    {
        $menuItemId = $this->getXmlAttr($xml->a, 'href');
        $translation = $this->getXmlAttr($xml, 'translation');

        if ($translation) {
            $menuItemTranslation = $this->db->getRepository('\Fraym\Menu\Entity\MenuItemTranslation')->findOneById($menuItemId);
        } else {
            $menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuItemId);
            $menuItemTranslation = $menuItem->getCurrentTranslation();
        }

        if ($menuItemTranslation->externalUrl) {
            $xml->a->addAttribute('target', '_blank');
        }
        $xml->a->attributes()->href = empty($menuItemTranslation->url) ? '/' : $menuItemTranslation->url;

        return (string)$xml->a->asXml();
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
    private function execBlockOfTypeCss($xml)
    {
        $consolidatedContent = '';

        if (($this->sequence === false &&
                !$this->getXmlAttr(
                    $xml,
                    'sequence'
                )) ||
            ($this->sequence !== false && $this->sequence === $this->getXmlAttr($xml, 'sequence'))
        ) {
            $cssReplace = '';
            $group = $this->getXmlAttr($xml, 'group') ? : 'default';
            $fileHash = null;
            $cssFiles = $this->template->getCssFiles($group);
            $fileHash = md5(implode('', $cssFiles));
            $consolidatedCssFilePath = rtrim(CONSOLIDATE_FOLDER, '/') . '/' . $fileHash . '.css';
            $consolidatedFileExists = is_file('Public' . $consolidatedCssFilePath);
            $consolidate = GLOBAL_CACHING_ENABLED ? $this->getXmlAttr($xml, 'consolidate') : false;

            foreach ($cssFiles as $cssFile) {
                if ($isUrl = preg_match(
                    "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie",
                    $cssFile
                )
                ) {
                    $file = $cssFile;
                } else {
                    $file = (substr($cssFile, 0, 1) == '/' ? $cssFile : rtrim(CSS_FOLDER, '/') . '/' . $cssFile);
                }

                if ($isUrl == 0 && $consolidatedFileExists === false && $consolidate === true) {
                    $consolidatedContent .= file_get_contents('Public/' . ltrim($file, '/'));
                } elseif ($isUrl !== 0 || $consolidate !== true) {
                    if (!$isUrl) {
                        $fileHash = hash_file('crc32', 'Public/' . $file);
                    }
                    $cssReplace .= '<link rel="stylesheet" type="text/css" href="' . $file . ($fileHash ? '?' . $fileHash : '') . '" />';
                }
            }

            if ($consolidate === true && (!empty($consolidatedContent) || $consolidatedFileExists)) {
                if ($consolidatedFileExists) {
                    $cssReplace .= '<link rel="stylesheet" type="text/css" href="' . $consolidatedCssFilePath . '" />';
                } else {
                    $cssReplace .= $this->consolidateCss($consolidatedCssFilePath, $consolidatedContent);
                }
            }
            if ($this->getXmlAttr($xml, 'placeholder')) {
                $this->addPlaceholderReplacement($xml, $cssReplace);
                return '';
            }

            return $cssReplace;
        }

        return $this->xmlString;
    }

    /**
     * @param $content
     * @return mixed
     */
    public function minifyCSSContent($content)
    {
        // Remove comments
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        // Remove empty tags
        $content = preg_replace('/(^|\})[^\{\}]+\{\s*\}/', '\\1', $content);

        /**
         * Remove Whitespaces
         */
        // remove leading & trailing whitespace
        $content = preg_replace('/^\s*/m', '', $content);
        $content = preg_replace('/\s*$/m', '', $content);
        // replace newlines with a single space
        $content = preg_replace('/\s+/', ' ', $content);
        // remove whitespace around meta characters
        // inspired by stackoverflow.com/questions/15195750/minify-compress-css-with-regex
        $content = preg_replace('/\s*([\*$~^|]?+=|[{};,>~]|!important\b)\s*/', '$1', $content);
        $content = preg_replace('/([\[(:])\s+/', '$1', $content);
        $content = preg_replace('/\s+([\]\)])/', '$1', $content);
        $content = preg_replace('/\s+(:)(?![^\}]*\{)/', '$1', $content);
        // whitespace around + and - can only be stripped in selectors, like
        // :nth-child(3+2n), not in things like calc(3px + 2px) or shorthands
        // like 3px -2px
        $content = preg_replace('/\s*([+-])\s*(?=[^}]*{)/', '$1', $content);
        // remove semicolon/whitespace followed by closing bracket
        $content = str_replace(';}', '}', $content);
        return trim($content);
    }

    /**
     * @param $content
     * @return mixed
     */
    public function minifyJSContent($content)
    {
        $content = \JsMin\Minify::minify($content);
        return trim($content);
    }

    /**
     * @param $consolidatedCssFilePath
     * @param $consolidatedContent
     * @return string
     */
    public function consolidateCss($consolidatedCssFilePath, $consolidatedContent)
    {
        $dir = 'Public' . dirname($consolidatedCssFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        file_put_contents('Public' . $consolidatedCssFilePath, $consolidatedContent);
        return '<link rel="stylesheet" type="text/css" href="' . $consolidatedCssFilePath . '" />';
    }

    /**
     * @param $xml
     * @return string
     */
    private function execBlockOfTypeJS($xml)
    {
        $consolidatedContent = '';

        if (($this->sequence === false &&
                !$this->getXmlAttr(
                    $xml,
                    'sequence'
                )) ||
            ($this->sequence !== false &&
                $this->sequence === $this->getXmlAttr($xml, 'sequence'))
        ) {
            $jsReplace = '';
            $isUrl = 0;
            $fileHash = null;
            $group = $this->getXmlAttr($xml, 'group') ? : 'default';

            $jsFiles = $this->template->getJsFiles($group);
            $fileHash = md5(implode('', $jsFiles));
            $consolidatedJsFilePath = rtrim(CONSOLIDATE_FOLDER, '/') . '/' . $fileHash . '.js';
            $consolidatedFileExists = is_file('Public' . $consolidatedJsFilePath);
            $consolidate = GLOBAL_CACHING_ENABLED ? $this->getXmlAttr($xml, 'consolidate') : false;

            foreach ($jsFiles as $jsFile) {
                if ($isUrl = preg_match(
                    "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie",
                    $jsFile
                )
                ) {
                    $file = $jsFile;
                } else {
                    $file = (substr($jsFile, 0, 1) == '/' ? $jsFile : rtrim(JS_FOLDER, '/') . '/' . $jsFile);
                }

                if ($isUrl == 0 && $consolidatedFileExists === false && $consolidate === true) {
                    $consolidatedContent .= ";\n" . file_get_contents('Public/' . ltrim($file, '/'));
                } elseif ($isUrl !== 0 || $consolidate !== true) {
                    if (!$isUrl) {
                        $fileHash = hash_file('crc32', 'Public/' . $file);
                    }
                    $jsReplace .= '<script type="text/javascript" src="' . $file . ($fileHash ? '?' . $fileHash : '') . '"></script>';
                }
            }

            if ($consolidate === true && (!empty($consolidatedContent) || $consolidatedFileExists)) {
                if ($consolidatedFileExists) {
                    $jsReplace .= '<script type="text/javascript" src="' . $consolidatedJsFilePath . '"></script>';
                } else {
                    $jsReplace .= $this->consolidateJs($consolidatedJsFilePath, $consolidatedContent);
                }
            }

            if ($this->getXmlAttr($xml, 'placeholder')) {
                $this->addPlaceholderReplacement($xml, $jsReplace);
                return '';
            }
            return $jsReplace;
        }

        return $this->xmlString;
    }

    /**
     * @param $consolidatedJsFilePath
     * @param $consolidatedContent
     * @return string
     */
    private function consolidateJs($consolidatedJsFilePath, $consolidatedContent)
    {
        $dir = 'Public' . dirname($consolidatedJsFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        file_put_contents('Public' . $consolidatedJsFilePath, $consolidatedContent);
        return '<script type="text/javascript" src="' . $consolidatedJsFilePath . '"></script>';
    }

    /**
     * Gets an XML attribute.
     *
     * @param $xml
     * @param $attr
     * @return bool|null|string
     */
    public function getXmlAttr($xml, $attr)
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
    public function xmlToArray($xml, $options = [])
    {
        $defaults = [
            'namespaceSeparator' => ':', //you may want this to be something other than a colon
            'attributePrefix' => '@', //to distinguish between attributes and nodes with the same name
            'alwaysArray' => [], //array of xml tag names which should always become arrays
            'autoArray' => true, //only create arrays for tags which appear more than once
            'textContent' => '$', //key used for the text content of elements
            'autoText' => true, //skip textContent key if node has no attributes or child nodes
            'keySearch' => false, //optional search and replace on tag and attribute names
            'keyReplace' => false //replace values for above search values (as passed to str_replace())
        ];
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = [];
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
        $tagsArray = [];
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
                            ? [$childProperties] : $childProperties;
                } elseif (is_array($tagsArray[$childTagName]) &&
                    array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = [$tagsArray[$childTagName], $childProperties];
                }
            }
        }

        //get text content of node
        $textContentArray = [];
        $plainText = trim((string)$xml);
        if ($plainText !== '') {
            $textContentArray[$options['textContent']] = $plainText;
        }

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return [
            $xml->getName() => $propertiesArray
        ];
    }
}
