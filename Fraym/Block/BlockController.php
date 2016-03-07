<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * Class BlockController
 * @package Fraym\Block
 * @Injectable(lazy=true)
 */
class BlockController extends \Fraym\Core
{
    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManagerController;

    /**
     * @Inject
     * @var \Fraym\FileManager\FileManager
     */
    protected $fileManager;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

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
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @Inject
     * @var \Fraym\Validation\Validation
     */
    protected $validation;

    /**
     * @Inject
     * @var \Fraym\Core
     */
    protected $core;

    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManager;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @param $block
     * @param $html
     * @param $xml simplexml object
     * @param bool $force true if to output block info on ajax request
     * @return mixed
     */
    public function addBlockInfo($block, $html, $xml, $force = false)
    {
        if ($this->user->isAdmin() === false) {
            return;
        }

        // do not add block info to ajax requests
        if ($this->request->isXmlHttpRequest() && $force === false) {
            return $html;
        }

        $renderTime = 0;
        if ($this->blockParser->getXMLAttr($xml, 'id')) {
            $renderTime = $this->core->stopTimer('blockExecution_' . $this->blockParser->getXMLAttr($xml, 'id'));
        };

        $this->view->assign('renderTime', $renderTime);
        $this->view->assign('type', $this->blockParser->getXMLAttr($xml, 'type'));
        $this->view->assign('id', $this->blockParser->getXMLAttr($xml, 'id'));
        $this->view->assign('block', $block);
        $this->view->assign('moudleName', $block ? $block->extension->name : '');
        $this->view->assign('content', $html);
        return $this->template->fetch('BlockInfo.tpl');
    }

    /**
     * Block config
     *
     * @Fraym\Annotation\Route("/fraym/admin/block", name="block", permission={"GROUP:Administrator"})
     * @return mixed
     */
    public function renderBlock()
    {
        $contentId = $this->request->gp('contentId');

        $blockTemplates = $this->db->getRepository('\Fraym\Block\Entity\BlockTemplate')->findBy(
            array(),
            array('name' => 'asc')
        );
        $extensions = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findBy(
            array(),
            array('name' => 'asc')
        );
        $users = $this->db->getRepository('\Fraym\User\Entity\User')->findBy(array(), array('username' => 'asc'));
        $userGroups = $this->db->getRepository('\Fraym\User\Entity\Group')->findBy(array(), array('name' => 'asc'));
        $this->view->assign('blockTemplates', $blockTemplates);
        $this->view->assign('users', $users);
        $this->view->assign('userGroups', $userGroups);
        $this->view->assign('extensions', $extensions);
        $this->view->assign('contentId', $contentId);

        return $this->siteManagerController->getIframeContent($this->template->fetch('BlockIframeContent.tpl'));
    }

    /**
     * @param $xml
     * @param $content
     * @return mixed
     */
    public function createEditViewContentDIV($xml, $content)
    {
        $contentId = $this->blockParser->getXMLAttr($xml, 'id');
        $cssClass = $this->blockParser->getXMLAttr($xml, 'class');
        $editStyle = $this->blockParser->getXMLAttr($xml, 'editStyle');
        $actionBarStyle = $this->blockParser->getXMLAttr($xml, 'actionBarStyle');
        $description = $this->blockParser->getXMLAttr($xml, 'description');

        $renderElement = $this->blockParser->getXMLAttr($xml, 'renderElement') === false ? false : true;
        $htmlElement = $this->blockParser->getXMLAttr($xml, 'element') ? : 'div';
        $unique = $this->blockParser->getXMLAttr($xml, 'unique') === true ? true : false;

        $this->view->assign('description', $description);
        $this->view->assign('actionBarStyle', $actionBarStyle);
        $this->view->assign('editStyle', $editStyle);
        $this->view->assign('unique', $unique);
        $this->view->assign('cssClass', $cssClass);
        $this->view->assign('htmlElement', $htmlElement);
        $this->view->assign('renderElement', $renderElement);
        $this->view->assign('contentId', $contentId);
        $this->view->assign('inEditMode', $this->block->inEditMode());
        $this->view->assign('content', $content);
        return $this->template->fetch('EditViewBar.tpl');
    }

    /**
     * Check if the requested route exsits for the block modules.
     *
     * @return bool
     */
    public function checkRoute()
    {
        if ($this->user->isAdmin() === false) {
            return false;
        }

        $allowCmds = array(
            'pasteBlock',
            'getBlockConfigView',
            'moveBlockToView',
            'getExtensionConfigView',
            'getBlockConfig',
            'setEditMode',
            'deleteBlock',
            'getTemplateConfig',
            'saveBlockConfig'
        );
        $cmd = trim($this->request->gp('cmd', ''));

        if (in_array($cmd, $allowCmds)) {
            return true;
        }
        return false;
    }


    private function getTemplateConfig()
    {

    }

    /**
     *
     */
    private function setEditMode()
    {
        $value = $this->request->gp('value');
        if (empty($value)) {
            $value = $this->block->inEditMode() ? 0 : 1;
        }
        if ($this->block->setEditMode($value)) {
            $this->response->sendAsJson(array('success' => true));
        }
        $this->response->sendAsJson(array('success' => false));
    }

    /**
     * Save the block configuration received by the client.
     *
     * @return bool
     */
    public function saveBlockConfig()
    {

        $blockConfigGP = $this->request->getGPAsObject();
        $validate = $this->validation->setData($blockConfigGP);
        $validate->addRule('id', 'numeric')
            ->addRule('menuId', 'numeric')
            ->addRule('contentId', 'notEmpty');

        $block = false;
        $menuItemTranslation = null;

        if (($result = $validate->check()) === true) {

            $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneById(
                $blockConfigGP->id
            );

            $menu = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($blockConfigGP->menuId);

            $menuItemTranslation = $this->db->getRepository('\Fraym\Menu\Entity\MenuItemTranslation')->findOneById(
                $blockConfigGP->menuTranslationId
            );

            if (isset($blockConfigGP->currentBlockId)) {
                $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById(
                    $blockConfigGP->currentBlockId
                );
            }

            if ($extension) {
                if ($block) {
                    $newBlock = new BlockXML();
                    $newBlock->init($block->config);
                } else {
                    $newBlock = new BlockXML();
                }

                if (isset($blockConfigGP->excludedDevices)) {
                    $newBlock->setExcludedDevices($blockConfigGP->excludedDevices);
                }

                if (isset($blockConfigGP->permissions)) {
                    $newBlock->setPermissions($blockConfigGP->permissions);
                }

                $newBlock->setActive($blockConfigGP->active);
                $newBlock->setCache($blockConfigGP->cache);
                $newBlock->setMethod($extension->execMethod);
                $newBlock->setClass($extension->class);

                if (!empty($blockConfigGP->startDate)) {
                    $date = new \DateTime(date('Y-m-d H:i:s', strtotime($blockConfigGP->startDate)));
                    $newBlock->setStartDate($date);
                }
                if (!empty($blockConfigGP->endDate)) {
                    $date = new \DateTime(date('Y-m-d H:i:s', strtotime($blockConfigGP->endDate)));
                    $newBlock->setEndDate($date);
                }

                if ($blockConfigGP->template == 'custom') {
                    $newBlock->setTemplate($blockConfigGP->templateContent);
                    $newBlock->setTemplateType('string');
                } elseif (empty($blockConfigGP->template)) {
                    $newBlock->setTemplate($blockConfigGP->templateFile);
                    $newBlock->setTemplateType('file');
                } else {
                    $newBlock->setTemplateType($blockConfigGP->template);
                }

                $saveMethod = $extension->saveMethod;
                $instance = $this->serviceLocator->get($extension->class);

                if (empty($block)) {
                    $block = new \Fraym\Block\Entity\Block();
                }

                $blockCount = count($this->db->getRepository('\Fraym\Block\Entity\Block')->findByContentId(
                    $blockConfigGP->contentId
                ));

                $block->contentId = $blockConfigGP->contentId;
                $block->position = $block->position ? $block->position : $blockCount;
                $block->menuItem = isset($blockConfigGP->menu) && $blockConfigGP->menu == '1' ? null : $menu;
                $block->site = $menu->site;
                $block->menuItemTranslation = $blockConfigGP->menuTranslation === 'current' ? $menuItemTranslation : null;
                $block->extension = $extension;

                $this->db->persist($block);
                $this->db->flush();

                /**
                 * Set configuration for the block output
                 */
                $this->locale->setLocale($menuItemTranslation->locale->id);
                $this->route->setCurrentMenuItem($menu);
                $this->route->setCurrentMenuItemTranslation($menuItemTranslation);

                /**
                 * Extension event callback
                 */
                if (method_exists($instance, $saveMethod)) {
                    $newBlock = $instance->$saveMethod($block->id, $newBlock);
                }

                $blockConfig = $this->blockParser->getBlockConfig((string)$newBlock);
                $block->config = $blockConfig;
                $this->db->flush();

                /**
                 * Save block in history
                 */
                if (isset($blockConfigGP->currentBlockId)) {
                    $this->block->saveHistory($block, 'edited');
                } else {
                    $this->block->saveHistory($block, 'added');
                }

                $data = $this->prepareBlockOutput($block);
                $this->response->sendAsJson(array('data' => $data, 'blockId' => $block->id));
            }
        }

        $this->response->sendAsJson(array('error' => $result));
    }

    /**
     * @param Entity\Block $block
     * @return mixed
     */
    public function prepareBlockOutput($block)
    {
        $blockParser = $this->blockParser;
        $blockXml = $blockParser->wrapBlockConfig($block);
        $blockXmlStringWithId = $blockParser->addIdToXmlBlock($block->id, $blockXml);

        if ($block->menuItem) {
            $this->route->setCurrentMenuItem($block->menuItem);
        }

        if ($block->menuItemTranslation) {
            $this->route->setCurrentMenuItemTranslation($block->menuItemTranslation);
        }

        return $this->addBlockInfo(
            $block,
            $blockParser->parse($blockXmlStringWithId),
            $blockParser->getXMLObjectFromString($blockXmlStringWithId),
            true
        );
    }

    /**
     * AJAX handler function
     *
     * @return bool
     */
    public function ajaxHandler()
    {
        if ($this->user->isAdmin() === false) {
            return;
        }

        $cmd = trim($this->request->gp('cmd', ''));

        if (method_exists($this, $cmd)) {
            return $this->$cmd();
        }
        return false;
    }

    /**
     *
     */
    public function getConfigurableTplBlockConfig() {

    }

    /**
     * Loading the block xml configuration. Response JSON.
     *
     * @return bool
     */
    public function getBlockConfig()
    {
        // set if the block exsists
        $id = $this->request->gp('id', false);
        // set if the block is a new one
        $extensionId = $this->request->gp('extensionId', false);
        $result = new \stdClass();

        if ($extensionId) {
            $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneById($extensionId);
            $result = $extension->toArray(1);
        } elseif ($id) {
            $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($id);

            if ($block) {
                $extension = $block->extension;
                $arrayFromXml = $this->blockParser->xmlToArray(
                    $this->blockParser->getXMLObjectFromString($this->blockParser->wrapBlockConfig($block))
                );
                $result->xml = $arrayFromXml['block'];
                $result = (object)array_merge($block->toArray(2), $block->extension->toArray(1), (array)$result);
            }
        }

        return ($extension ? $this->response->sendAsJson($result) : false);
    }

    /**
     * Executes the extension function to render the view for the extension config.
     */
    private function getExtensionConfigView()
    {
        $id = $this->request->gp('id', false);
        $blockId = $this->request->gp('blockId', null);

        if ($id) {
            $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneById($id);

            if ($extension) {
                $instance = $this->serviceLocator->get($extension->class);
                $configMethod = $extension->configMethod;

                if (method_exists($instance, $configMethod)) {
                    $instance->$configMethod($blockId);
                }
            }
        }
    }

    /**
     * Move/saves the block to a html content element with the contentId.
     */
    private function moveBlockToView()
    {
        $blocks = $this->request->gp('blocks', array());

        foreach ($blocks as $k => $block) {
            if(isset($block['blockId'])) {
                $movedblock = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($block['blockId']);
                if ($movedblock) {
                    $movedblock->contentId = $block['contentId'];
                    $movedblock->position = intval($k);
                    $this->db->flush();
                } else {
                    $this->response->sendAsJson(array('success' => false));
                }
            } else {
                $this->response->sendAsJson(array('success' => false));
            }
        }

        $this->response->sendAsJson(array('success' => true));
    }


    /**
     * Delete a block
     */
    private function deleteBlock()
    {
        $blockId = $this->request->gp('blockId', false);
        if ($blockId && ($block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId))) {
            $this->block->saveHistory($block, 'deleted');

            foreach ($block->refBlocks as $refBlock) {
                $this->db->remove($refBlock);
            }

            $this->db->remove($block);
            $this->db->flush();

            $this->response->sendAsJson(array('success' => true));
        }
    }

    /**
     * Paste copied block
     */
    private function pasteBlock()
    {
        $blockId = $this->request->gp('blockId');
        $op = $this->request->gp('op', 'copy') == 'copy' ? 'copy' : 'cut';
        $byRef = $this->request->gp('byRef', false) === 'true' ? true : false;
        $menuId = $this->request->gp('menuId', false);
        $contentId = $this->request->gp('contentId', false);

        if ($contentId && $blockId && ($block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById(
            $blockId
        ))
        ) {
            if ($op === 'copy') {
                $copiedBlock = clone $block;
                $copiedBlock->id = null;
                $copiedBlock->contentId = $contentId;
                $copiedBlock->menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById(
                    $menuId
                );
                if ($byRef === true) {
                    $copiedBlock->config = null;
                    if ($block->byRef) {
                        $block = $block->byRef;
                        $copiedBlock->byRef = $block;
                    }
                    $copiedBlock->byRef = $block;
                }
                $this->db->persist($copiedBlock);
                $block = $copiedBlock;
            } else {
                $block->menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuId);
                $block->contentId = $contentId;
                $this->db->persist($block);
            }

            $this->db->flush();

            $this->response->sendAsJson(array('success' => true, 'data' => $this->prepareBlockOutput($block)));
        }
        $this->response->sendAsJson(
            array('success' => false, 'message' => $this->translation->getTranslation('Paste error, please copy again'))
        );
    }

    /**
     *  Render a only a block template
     */
    public function renderContentBlock($xml)
    {
        $this->blockParser->setCurrentParsingBlockId($this->blockParser->getXMLAttr($xml, 'id'));
        $this->view->setTemplate('Content');
    }

    /**
     * @param mixed $blockConfig
     */
    public function getBlockContainerConfig($blockConfig = null)
    {
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->render('BlockContainerConfig.tpl');
    }
}
