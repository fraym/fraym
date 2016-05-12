<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Menu;

/**
 * Class CustomMenu
 * @package Fraym\Menu
 * @Injectable(lazy=true)
 */
class CustomMenu
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
     * @var \Fraym\Route\Route
     */
    protected $route;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    /**
     * @param $blockId
     * @param \Fraym\Block\BlockXml $blockXML
     * @return \Fraym\Block\BlockXml
     */
    public function saveSiteMenu($blockId, \Fraym\Block\BlockXml $blockXML)
    {
        $blockConfig = $this->request->getGPAsObject();
        $newMenuItems = json_decode($blockConfig->customMenu);

        $iteration = function (&$dom, $newMenuItems) use (&$iteration) {
            $childs = [];
            foreach ($newMenuItems as $item) {
                $element = $dom->createElement('item');
                $element->setAttribute('id', $item->key);
                if (isset($item->children) && count($item->children)) {
                    $subChilds = $iteration($dom, $item->children);
                    foreach ($subChilds as $child) {
                        $element->appendChild($child);
                    }
                }
                $childs[] = $element;
            }
            return $childs;
        };

        if (count($newMenuItems)) {
            $customDom = new \Fraym\Block\BlockXmlDom();
            $element = $customDom->createElement('menuItems');
            $element->setAttribute('site', $blockConfig->site);
            $childs = $iteration($customDom, $newMenuItems);
            foreach ($childs as $child) {
                $element->appendChild($child);
            }
            $customDom->appendChild($element);

            $blockXML->setCustomProperty($customDom);
        }

        return $blockXML;
    }

    /**
     * @param \SimpleXMLElement $obj
     * @param null $parent
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    private function getItems(\SimpleXMLElement $obj, $parent = null)
    {
        $returnChildren = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($obj->children() as $objData) {
            $id = $this->blockParser->getXmlAttr($objData, 'id');
            $menuItemTranslation = $this->db->createQueryBuilder()
                ->select("menu, translation")
                ->from('\Fraym\Menu\Entity\MenuItemTranslation', 'translation')
                ->join('translation.menuItem', 'menu')
                ->where('menu.id = :id AND translation.locale = :localeId AND translation.active = 1')
                ->setParameter('id', $id)
                ->setParameter('localeId', $this->route->getCurrentMenuItemTranslation()->locale->id)
                ->getQuery()
                ->getOneOrNullResult();

            $this->db->free();

            if ($menuItemTranslation) {
                $menuItem = clone $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById(
                    $menuItemTranslation->menuItem->id
                );
                $children = $this->getItems($objData, $menuItem);
                $menuItem->parent = $parent;
                $menuItem->children = $children;
                $returnChildren->set($menuItem->id, $menuItem);
            }
        }

        return $returnChildren;
    }

    /**
     * @param $menuItems
     * @return bool|\stdClass
     */
    public function buildMenu($menuItems)
    {
        if (isset($menuItems) && $menuItems->children !== null) {
            $menu = new \stdClass();
            $menu->children = $this->getItems($menuItems);
        } else {
            // Get the root menuItem of the current site.
            $menu = $this->route->getMenu();
        }
        return $menu;
    }
}
