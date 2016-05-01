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
     * Exec content element template
     *
     * @param $xml
     */
    public function execBlock($xml)
    {
        return $this->blockController->renderContentBlock($xml);
    }
}
