<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Mail;

/**
 * Class Mail
 * @package Fraym\Mail
 */
class Mail
{
    private $messageInstance;
    private $transport = null;

    public function __construct()
    {
        $this->messageInstance = \Swift_Message::newInstance();
        $this->useSendmail();
    }

    /**
     * Call default methods
     *
     * @param $method
     * @param $param
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $param)
    {
        if (is_object($this->messageInstance) && method_exists($this->messageInstance, $method)) {
            return call_user_func_array([&$this->messageInstance, $method], $param);
        }
        throw new \Exception('Undefined method name.');
    }

    /**
     * @return \Swift_Mime_Message
     */
    public function getMessageInstance()
    {
        return $this->messageInstance;
    }

    /**
     * @param \Swift_Mime_Message $messageInstance
     * @return $this
     */
    public function setMessageInstance($messageInstance)
    {
        $this->messageInstance = $messageInstance;
        return $this;
    }

    /**
     * @return null|\Swift_MailTransport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param \Swift_MailTransport $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Sending the mail.
     * @return int
     */
    public function send()
    {
        //Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($this->transport);
        return $mailer->send($this->messageInstance);
    }

    /**
     * Embed a file from a string. Returns the CID id.
     *
     * @param  $fileContent
     * @param  $filename
     * @param string $mimeType
     * @return string
     */
    public function embedFromStream($fileContent, $filename, $mimeType = 'image/jpeg')
    {
        return $this->messageInstance->embed(\Swift_Image::newInstance($fileContent, $filename, $mimeType));
    }

    /**
     * If you want to use a SMTP server to send the mails just call this function.
     *
     * @param string $host
     * @param int $port
     * @param null $security
     * @param null $username
     * @param null $password
     * @return Mail
     */
    public function useSMTP($host = 'localhost', $port = 25, $security = null, $username = null, $password = null)
    {
        $this->transport = Swift_SmtpTransport::newInstance($host, $port, $security);
        if ($username !== null) {
            $this->transport->setUsername($username);
        }
        if ($password !== null) {
            $this->transport->setPassword($password);
        }
        return $this;
    }

    /**
     * If you want to use Sendmail than call this function
     *
     * @param string $command
     * @return Mail
     */
    public function useSendmail($command = '/usr/sbin/sendmail -bs')
    {
        $this->transport = \Swift_SendmailTransport::newInstance($command);
        return $this;
    }

    /**
     * Embed a file to the email content.
     *
     * @param  $file
     * @return mixed
     */
    public function embedFromFile($file)
    {
        return $this->messageInstance->embed(Swift_Image::fromPath($file));
    }

    /**
     * Adds a attachment to the email
     *
     * @param  $file
     * @param  $filename
     * @param null $mimeType
     * @param bool $inline
     * @return Mail
     */
    public function addAttachment($file, $filename, $mimeType = null, $inline = false)
    {
        $attachment = \Swift_Attachment::fromPath($file)->setFilename($filename);
        if ($inline) {
            $attachment->setDisposition('inline');
        }
        if ($mimeType !== null) {
            $attachment->setContentType($mimeType);
        }
        $this->messageInstance->attach($attachment);
        return $this;
    }
}
