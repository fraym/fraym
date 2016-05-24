<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\BugReport;

use Fraym\Annotation\Registry;

/**
 * Class BugReportController
 * @package Extension\BugReport
 * @Registry(
 * name="Bug Report",
 * description="Bug Report for Fraym.",
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * repositoryKey="FRAYM_EXT_BUGREPORT",
 * updateEntity={
 *      "\Fraym\SiteManager\Entity\Extension"={
 *          {
 *           "name"="Bug Report",
 *           "class"="\Extension\BugReport\BugReportController",
 *           },
 *      },
 * },
 * entity={
 *      "\Fraym\SiteManager\Entity\Extension"={
 *          {
 *           "name"="Bug Report",
 *           "class"="\Extension\BugReport\BugReportController",
 *           "method"="getContent",
 *           "active"="1",
 *           "description"="EXT_EXTENSION_BUGREPORT_DESC",
 *           "iconCssClass"="fa fa-bug"
 *           },
 *      },
 * },
 * files={
 *      "Extension/BugReport/",
 *      "Template/Default/Extension/BugReport/",
 *      "Public/js/fraym/extension/BugReport/",
 * }
 * )
 * @Injectable(lazy=true)
 */
class BugReportController extends \Fraym\Core
{
    const BUGREPORT_URL = 'http://fraym.org/bugreport';

    /**
     * @Inject
     * @var \Fraym\SiteManager\SiteManagerController
     */
    protected $siteManagerController;

    /**
     * @Inject
     * @var \Fraym\Validation\Validation
     */
    public $validation;

    /**
     *
     */
    public function getContent()
    {
        $values = [];
        $errors = [];
        $result = [];

        if ($this->request->isPost()) {
            $fields = $this->request->post('field');

            $this->validation->setData($fields);
            $this->validation
                ->addRule('email', 'email')
                ->addRule('subject', 'notEmpty')
                ->addRule('name', 'notEmpty')
                ->addRule('description', 'notEmpty')
                ->addRule('reproduce', 'notEmpty');

            $check = $this->validation->check();
            if ($check === true) {
                $params = [
                    'fields' => $fields,
                    'client_info' => [
                        'version' => \Fraym\Core::VERSION,
                        'php_version' => phpversion(),
                        'os' => php_uname('s'),
                        'apc_enabled' => APC_ENABLED,
                        'image_processor' => IMAGE_PROCESSOR,
                        'server' => $_SERVER,
                        'env' => ENV,
                    ]
                ];
                $result = $this->request->send(self::BUGREPORT_URL, $params);
            } else {
                $errors = $check;
            }
        }

        $this->view->assign('result', $result);
        $this->view->assign('values', $values);
        $this->view->assign('errors', $errors);

        return $this->siteManagerController->getIframeContent($this->view->fetch('BugReport'));
    }
}
