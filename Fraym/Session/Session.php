<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Session;

/**
 * Class Session
 * @package Fraym\Session
 */
class Session
{
    /**
     * @var array
     */
    private $onDestroy = [];

    /**
     * @var null
     */
    private $savePath = null;

    /**
     * @var null
     */
    private $sessionName = null;

    public function __construct()
    {
        $this->name('fraym_s');

        session_set_save_handler(
            [$this, "handlerOpen"],
            [$this, "handlerClose"],
            [$this, "handlerRead"],
            [$this, "handlerWrite"],
            [$this, "handlerDestroy"],
            [$this, "handlerGc"]
        );
        return $this;
    }

    /**
     * @param $savePath
     * @param $sessionName
     * @return bool
     */
    public function handlerOpen($savePath, $sessionName)
    {
        $this->savePath = $savePath;
        $this->sessionName = $sessionName;
        return true;
    }

    /**
     * @return bool
     */
    public function handlerClose()
    {
        return true;
    }

    /**
     * @param $id
     * @return string
     */
    public function handlerRead($id)
    {
        $sess_file = $this->savePath . DIRECTORY_SEPARATOR . "sess_$id";
        return (string)@file_get_contents($sess_file);
    }

    /**
     * @param $id
     * @param $data
     * @return bool|int
     */
    public function handlerWrite($id, $data)
    {
        $sess_file = $this->savePath . DIRECTORY_SEPARATOR . "sess_$id";
        if ($fp = @fopen($sess_file, "w")) {
            $return = fwrite($fp, $data);
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function handlerDestroy($id)
    {
        foreach ($this->onDestroy as $callback) {
            call_user_func_array($callback, [$this]);
        }
        $sess_file = $this->savePath . DIRECTORY_SEPARATOR . "sess_$id";
        return (@unlink($sess_file));
    }

    /**
     * @param $maxlifetime
     * @return bool
     */
    public function handlerGc($maxlifetime)
    {
        foreach ($this->onDestroy as $callback) {
            call_user_func_array($callback, [$this]);
        }
        $path = $this->savePath;
        foreach (glob("$path/sess_*") as $filename) {
            if ((filemtime($filename) + $maxlifetime) < time()) {
                @unlink($filename);
            }
        }
        return true;
    }

    /**
     * @param $lifetime
     * @param null $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httponly
     * @return $this
     */
    public function setCookieParams($lifetime, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        } else {
            setcookie($this->name(), $this->id(), $lifetime, $path, $domain, $secure, $httponly);
        }
        return $this;
    }

    /**
     * @param null $sessionName
     * @return string
     */
    public function name($sessionName = null)
    {
        return session_name($sessionName);
    }

    /**
     * @return $this
     */
    public function start()
    {
        $session_id = session_id();
        if (empty($session_id)) {
            // Start the session
            session_start();
        }
        return $this;
    }

    /**
     * @param $name
     * @param bool $default
     * @param bool $delete
     * @return bool
     */
    public function get($name, $default = false, $delete = false)
    {
        $str = isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
        if ($delete) {
            self::delete($name);
        }
        return $str;
    }

    /**
     * @param $name
     * @param bool $value
     * @return $this
     */
    public function set($name, $value = false)
    {
        $_SESSION[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function delete($name)
    {
        unset($_SESSION[$name]);
        return $this;
    }

    /**
     * @param bool $id
     * @return bool|string
     */
    public function id($id = false)
    {
        if ($id) {
            session_id($id);
        } else {
            return session_id();
        }
        return $id;
    }

    /**
     * @return bool
     */
    public function destroy()
    {
        return session_destroy();
    }

    /**
     * @param bool $delOldSession
     * @return bool
     */
    public function regenerate($delOldSession = false)
    {
        return session_regenerate_id($delOldSession);
    }

    /**
     * @param $callback
     * @return $this
     */
    public function addOnDestroyCallback($callback)
    {
        $this->onDestroy[] = $callback;
        return $this;
    }
}
