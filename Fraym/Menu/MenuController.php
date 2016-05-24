<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Menu;

/**
 * Class MenuController
 * @package Fraym\Menu
 * @Injectable(lazy=true)
 */
class MenuController extends \Fraym\Core
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
     * @var \Fraym\Menu\CustomMenu
     */
    protected $customMenu;

    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManager;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @param $params
     * @return mixed
     */
    private function addAndEditMenuItem($params)
    {
        $menuId = isset($params['parent_id']) ? $params['parent_id'] : $params['menu_id'];
        if (is_numeric($menuId)) {
            $templates = $this->db->getRepository('\Fraym\Template\Entity\Template')->findAll();
            $locales = $this->db->getRepository('\Fraym\Locale\Entity\Locale')->findAll();
            $menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuId);
            $siteId = $menuItem->site->id;

            if (isset($params['parent_id'])) {
                $this->view->assign('menuItem', false);
                // on a new menu item we assign the parent id
                $this->view->assign('parentId', $params['parent_id']);
            } elseif ($menuItem) {
                $this->view->assign('menuItem', $menuItem);
            }

            $this->view->assign('locales', $locales);
            $this->view->assign('siteId', $siteId);
            $this->view->assign('templates', $templates);
            return $this->siteManager->getIframeContent($this->view->fetch('MenuItemView'));
        }
    }

    public function getBlockConfig()
    {
        $sites = $this->db->getRepository('\Fraym\Site\Entity\Site')->findAll();
        $this->view->assign('sites', $sites);
        $this->view->render('AddCustomMenu');
    }

    /**
     * @param $blockId
     * @param \Fraym\Block\BlockXml $blockXML
     * @return \Fraym\Block\BlockXml
     */
    public function saveBlockConfig($blockId, \Fraym\Block\BlockXml $blockXML)
    {
        return $this->customMenu->saveSiteMenu($blockId, $blockXML);
    }

    /**
     * @param $xml
     */
    public function execBlock($xml)
    {
        $currentMenuItem = $this->route->getCurrentMenuItem();

        $showRootPage = strtolower(
            $xml->options->showRootPage
        ) === 'true' || $xml->options->showRootPage == '1' ? true : false;

        $this->view->assign('showRootPage', $showRootPage);
        $this->view->assign('isCustomMenu', isset($xml->menuItems) && $xml->menuItems->children !== null);
        $this->view->assign('root', $this->customMenu->buildMenu($xml->menuItems));
        $this->view->assign('activeItem', $currentMenuItem);

        $this->view->setTemplate('CustomMenuView');
    }

    /**
     * @Fraym\Annotation\Route("/fraym/admin/menu/selection", name="menuSelection", permission={"\Fraym\User\User"="isAdmin"})
     * @return bool|mixed
     */
    public function getContent()
    {
        if ($this->request->isXmlHttpRequest()) {
            return $this->ajaxHandler();
        } else {
            $this->view->assign('sites', $this->db->getRepository('\Fraym\Site\Entity\Site')->findAll());

            $params = $this->request->getGPAsArray();

            if (isset($params['function'])) {
                return $this->addAndEditMenuItem($params);
            }
            $mode = $this->request->gp('mode', false);
            $this->view->assign('mode', $mode);
            return $this->siteManager->getIframeContent($this->view->fetch('SiteMenuOverview'));
        }
    }

    /**
     * @return string
     */
    public function getContentBlockStructure()
    {
        $template_id = intval($this->request->gp('template_id', 0));
        $result = $this->db->getRepository('\Fraym\Template\Entity\Template')->findOneById($template_id);

        $result = $this->blockParser->getBlockOfType('content', $result->html);

        return implode('', $result);
    }

    /**
     *
     */
    public function getSiteMenu()
    {
        $siteId = $this->request->gp('site_id', 0);

        $siteRootMenuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneBy(
            ['site' => $siteId, 'parent' => null]
        );
        if ($siteRootMenuItem) {
            $obj = new \stdClass();
            $obj->children = [$siteRootMenuItem];

            $this->response->sendAsJson($this->toDynatreeArray($obj));
        }
    }

    /**
     * @param $fileArray
     * @return array
     */
    private function toDynatreeArray($fileArray)
    {
        $dynatree = [];

        foreach ($fileArray->children as $menuItem) {
            $title = '';
            foreach ($menuItem->translations as $translation) {
                if ($translation->locale->default) {
                    $title = $translation->title;
                }
            }
            $dynatree[] = [
                'title' => $title,
                'isFolder' => true,
                'key' => $menuItem->id,
                'parent' => $menuItem->parent ? $menuItem->parent->id : false,
                'children' => $this->toDynatreeArray($menuItem)
            ];
        }

        return $dynatree;
    }

    /**
     * @return bool
     */
    public function changeMenuItemPosition()
    {
        $parentId = intval($this->request->gp('parent_id'));
        $menuId = intval($this->request->gp('menu_id'));
        $position = intval($this->request->gp('position'));

        $menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuId);
        if (!$menuItem) {
            return false;
        }
        $newPositions = 0;
        $menuItem->sorter = $position;
        $menuItem->parent = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($parentId);

        foreach ($menuItem->parent->children as $childMenuItem) {
            if ($menuId != $childMenuItem->id) {
                if ($newPositions == $position) {
                    $newPositions++;
                }
                $childMenuItem->sorter = $newPositions;
                $newPositions++;
                $this->db->persist($childMenuItem);
            }
        }
        $this->db->flush();
        return true;
    }

    /**
     *
     */
    private function removeMenuItem()
    {
        $menuId = intval($this->request->gp('menu_id'));

        $menu = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuId);
        $this->db->remove($menu);
        $this->db->flush();
    }

    /**
     *
     */
    private function addMenuItem()
    {
        $menu = $this->request->post('menu');

        if ($menu !== null) {
            $newMenuItem = new \Fraym\Menu\Entity\MenuItem();
            try {
                foreach ($menu['translations'] as $k => $translation) {
                    if (empty($translation['title'])) {
                        unset($menu['translations'][$k]);
                    }
                }
                $newMenuItem->updateEntity($menu);

                $menuItemsTranslations = [];
                foreach ($newMenuItem->translations as $translation) {
                    $menuItemsTranslations[$translation->locale->id] = $translation->id;
                }

                $this->response->sendAsJson(['error' => false, 'menuId' => $newMenuItem->id, 'translations' => $menuItemsTranslations]);
            } catch (\Exception $e) {
                $this->response->sendAsJson(['error' => true, 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     *
     */
    private function editMenuItem()
    {
        $menu = $this->request->post('menu');

        if ($menu !== null) {
            $newMenuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menu['id']);
            try {
                foreach ($menu['translations'] as $k => $translation) {
                    if (empty($translation['title'])) {
                        unset($menu['translations'][$k]);
                    }
                }
                $newMenuItem->updateEntity($menu);
                $this->response->sendAsJson(['error' => false, 'menuId' => $newMenuItem->id, 'translations' => []]);
            } catch (\Exception $e) {
                $this->response->sendAsJson(['error' => true, 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     * @Fraym\Annotation\Route("/fraym/admin/menu/ajax", name="menuControllerAjax", permission={"\Fraym\User\User"="isAdmin"})
     * @return bool
     */
    public function ajaxHandler()
    {
        $cmd = trim($this->request->gp('cmd', ''));
        if (method_exists($this, $cmd)) {
            return $this->$cmd();
        }
        return false;
    }
}
