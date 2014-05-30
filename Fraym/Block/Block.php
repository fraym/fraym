<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * Class Block
 * @package Fraym\Block
 * @Injectable(lazy=true)
 */
class Block
{
    /**
     * @var bool
     */
    private $inEditMode = false;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @Inject
     * @var \Fraym\Block\BlockController
     */
    protected $blockController;

    /**
     * @var \Fraym\User\User
     */
    protected $user;

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
     * @var \Fraym\Session\Session
     */
    protected $session;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    protected $request;

    /**
     * @param \Fraym\Session\Session $session
     * @param \Fraym\User\User $user
     */
    public function __construct(\Fraym\Session\Session $session, \Fraym\User\User $user)
    {
        $this->user = $user;
        $this->session = $session;
        $this->inEditMode = $this->user->isAdmin() && $this->session->get('inEditMode', false);
    }

    /**
     * Callback function to clear the cache if a block element is created or changed
     */
    public function clearCache()
    {
        $location = $this->request->post('location', false);
        $this->cache->deleteCache($location);
    }

    /**
     * @return bool
     */
    public function inEditMode()
    {
        return $this->user->isAdmin() && $this->inEditMode;
    }

    /**
     * @param $val
     * @return bool
     */
    public function setEditMode($val)
    {
        $this->inEditMode = $val;
        $this->session->set('inEditMode', $this->inEditMode);
        return true;
    }

    /**
     * Checks if the current user can do a block action
     *
     * @param $permission
     * @param $extension_id
     * @return bool
     */
    public function permissionAllowed($permission, $extension_id)
    {
        //TODO: Implement this function
        $user = $this->user;
        if (!$user) {
            return false;
        }
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneById($extension_id);

        if ($extension && count($extension->permissions)) {
            $identifiers = $user->getIdentifiersFromGroups();
            $identifiers[] = $user->identifier;

            $result = $this->db->createQueryBuilder()
                ->select("perm")
                ->from('\Fraym\Block\Entity\Permission', 'perm')
                ->where(
                    "perm.extension = :id AND perm.identifier IN ('" . implode(
                        "','",
                        $identifiers
                    ) . "') AND perm.permission LIKE :permission"
                )
                ->setParameter('id', $extension_id)
                ->setParameter('permission', "%{$permission}%")
                ->getQuery()->getOneOrNullResult();

            return $result ? true : false;
        }
        return true;
    }

    /**
     * @param $block
     * @param $historyType
     */
    public function saveHistory($block, $historyType)
    {
        $blockHistory = new \Fraym\Block\Entity\BlockHistory();
        $blockHistory = $block->copyEntityTo($blockHistory);
        $blockHistory->user = $this->user->getUserEntity();
        $blockHistory->type = $historyType;
        $blockHistory = $this->setHistoryFrom($blockHistory, $block, $historyType);

        $this->db->persist($block);
        $this->db->persist($blockHistory);
        $this->db->flush();
    }

    /**
     * @param $blockHistory
     * @param $block
     * @param $historyType
     * @return mixed
     */
    public function setHistoryFrom($blockHistory, $block, $historyType)
    {
        if ($historyType != 'deleted') {
            $blockHistory->from = $block;
        } else {
            foreach ($block->histories as $historyBlock) {
                $historyBlock->from = null;
                $this->db->persist($historyBlock);
            }
            $this->db->flush();
        }
        return $blockHistory;
    }

    /**
     * Exec content element template
     *
     * @param $xml
     */
    public function execBlock($xml)
    {
        return $this->blockController->renderContentBlock($xml);
    }

    /**
     * @param null $blockId
     */
    public function getBlockConfig($blockId = null)
    {
        $configXml = null;
        if ($blockId) {
            $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
            $configXml = $this->blockParser->getXMLObjectFromString($this->blockParser->wrapBlockConfig($block));
        }
        $this->blockController->getBlockContainerConfig($configXml);
    }

    /**
     * @param $blockId
     * @param BlockXML $blockXML
     * @return BlockXML
     */
    public function saveBlockConfig($blockId, \Fraym\Block\BlockXML $blockXML)
    {
        $blockConfig = $this->request->getGPAsArray();
        $customProperties = new \Fraym\Block\BlockXMLDom();
        $config = $customProperties->createElement('sliderConfig');
        foreach ($blockConfig['sliderConfig'] as $field => $value) {
            $element = $customProperties->createElement($field);
            $element->nodeValue = $value;
            $config->appendChild($element);
        }

        $customProperties->appendChild($config);
        $blockXML->setCustomProperty($customProperties);
        return $blockXML;
    }
}
