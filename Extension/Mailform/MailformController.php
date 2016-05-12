<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\Mailform;

/**
 * Class MailformController
 * @package Extension\Mailform
 * @Injectable(lazy=true)
 */
class MailformController extends \Fraym\Core
{
    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * Render template
     */
    public function renderHtml($submit, $values, $errors)
    {
        $this->view->assign('values', $values);
        $this->view->assign('submit', $submit);
        $this->view->assign('errors', $errors);
        $this->view->setTemplate('Block');
    }
}
