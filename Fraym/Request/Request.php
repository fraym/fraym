<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Request;

/**
 * Class Request
 * @package Fraym\Request
 */
class Request
{
    /**
     * @return mixed
     */
    public function getGPAsObject()
    {
        $obj = array_merge($_GET, $_POST);
        return json_decode(json_encode($obj));
    }

    /**
     * @return array
     */
    public function getGPAsArray()
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * @param null $key
     * @param bool $default
     * @return bool
     */
    public function get($key = null, $default = false)
    {
        return $key === null ? $_GET : (isset($_GET[$key]) ? $_GET[$key] : $default);
    }

    /**
     * @param null $key
     * @param bool $default
     * @return bool
     */
    public function post($key = null, $default = false)
    {
        return $key === null ? $_POST : (isset($_POST[$key]) ? $_POST[$key] : $default);
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        if (isset($_POST) && count($_POST) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        if (isset($_GET) && count($_GET) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param null $key
     * @param bool $default
     * @return array|bool
     */
    public function gp($key = null, $default = false)
    {
        $gp = array_merge($_GET, $_POST);
        return $key === null ? $gp : (isset($gp[$key]) ? $gp[$key] : $default);
    }

    /**
     * @param $key
     * @param bool $default
     * @return bool
     */
    public function cookie($key, $default = false)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * @param $key
     * @param bool $default
     * @return bool
     */
    public function server($key, $default = false)
    {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    /**
     * @param null $key
     * @param bool $default
     * @return bool
     */
    public function files($key = null, $default = false)
    {
        return $key ? (isset($_FILES[$key]) ? $_FILES[$key] : $default) : $_FILES;
    }

    /**
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower(
                $_SERVER['HTTP_X_REQUESTED_WITH']
            ) == 'xmlhttprequest'
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param $url
     * @param null $params
     * @param string $verb
     * @param string $format
     * @return bool|mixed|\SimpleXMLElement|string
     * @throws \Exception
     */
    public function send($url, $params = null, $verb = 'POST', $format = 'json')
    {
        $cparams = [
            'http' => [
                'method' => $verb,
                'ignore_errors' => true
            ]
        ];
        if ($params !== null) {
            $params = http_build_query($params);
            if ($verb == 'POST') {
                $cparams['http']['content'] = $params;
                $cparams['http']['header'] = "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-length: " .
                    strlen(
                        $params
                    ) .
                    "\r\n";
            } else {
                $url .= '?' . $params;
            }
        }

        $context = stream_context_create($cparams);
        $fp = fopen($url, 'rb', false, $context);
        if (!$fp) {
            $res = false;
        } else {
            $res = stream_get_contents($fp);
        }

        if ($res === false) {
            throw new \Exception("$verb $url failed: $php_errormsg");
        }

        switch ($format) {
            case 'json':
                $r = json_decode($res);
                if ($r === null) {
                    return false;
                }
                return $r;

            case 'xml':
                $r = simplexml_load_string($res);
                if ($r === null) {
                    return false;
                }
                return $r;
        }
        return $res;
    }
}
