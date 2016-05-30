<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\EntityManager;

/**
 * Class EntityManagerController
 * @package Fraym\EntityManager
 * @Injectable(lazy=true)
 */
class EntityManagerController extends \Fraym\Core
{

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManagerController;

    /**
     * @Inject
     * @var \Fraym\Validation\Validation
     */
    protected $validation;

    /**
     * @Inject
     * @var \Fraym\Entity\FormField
     */
    protected $formField;

    /**
     * @Inject
     * @var \Fraym\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @return bool|mixed
     */
    public function getContent()
    {
        if($this->user->isAdmin() === false) {
            return false;
        }

        $currentEntity = false;
        $errors = false;
        $data = [];
        $formFields = [];
        $entities = [];
        $modelClass = false;
        $saveError = false;
        $model = false;
        $modelName = $this->request->gp('model', false);

        $groupedModels = $this->db->getRepository('\Fraym\EntityManager\Entity\Group')->findAll();

        if ($modelName) {
            $model = $this->entityManager->getEntityByStringOrId($modelName);
            if ($model) {
                $modelClass = $model->className;
            }
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->createEntityFromSingleField($modelClass);
        }

        if ($modelClass && $this->request->isPost()) {
            $data = $this->request->getGPAsArray();

            $validation = $this->validation->setData($data)->getFormFieldValidation($modelClass);

            try {
                if ($id = $this->request->post('id')) {
                    $currentEntity = $this->db->getRepository($modelClass)->findOneById($id);

                    if (isset($data['cmd']) &&
                        $data['cmd'] == 'update' &&
                        $currentEntity &&
                        ($errors = $validation->check()) === true
                    ) {
                        $currentEntity->updateEntity($data);
                    } elseif (isset($data['cmd']) && $data['cmd'] == 'remove' && $currentEntity) {
                        $this->db->remove($currentEntity);
                        $this->db->flush();
                        $data = [];
                        $currentEntity = false;
                    } elseif (isset($data['cmd']) && $data['cmd'] == 'update') {
                        $currentEntity->updateEntity($data, false);
                    }

                } else {
                    if (isset($data['cmd']) && $data['cmd'] == 'new' && ($errors = $validation->check()) === true) {
                        $currentEntity = new $modelClass();
                        $currentEntity->updateEntity($data);
                    }
                }
            } catch(\Exception $e) {
                $saveError = true;
            }
        }
        if ($modelClass && $model) {
            $entities = $this->db->getRepository($modelClass)->findAll();
            $formFields = $this->formField->setClassName($modelClass)->getFields();
        }

        $this->view->assign('locales', $this->locale->getLocales());
        $this->view->assign('data', $data);
        $this->view->assign('saveError', $saveError);
        $this->view->assign('errors', $errors);
        $this->view->assign('currentEntity', $currentEntity);
        $this->view->assign('entities', $entities);
        $this->view->assign('groupedModels', $groupedModels);
        $this->view->assign('model', $model);
        $this->view->assign('formFields', $formFields);
        return $this->siteManagerController->getIframeContent($this->view->fetch('EntityView'));
    }

    /**
     * @param $modelClass
     * @return bool
     */
    private function createEntityFromSingleField($modelClass)
    {
        $field = $this->request->post('field');
        $value = $this->request->post('value');
        if (empty($value) === false && empty($field) === false) {
            $currentEntity = new $modelClass();
            $currentEntity->updateEntity([$field => $value]);
            $this->response->sendAsJson(['id' => $currentEntity->id]);
        }
        return false;
    }
}
