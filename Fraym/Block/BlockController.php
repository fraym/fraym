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
        if ($this->blockParser->getXmlAttr($xml, 'id')) {
            $renderTime = $this->core->stopTimer('blockExecution_' . $this->blockParser->getXmlAttr($xml, 'id'));
        };

        $this->view->assign('renderTime', $renderTime);
        $this->view->assign('type', $this->blockParser->getXmlAttr($xml, 'type'));
        $this->view->assign('style', $this->blockParser->getXmlAttr($xml, 'style'));
        $this->view->assign('id', $block && $block->block ? $block->block->id : $this->blockParser->getXmlAttr($xml, 'id'));
        $this->view->assign('block', $block);
        $this->view->assign('moduleName', $block ? $block->extension->name : '');
        $this->view->assign('content', $html);
        return $this->view->fetch('BlockInfo');
    }

    /**
     * Block config
     *
     * @Fraym\Annotation\Route("/fraym/admin/block", name="block", permission={"\Fraym\User\User"="isAdmin"})
     * @return mixed
     */
    public function renderBlock()
    {
        $contentId = $this->request->gp('contentId');

        $blockTemplates = $this->db->getRepository('\Fraym\Block\Entity\Template')->findBy(
            [],
            ['name' => 'asc']
        );
        $extensions = $this->db->getRepository('\Fraym\Block\Entity\Extension')->findBy(
            [],
            ['name' => 'asc']
        );
        $users = $this->db->getRepository('\Fraym\User\Entity\User')->findBy([], ['username' => 'asc']);
        $userGroups = $this->db->getRepository('\Fraym\User\Entity\Group')->findBy([], ['name' => 'asc']);
        $this->view->assign('blockTemplates', $blockTemplates);
        $this->view->assign('users', $users);
        $this->view->assign('userGroups', $userGroups);
        $this->view->assign('extensions', $extensions);
        $this->view->assign('contentId', $contentId);

        return $this->siteManagerController->getIframeContent($this->view->fetch('BlockIframeContent'));
    }

    /**
     * @param $xml
     * @param $content
     * @return mixed
     */
    public function createEditViewElement($xml, $content)
    {
        $contentId = $this->blockParser->getXmlAttr($xml, 'id');
        $cssClass = $this->blockParser->getXmlAttr($xml, 'class');
        $editStyle = $this->blockParser->getXmlAttr($xml, 'editStyle');
        $actionBarStyle = $this->blockParser->getXmlAttr($xml, 'actionBarStyle');
        $description = $this->blockParser->getXmlAttr($xml, 'description');

        $renderElement = $this->blockParser->getXmlAttr($xml, 'renderElement') === false ? false : true;
        $htmlElement = $this->blockParser->getXmlAttr($xml, 'element') ? : 'div';
        $unique = $this->blockParser->getXmlAttr($xml, 'unique') === true ? true : false;

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
        return $this->view->fetch('EditViewBar');
    }

    /**
     *
     */
    private function clearCache()
    {
        $this->cache->clearAll();
        $this->response->sendAsJson(['success' => true]);
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
            $this->response->sendAsJson(['success' => true]);
        }
        $this->response->sendAsJson(['success' => false]);
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
            $extension = $this->db->getRepository('\Fraym\Block\Entity\Extension')->findOneById(
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
                    $newBlock = new BlockXml();
                    $newBlock->init($block->config);
                } else {
                    $newBlock = new BlockXml();
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

                $changeSet = new \Fraym\Block\Entity\ChangeSet();
                $changeSet->contentId = $blockConfigGP->contentId;
                $changeSet->name = $blockConfigGP->name;
                $changeSet->position = $block ? $block->position : 0;
                $changeSet->byRef = $block ? $block->byRef : null;
                $changeSet->menuItem = isset($blockConfigGP->menu) && $blockConfigGP->menu == '1' ? null : $menu;
                $changeSet->site = $menu->site;
                $changeSet->menuItemTranslation = $blockConfigGP->menuTranslation === 'current' ? $menuItemTranslation : null;
                $changeSet->extension = $extension;
                $changeSet->block = $block;
                $changeSet->user = $this->user->getUserEntity();

                if ($block) {
                    $changeSet->type = \Fraym\Block\Entity\ChangeSet::EDITED;
                } else {
                    $changeSet->type = \Fraym\Block\Entity\ChangeSet::ADDED;
                    $block = $changeSet;
                }

                $this->db->persist($changeSet);
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
                if ($changeSet->byRef === null) {
                    $changeSet->config = $blockConfig;
                } else {
                    $changeRefBlock = clone $changeSet->byRef;
                    $changeRefBlock->config = $blockConfig;
                    $this->createChangeSet($changeRefBlock, $changeSet->byRef, \Fraym\Block\Entity\ChangeSet::EDITED);
                }

                $this->db->flush();

                $data = $this->prepareBlockOutput($changeSet);
                $this->response->sendAsJson(['data' => $data, 'blockId' => $block->id]);
            }
        }

        $this->response->sendAsJson(['error' => $result]);
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
            $blockParser->getXmlObjectFromString($blockXmlStringWithId),
            true
        );
    }

    /**
     * AJAX handler function
     *
     * @Fraym\Annotation\Route("/ajax", name="fraymAjaxHandler")
     * @return bool
     */
    public function ajaxHandler()
    {
        if ($this->user->isAdmin() === false) {
            $this->response->sendPageNotFound();
        }

        $cmd = trim($this->request->gp('cmd', ''));

        if (method_exists($this, $cmd)) {
            return $this->$cmd();
        }
        return false;
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
        $extension = null;

        if ($extensionId) {
            $extension = $this->db->getRepository('\Fraym\Block\Entity\Extension')->findOneById($extensionId);
            $result = $extension->toArray(1);
        } elseif ($id) {
            $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($id);

            if ($block) {
                if ($block->changeSets->count()) {
                    $block = $block->changeSets->last();
                }
                $extension = $block->extension;
                $arrayFromXml = $this->blockParser->xmlToArray(
                    $this->blockParser->getXmlObjectFromString($this->blockParser->wrapBlockConfig($block))
                );

                $result->xml = $arrayFromXml['block'];
                $result = (object)array_merge($block->toArray(2), $block->extension->toArray(1), (array)$result);
                $result->blockName = $block->name;
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
            $extension = $this->db->getRepository('\Fraym\Block\Entity\Extension')->findOneById($id);

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
        $blocks = $this->request->gp('blocks', []);

        foreach ($blocks as $k => $block) {
            if (isset($block['blockId'])) {
                $movedblock = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($block['blockId']);
                if ($movedblock) {
                    if ($movedblock->changeSets->count()) {
                        $changedBlock = clone $movedblock->changeSets->last();
                    } else {
                        $changedBlock = clone $movedblock;
                    }

                    $changedBlock->contentId = $block['contentId'];
                    $changedBlock->position = intval($k);
                    $this->createChangeSet($changedBlock, $movedblock, \Fraym\Block\Entity\ChangeSet::MOVED);
                } else {
                    $this->response->sendAsJson(['success' => false]);
                }
            } else {
                $this->response->sendAsJson(['success' => false]);
            }
        }

        $this->response->sendAsJson(['success' => true]);
    }


    /**
     * Delete a block
     */
    private function deleteBlock()
    {
        $blockId = $this->request->gp('blockId', false);
        if ($blockId && ($block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId))) {
            if ($block->changeSets->count()) {
                $changedBlock = $block->changeSets->last();
            } else {
                $changedBlock = $block;
            }
            $this->createChangeSet($changedBlock, $block, \Fraym\Block\Entity\ChangeSet::DELETED);
            $this->response->sendAsJson(['success' => true]);
        }
    }

    /**
     * @param $changedBlock
     * @param $parentBlock
     * @param $type
     * @return Entity\ChangeSet
     */
    private function createChangeSet($changedBlock, $parentBlock, $type)
    {
        $changeSet = new \Fraym\Block\Entity\ChangeSet();
        $changeSet->contentId = $changedBlock->contentId;
        $changeSet->position = $changedBlock->position;
        $changeSet->menuItem = $changedBlock->menuItem;
        $changeSet->site = $changedBlock->site;
        $changeSet->menuItemTranslation = $changedBlock->menuItemTranslation;
        $changeSet->extension = $changedBlock->extension;
        $changeSet->user = $this->user->getUserEntity();
        $changeSet->byRef = $changedBlock->byRef;
        $changeSet->block = $parentBlock;
        $changeSet->type = $type;

        if (!$changedBlock->byRef) {
            $changeSet->config = $changedBlock->config;
        }

        $this->db->persist($changeSet);
        $this->db->flush();
        return $changeSet;
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

        if ($contentId && $blockId &&
            ($block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId))) {
            $menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuId);
            $blocks = $this->db->getRepository('\Fraym\Block\Entity\Block')->findBy(
                ['menuItem' => $menuItem, 'contentId' => $contentId],
                ['position' => 'asc']
            );

            // Re-Order other blocks
            foreach ($blocks as $k => $b) {
                $b->position = $k+1;
            }

            if ($op === 'copy') {
                $block = $block->changeSets->count() ? $block->changeSets->last() : $block;
                $copiedBlock = clone $block;

                $copiedBlock->id = null;
                $copiedBlock->position = 0;
                $copiedBlock->contentId = $contentId;
                $copiedBlock->menuItem = $menuItem;

                if ($byRef === true) {
                    $copiedBlock->config = null;
                    if ($block->byRef) {
                        $block = $block->byRef;
                        $copiedBlock->byRef = $block;
                    }
                    $copiedBlock->byRef = $block;
                }

                $changedBlock = $this->createChangeSet($copiedBlock, null, \Fraym\Block\Entity\ChangeSet::ADDED);
            } else {
                $changedBlock = $block->changeSets->count() ? $block->changeSets->last() : $block;
                $changedBlock->position = 0;
                $changedBlock->menuItem = $menuItem;
                $changedBlock->contentId = $contentId;
                $this->createChangeSet($changedBlock, $block, \Fraym\Block\Entity\ChangeSet::MOVED);
            }
            $this->db->flush();
            $this->response->sendAsJson(['success' => true, 'data' => $this->prepareBlockOutput($changedBlock)]);
        }
        $this->response->sendAsJson(
            ['success' => false, 'message' => $this->translation->getTranslation('Paste error, please reload the page and copy again.')]
        );
    }

    /**
     *  Render a only a block template
     */
    public function renderContentBlock($xml)
    {
        $this->blockParser->setCurrentParsingBlockId($this->blockParser->getXmlAttr($xml, 'id'));
        $this->view->setTemplate('Content');
    }
}
