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

        $extensions = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findBy([], ['name' => 'asc']);
        $updates = $this->registryManager->getUpdates($extensions);
        $extensionPackages = $this->registryManager->getExtensionPackages($extensions);
        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();
        $this->registryManager->updateUnregisteredExtensions($unregisteredExtensions);

        $this->view->assign('unregisteredExtensions', $unregisteredExtensions);
        $this->view->assign('extensions', $extensions);
        $this->view->assign('extensionPackages', $extensionPackages, false);
        $this->view->assign('extensionUpdates', $updates, false);
        return $this->siteManagerController->getIframeContent($this->view->fetch('Extension'));
    }

    /**
     *
     */
    public function installExtension()
    {
        set_time_limit(0);
        $repositoryKey = $this->request->post('repositoryKey', null);
        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();
        foreach ($unregisteredExtensions as $class => $extension) {
            if ($extension['repositoryKey'] === $repositoryKey) {
                $this->registryManager->registerClass($class);
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
        set_time_limit(0);
        $extensionId = $this->request->post('extensionId', null);
        $this->registryManager->unregisterExtension($extensionId);
        return $this->response->sendAsJson();
    }

    /**
     *
     */
    public function removeExtension()
    {
        set_time_limit(0);
        $repositoryKey = $this->request->post('repositoryKey', null);
        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();
        foreach ($unregisteredExtensions as $class => $extension) {
            if ($extension['repositoryKey'] === $repositoryKey) {
                $this->registryManager->composerRemove($extension);
            }
        }
        $this->response->finish();
    }

    /**
     *
     */
    public function downloadExtension()
    {
        set_time_limit(0);
        $repositoryKey = $this->request->post('repositoryKey', '');
        $this->registryManager->downloadExtension($repositoryKey);
        $this->response->finish();
    }

    /**
     *
     */
    public function updateExtension()
    {
        set_time_limit(0);
        $repositoryKey = $this->request->post('repositoryKey', '');
        $this->registryManager->updateExtension($repositoryKey);
        $this->response->finish();
    }

    /**
     * @Fraym\Annotation\Route("/fraym/registry/download", name="registryManagerDownload", permission={"\Fraym\User\User"="isAdmin"})
     */
    public function downloadPackage()
    {
        set_time_limit(0);
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
        $extensions = $this->registryManager->searchPackage($searchTerm);
        $availableExtensions = new \Doctrine\Common\Collections\ArrayCollection();
        $unregisteredExtensions = $this->registryManager->getUnregisteredExtensions();
        $installedExtensions = $this->db->getRepository('\Fraym\Registry\Entity\Registry')->findBy(
            [],
            ['name' => 'asc']
        );

        foreach ($installedExtensions as $installedExtension) {
            $availableExtensions->set($installedExtension->repositoryKey, $installedExtension);
        }

        foreach ($unregisteredExtensions as $unregisteredExtension) {
            $availableExtensions->set($unregisteredExtension['repositoryKey'], $unregisteredExtension);
        }

        $this->view->assign('availableExtensions', $availableExtensions);
        $this->view->assign('extensions', $extensions ? : []);
        return $this->view->fetch('RepositorySearch');
    }
}
