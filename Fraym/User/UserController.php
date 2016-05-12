<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\User;

/**
 * Class UserController
 * @package Fraym\User
 * @Injectable(lazy=true)
 */
class UserController extends \Fraym\Core
{
    /**
     * @var bool
     */
    private $xml = false;

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
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManagerController;

    /**
     * User LogIn function. Try to login a user by loginname an password.
     *
     * @return mixed
     */
    public function viewLogIn()
    {
        $errorFields = [];

        if ($this->request->isPost() && $this->user->isLoggedIn() === false) {
            $loginName = trim($this->request->post('login_name', ''));
            $password = trim($this->request->post('password', ''));
            $staySignedIn = intval($this->request->post('stay_signed_in', 0)) === 1;

            $user = $this->user->login($loginName, $password, $staySignedIn);
            if (!$user) {
                $errorFields[] = 'user';
                $errorFields[] = 'password';
            }

            $this->view->assign('loginName', $loginName);
            $this->view->assign('user', $user);

            if ($this->checkLoginRedirectConfig()) {
                return;
            }
        }

        $this->view->assign('errorFields', $errorFields);

        // if admin login url render in admin frame
        if ($this->xml === false) {
            return $this->siteManagerController->getIframeContent(
                $this->view->fetch('AdminLogIn'),
                ['cssClass' => 'admin-login']
            );
        } else {
            $this->view->setTemplate('LogIn');
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function checkLoginRedirectConfig()
    {
        if ($this->user->isLoggedIn() &&
            isset($this->xml->onLoginSuccessful) &&
            isset($this->xml->onLoginSuccessful->redirectTo)
        ) {

            if (isset($this->xml->onLoginSuccessful->redirectTo->attributes()->type)) {
                $type = $this->xml->onLoginSuccessful->redirectTo->attributes()->type;
                $redirectToData = (string)$this->xml->onLoginSuccessful->redirectTo;
                switch ($type) {
                    case 'id':
                        $redirectMenuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById(
                            $redirectToData
                        );
                        if ($redirectMenuItem) {
                            $this->route->redirectToURL($this->route->buildFullUrl($redirectMenuItem, true));
                        }
                        break;
                    case 'url':
                        $this->route->redirectToURL((string)$this->xml->onLoginSuccessful->redirectTo);
                        break;
                }
                return true;
            }
        } elseif ($this->user->isLoggedIn()) {
            $this->route->redirectToURL($this->route->getRequestRoute(true));
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkLogoutRedirectConfig()
    {
        if ($this->user->isLoggedIn() === false &&
            isset($this->xml->onLogoutSuccessful) &&
            isset($this->xml->onLogoutSuccessful->redirectTo)
        ) {

            if (isset($this->xml->onLogoutSuccessful->redirectTo->attributes()->type)) {
                $type = $this->xml->onLogoutSuccessful->redirectTo->attributes()->type;
                $redirectToData = (string)$this->xml->onLogoutSuccessful->redirectTo;
                switch ($type) {
                    case 'id':
                        $redirectMenuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById(
                            $redirectToData
                        );
                        if ($redirectMenuItem) {
                            $this->route->redirectToURL($this->route->buildFullUrl($redirectMenuItem, true));
                        }
                        break;
                    case 'url':
                        $this->route->redirectToURL((string)$this->xml->onLogoutSuccessful->redirectTo);
                        break;
                }
                return true;
            }
        } elseif ($this->user->isLoggedIn() === false) {
            $this->route->redirectToURL($this->route->getSiteBaseURI());
            return true;
        }
        return false;
    }

    /**
     * @param null $xml
     * @return string
     */
    public function renderForm($xml = null)
    {
        $this->xml = $xml;

        if ($this->user->isLoggedIn()) {
            $this->viewlogOut();
        } else {
            $this->viewLogIn();
        }
        return '';
    }

    /**
     * @Fraym\Annotation\Route("/fraym", name="adminLogin")
     * @return string
     */
    public function renderAdminPage()
    {
        if ($this->user->isLoggedIn()) {
            return $this->viewlogOut();
        } else {
            return $this->viewLogIn();
        }
    }

    /**
     * LogOut function for Users. It unsets the session.
     *
     * @return mixed
     */
    public function viewlogOut()
    {
        if ($this->request->isPost() && $this->request->post('logout', false) == '1') {
            $this->user->logout();
            $this->checkLoginRedirectConfig();
        }

        $this->view->assign('user', $this->user);
        if ($this->xml === false) {
            return $this->siteManagerController->getIframeContent(
                $this->view->fetch('AdminArea'),
                ['cssClass' => 'admin-login']
            );
        } else {
            $this->view->setTemplate('LogOut');
        }
    }

    /**
     * Sets the 'permissions denied' return value.
     *
     * @param  $redirectRoute
     * @return void
     */
    public function permissionDenied($redirectRoute = null)
    {
        if ($redirectRoute === null) {
            $redirectRoute = $this->route->getSiteBaseURI();
        }
        $this->route->redirectToURL($redirectRoute);
    }

    /**
     * @param null $blockConfig
     */
    public function getBlockConfig($blockConfig = null)
    {
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->render('UserFormConfig');
    }
}
