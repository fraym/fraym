<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\SiteManager;

/**
 * Class SiteManagerController
 * @package Fraym\SiteManager
 * @Injectable(lazy=true)
 */
class SiteManagerController extends \Fraym\Core
{
    /**
     * @Inject
     * @var \Fraym\Block\Block
     */
    protected $block;

    /**
     * @Inject
     * @var \Fraym\Cache\Cache
     */
    protected $cache;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

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
     * @var \Fraym\Translation\Translation
     */
    public $translation;

    /**
     * @return bool
     */
    public function checkRoute()
    {
        $allowCmds = array('setEditMode');
        $cmd = trim($this->request->gp('cmd', ''));

        if (in_array($cmd, $allowCmds)) {
            return true;
        }
        return false;
    }

    /**
     * @param $content
     * @param array $options
     * @return mixed
     */
    public function getIframeContent($content, $options = array())
    {
        $this->view->assign('options', $options);
        $this->view->assign('content', $content);
        return $this->view->fetch('Iframe.tpl');
    }

    /**
     * @return mixed
     */
    public function getAdminPanel()
    {
        $extensions = $this->db->getRepository('\Fraym\SiteManager\Entity\Extension')->findBy(
            array('active' => 1),
            array('sorter' => 'asc')
        );

        $extensionSorted = array();

        foreach ($extensions as $extension) {
            if (!isset($extensionSorted[$extension->id])) {
                $extensionSorted[$extension->id] = array();
            }
            $extensionSorted[$extension->id] = $this->translation->getTranslation(
                $extension->name,
                'SITE_EXT_' . strtoupper(str_ireplace(' ', '_', $extension->name))
            );
        }

        uasort(
            $extensionSorted,
            function ($a, $b) {
                return strcasecmp($a, $b);
            }
        );

        $this->view->assign('extensions', $extensionSorted);
        $this->view->assign('inEditMode', $this->block->inEditMode());

        return $this->getIframeContent(
            $this->view->fetch('AdminPanelContent'),
            array('cssClass' => 'admin-panel')
        );
    }

    /**
     * @return mixed
     */
    public function getSiteManagerExtension()
    {
        return $this->getIframeContent($this->view->fetch('siteManagerExtensionOverview.tpl'));
    }

    /**
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

    /**
     *
     */
    public function setEditMode()
    {
        $user = $this->user;
        $data = $this->request->getGPAsObject();

        if ($user->isAdmin()) {
            $this->block->setEditMode($data->value == '0' ? false : true);
        }
        $this->response->sendAsJson();
    }

    /**
     * @return mixed
     */
    public function adminPanelInit()
    {
        if ($this->user->isAdmin() === false) {
            return;
        }
        $cmd = $this->request->gp('cmd', false);
        if ($cmd !== false) {
            if (method_exists($this, $cmd)) {
                return $this->$cmd();
            }
        }

        $this->view->addHeadData(
            '<script type="text/javascript">var menu_id=\'' . $this->route->getCurrentMenuItem(
            )->id . '\';var menu_translation_id=\'' . $this->route->getCurrentMenuItemTranslation(
            )->id . '\';var base_path=\'' . $this->route->getSiteBaseURI(
            ) . '\';var menu_path=\'' . $this->route->getMenuPath() . '\';</script>'
        );
        $this->view->assign('inEditMode', $this->block->inEditMode());
        $this->view->render('AdminPanel');
    }
}
