<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * Class BlockChangeSetManagerController
 * @package Fraym\Block
 * @Injectable(lazy=true)
 */
class BlockChangeSetManagerController extends \Fraym\Core
{
    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManagerController;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @Inject
     * @var \Fraym\Block\BlockChangeSetManager
     */
    protected $blockChangeSetManager;

    /**
     * @return bool|mixed
     */
    public function getContent()
    {
        if ($this->user->isAdmin() === false) {
            return false;
        }

        $this->view->assign('sites', $this->blockChangeSetManager->getSites());
        $this->view->assign('changeSets', $this->blockChangeSetManager->getChangeSets(), false);
        return $this->siteManagerController->getIframeContent($this->view->fetch('BlockChangeSetManager'));
    }

    /**
     * @Fraym\Annotation\Route("/fraym/deploy-change-set", name="deployChangeSet", permission={"\Fraym\User\User"="isAdmin"})
     */
    public function deployChangeSet()
    {
        if ($this->request->isPost()) {
            $undo = $this->request->post('undo') === 'true' ? true : false;

            if (($menu = $this->request->post('menu')) !== false) {
                $this->blockChangeSetManager->deployMenu($menu, $undo);
            } elseif (($blockId = $this->request->post('block')) !== false) {
                $this->blockChangeSetManager->deployBlock($blockId, $undo);
            } else {
                if ($undo) {
                    $this->blockChangeSetManager->undoAll();
                } else {
                    $this->blockChangeSetManager->deployAll();
                }
            }
        }
        $this->response->sendAsJson();
    }
}
