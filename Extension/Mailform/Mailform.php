<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\Mailform;

use \Fraym\Block\BlockXml as BlockXml;
use Fraym\Annotation\Registry;

/**
 * @package Extension\Mailform
 * @Registry(
 * name="Mailform",
 * description="Create a mail formular.",
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * repositoryKey="FRAYM_EXT_MAILFORM",
 * entity={
 *      "\Fraym\Block\Entity\Extension"={
 *          {
 *           "name"="Mailform",
 *           "description"="Create a mail formular.",
 *           "class"="\Extension\Mailform\Mailform",
 *           "execMethod"="execBlock"
 *           },
 *      }
 * },
 * files={
 *      "Extension/Mailform/",
 *      "Template/Default/Extension/Mailform/"
 * },
 * config={
  *      "MAILFORM_SUBJECT"={"value"="Mailform contact"},
  *      "MAILFORM_TO"={"value"="info@localhost"},
  * }
 * )
 * @Injectable(lazy=true)
 */
class Mailform
{
    /**
     * @Inject
     * @var \Extension\Mailform\MailformController
     */
    protected $mailformController;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    /**
     * @Inject
     * @var \Fraym\Validation\Validation
     */
    public $validation;

    /**
     * @Inject
     * @var \Fraym\Template\Template
     */
    public $template;

    /**
     * @Inject
     * @var \Fraym\Registry\Config
     */
    public $config;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    public $translation;

    /**
     * @Inject
     * @var \Fraym\Mail\Mail
     */
    public $mail;

    /**
     * @param $xml
     * @return mixed
     */
    public function execBlock($xml)
    {
        $errors = [];
        $submit = false;
        $values = $this->request->getGPAsArray();
        if ($this->request->post('mailform')) {
            $submit = true;
            $required = $values['required'];
            $fields = $values['field'];
            $this->validation->setData($fields);
            $this->validation->addRule('email', 'email');
            $errorMessages = [];

            foreach ($required as $field => $val) {
                $this->validation->addRule($field, 'notEmpty');
                $errorMessages = array_merge($errorMessages, [
                    $field => [$field => $this->translation->getTranslation('Please fill out the field')],
                ]);
            }

            $this->validation->setErrorMessages($errorMessages);
            $check = $this->validation->check();
            if ($check === true) {
                $msg = $this->mail->getMessageInstance();
                $msg->setFrom([$fields['email']]);
                $msg->setSubject($this->config->get('MAILFORM_SUBJECT')->value);
                $msg->setTo(explode(',', $this->config->get('MAILFORM_TO')->value));

                $this->template->assign('fields', $fields);
                $msg->setBody($this->template->fetch('Extension/Mailform/Mail'), 'text/html');
                $this->mail->send();
            } else {
                $errors = $check;
            }
        }
        $this->mailformController->renderHtml($submit, $values, $errors);
    }
}
