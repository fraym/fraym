<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Registry;

/**
 * Class RegistryManagerController
 * @package Fraym\Registry
 * @Injectable(lazy=true)
 */
class RegistryManagerController extends \Fraym\Core
{
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
     * @return mixed
     */
    public function getContent()
    {
        if ($this->user->isAdmin() === false) {
            return false;
        }

        if ($this->request->isPost()) {
            $cmd = $this->request->post('cmd', null);
            if (method_exists($this, $cmd)) {
                return $this->$cmd();
            }
            $this->response->finish();
        }

        $extensions = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findBy(
            array(),
            array('name' => 'asc')
        );

        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();

        $extensionUpdates = new \Doctrine\Common\Collections\ArrayCollection();

        $updates = $this->registryManager->getUpdates($extensions);
        if (is_object($updates)) {
            foreach ($updates as $k => $update) {
                $extensionUpdates->set($k, $update);
            }
        }

        $this->view->assign('unregisteredExtensions', $unregisteredExtensions);
        $this->view->assign('extensions', $extensions);
        $this->view->assign('extensionUpdates', $extensionUpdates);
        return $this->siteManagerController->getIframeContent($this->template->fetch('Extension'));
    }

    /**
     *
     */
    public function installExtension()
    {
        $extensionHash = $this->request->post('extensionHash', null);
        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();
        foreach ($unregisteredExtensions as $class => $extension) {
            if ($extension['fileHash'] === $extensionHash) {
                $this->registryManager->registerClass($class, $extension['file']);
                return $this->response->sendAsJson();
            }
        }
        $this->response->sendHTTPStatusCode(500)->finish();
    }

    /**
     *
     */
    public function uninstallExtension()
    {
        $extensionId = $this->request->post('extensionId', null);
        $this->registryManager->unregisterExtension($extensionId);
        return $this->response->sendAsJson();
    }

    /**
     *
     */
    public function removeExtension()
    {
        $extensionHash = $this->request->post('extensionHash', null);
        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();
        foreach ($unregisteredExtensions as $class => $extension) {
            if ($extension['fileHash'] === $extensionHash) {
                $this->registryManager->removeExtensionFiles($extension);
            }
        }
        $this->response->finish();
    }

    /**
     *
     */
    public function downloadExtension()
    {
        $repositoryKey = $this->request->post('repositoryKey', '');
        $this->registryManager->downloadExtension($repositoryKey);
        $this->response->finish();
    }

    /**
     *
     */
    public function updateExtension()
    {
        $repositoryKey = $this->request->post('repositoryKey', '');
        $this->registryManager->updateExtension($repositoryKey);
        $this->response->finish();
    }

    /**
     * @Fraym\Annotation\Route("/fraym/registry/download", name="registryManagerDownload", permission={"GROUP:Administrator"})
     */
    public function downloadPackage()
    {
        $repositoryKey = $this->request->get('repositoryKey', '');
        $registryEntry = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findOneByRepositoryKey(
            $repositoryKey
        );
        $zipFile = $this->registryManager->buildPackage($registryEntry);
        $this->response->addHTTPHeader("Content-type: application/zip");
        $this->response->addHTTPHeader("Content-Disposition: attachment; filename=\"" . $repositoryKey . ".zip\"");
        $this->response->addHTTPHeader("Content-Transfer-Encoding: binary");
        $this->response->addHTTPHeader("Content-Length: " . filesize($zipFile));
        $content = file_get_contents($zipFile);
        @unlink($zipFile);
        $this->response->send($content);
    }

    /**
     * @return bool|mixed|string
     */
    public function repositorySearch()
    {
        $searchTerm = $this->request->post('term', '');
        $availableExtensions = new \Doctrine\Common\Collections\ArrayCollection();
        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();
        $installedExtensions = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findBy(
            array(),
            array('name' => 'asc')
        );

        $extensions = $this->registryManager->repositoryRequest(
            'listModules',
            array('searchTerm' => $searchTerm, 'offset' => 0, 'limit' => 20)
        );

        foreach ($installedExtensions as $installedExtension) {
            $availableExtensions->set($installedExtension->repositoryKey, $installedExtension);
        }

        foreach ($unregisteredExtensions as $unregisteredExtension) {
            $availableExtensions->set($unregisteredExtension['repositoryKey'], $unregisteredExtension);
        }

        $this->view->assign('availableExtensions', $availableExtensions);
        $this->view->assign('extensions', $extensions ? : array());
        return $this->template->fetch('RepositorySearch');
    }
}
