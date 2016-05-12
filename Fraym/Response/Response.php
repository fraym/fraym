<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Response;

/**
 * Class Response
 * @package Fraym\Response
 * @Injectable(lazy=true)
 */
class Response
{
    /**
     * @var array
     */
    private $httpStatusCodes = [
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
        505 => 'HTTP/1.1 505 HTTP Version Not Supported',
    ];

    /**
     * @Inject
     * @var \Fraym\Core
     */
    protected $core;

    /**
     * @Inject
     * @var \Fraym\Template\Template
     */
    protected $template;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    /**
     * @param array $data
     */
    public function sendAsJson($data = [])
    {
        $data = is_array($data) || is_object($data) ? json_encode($data) : $data;
        $this->addHTTPHeader("Content-type: application/json");
        $this->addHTTPHeader("Content-Transfer-Encoding: binary");
        echo $data;
        $this->finish(true);
    }

    /**
     * @param $data
     * @param bool $parseOutput
     */
    public function send($data, $parseOutput = false)
    {
        $this->addHTTPHeader("Content-Type: text/html");
        $this->addHTTPHeader("Content-Transfer-Encoding: binary");

        if (is_string($data)) {
            echo $data;
        }
        $this->finish(true, $parseOutput);
    }

    /**
     * Send 404 header and ends the script
     */
    public function sendPageNotFound()
    {
        $this->addHTTPHeader('HTTP/1.1 404 Not Found');
        $this->finish();
    }

    /**
     * @param bool $exit
     * @param bool $parseOutput
     * @return $this
     */
    public function finish($exit = true, $parseOutput = false)
    {
        // get the output and eval for precaching
        $content = ob_get_clean();

        if ($parseOutput === true) {
            // Output the source to the client, for dynamic cache we need to eval the output source
            $content = $this->core->includeScript($content);
            // Filter the output to assign css or js files, sets title or meta tags
            $content = $this->template->outputFilter($content);
        }

        $this->addHTTPHeader("Content-Length: " . strlen($content));
        foreach ($this->_httpHeaders as $header) {
            $this->sendHTTPHeader($header);
        }

        echo $content;

        if ($exit) {
            exit();
        }
        return $this;
    }

    /**
     * @param int $code
     * @return $this|bool
     */
    public function sendHTTPStatusCode($code = 301)
    {
        if (isset($this->httpStatusCodes[$code])) {
            $this->sendHTTPHeader($this->httpStatusCodes[$code]);
        }
        return $this;
    }

    /**
     * @param $header
     * @return $this
     */
    public function sendHTTPHeader($header)
    {
        header($header);
        return $this;
    }

    /**
     * @param $header
     * @return $this
     */
    public function addHTTPHeader($header)
    {
        $this->_httpHeaders[] = $header;
        return $this;
    }
}
