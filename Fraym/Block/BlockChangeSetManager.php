<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * Class BlockChangeSetManager
 * @package Fraym\Block
 * @Injectable(lazy=true)
 */
class BlockChangeSetManager
{
    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Cache\Cache
     */
    protected $cache;

    /**
     * @return bool|mixed
     */
    public function getSites()
    {
        return $this->db->getRepository('\Fraym\Site\Entity\Site')->findAll();
    }

    /**
     * @return bool|mixed
     */
    public function getChangeSets()
    {
        $changeSets = [];
        $query = $this->db->createQueryBuilder();

        $results = $query
            ->select("block, byRef")
            ->from('\Fraym\Block\Entity\Block', 'block')
            ->leftJoin('block.byRef', 'byRef')
            ->leftJoin('block.changeSets', 'cs')
            ->andWhere('(block.changeSets IS NOT EMPTY AND block INSTANCE OF \Fraym\Block\Entity\Block) OR (block.block IS NULL AND block INSTANCE OF \Fraym\Block\Entity\ChangeSet)')
            ->addOrderBy('cs.created', 'desc')
            ->getQuery()
            ->getResult();

        foreach ($results as $block) {
            if (!isset($changeSets[$block->site->id])) {
                $changeSets[$block->site->id] = [];
            }

            $menuItemId = $block->menuItem ? $block->menuItem->id : 0;

            if (!isset($changeSets[$block->site->id][$menuItemId])) {
                $changeSets[$block->site->id][$menuItemId] = [];
            }
            $translationId = $block->menuItemTranslation ? $block->menuItemTranslation->id : 0;
            if (!isset($changeSets[$block->site->id][$menuItemId][$translationId])) {
                $changeSets[$block->site->id][$menuItemId][$translationId] = [
                    'menuItem' => $block->menuItem,
                    'menuItemTranslation' => $block->menuItemTranslation,
                    'blocks' => [],
                ];
            }
            $lastChange = $block->changeSets->count() ? $block->changeSets->last() : $block;
            $changeSets[$block->site->id][$menuItemId][$translationId]['blocks'][$block->id] = $lastChange;
        }
        return $changeSets;
    }

    /**
     * @param $block
     */
    public function deploy($block)
    {
        if (count($block->changeSets)) {
            $lastChange = clone $block->changeSets->last();
        }

        // New blocks
        if (get_class($block) === 'Fraym\Block\Entity\ChangeSet') {
            if (count($block->changeSets) === 0) {
                $lastChange = clone $block;
            }
            if ($lastChange->type !== Entity\ChangeSet::DELETED) {
                $this->db->remove($block);
                $this->db->flush();
                $block = new Entity\Block();
            }
        }

        foreach ($block->changeSets as $change) {
            $this->db->remove($change);
        }

        $this->db->flush();

        if ($lastChange->type === Entity\ChangeSet::DELETED) {
            $this->db->remove($block);
        } else {
            $block->contentId = $lastChange->contentId;
            $block->name = $lastChange->name;
            $block->position = $lastChange->position;
            $block->menuItem = $lastChange->menuItem;
            $block->site = $lastChange->site;
            $block->user = $lastChange->user;
            $block->byRef = $lastChange->byRef;
            $block->menuItemTranslation = $lastChange->menuItemTranslation;
            $block->extension = $lastChange->extension;
            if (!$block->byRef) {
                $block->config = $lastChange->config;
            }

            $this->db->persist($block);
        }

        $this->db->flush();
        $this->cache->clearAll();
    }

    /**
     * @param $block
     */
    public function undoBlock($block)
    {
        foreach ($block->changeSets as $change) {
            $this->db->remove($change);
        }
        if (get_class($block) === 'Fraym\Block\Entity\ChangeSet') {
            $this->db->remove($block);
        }
        $this->db->flush();
    }

    /**
     * Return deployed block count
     *
     * @return int
     */
    public function deployAll()
    {
        $count = 0;
        $changeSets = $this->getChangeSets();
        foreach ($changeSets as $sites) {
            foreach ($sites as $menuItems) {
                foreach ($menuItems as $data) {
                    foreach ($data['blocks'] as $blockId => $lastChagedBlock) {
                        $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
                        $this->deploy($block);
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    /**
     * Return undo block count
     *
     * @return int
     */
    public function undoAll()
    {
        $count = 0;
        $changeSets = $this->getChangeSets();
        foreach ($changeSets as $sites) {
            foreach ($sites as $menuItems) {
                foreach ($menuItems as $data) {
                    foreach ($data['blocks'] as $blockId => $lastChagedBlock) {
                        $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
                        $this->undoBlock($block);
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    /**
     * @param $menu
     * @param bool $undo
     */
    public function deployMenu($menu, $undo = false)
    {
        list($siteId, $menuId, $menuTranslationId) = explode(',', $menu);
        $menuTranslationId = intval($menuTranslationId);
        $changeSets = $this->getChangeSets();

        if (isset($changeSets[$siteId][$menuId][$menuTranslationId])) {
            foreach ($changeSets[$siteId][$menuId][$menuTranslationId]['blocks'] as $blockId => $change) {
                $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
                if ($undo) {
                    $this->undoBlock($block);
                } else {
                    $this->deploy($block);
                }
            }
        }
    }

    /**
     * @param $blockId
     * @param bool $undo
     */
    public function deployBlock($blockId, $undo = false)
    {
        $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
        if ($undo) {
            $this->undoBlock($block);
        } else {
            $this->deploy($block);
        }
    }
}
