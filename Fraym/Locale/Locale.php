<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Locale;

/**
 * Class Locale
 * @package Fraym\Locale
 * @Injectable(lazy=true)
 */
class Locale
{
    /**
     * @var null
     */
    private $locale = null;

    /**
     * @Inject
     * @var \Fraym\ServiceLocator\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    protected $request;

    /**
     * @Inject
     * @var \Fraym\Session\Session
     */
    protected $session;

    /**
     * @Inject
     * @var \Fraym\User\User
     */
    protected $user;

    /**
     * @return mixed
     */
    public function getDefaultLocale()
    {
        $defaultLocale = $this->db->getRepository('\Fraym\Locale\Entity\Locale')->findOneBy(['default' => 1]);
        return $defaultLocale;
    }

    /**
     * @return mixed
     */
    public function getLocales()
    {
        return $this->db->getRepository('\Fraym\Locale\Entity\Locale')->findAll();
    }

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        if (is_object($locale) === false) {
            $locale = $this->db->getRepository('\Fraym\Locale\Entity\Locale')->findOneById($locale);
        }
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale ? : $this->getDefaultLocale();
    }

    /**
     * @param $date
     * @return string
     */
    public function formatDate($date = null)
    {
        if ($date === null) {
            return strftime($this->getLocale()->dateFormat, time());
        } elseif (is_string($date)) {
            return strftime($this->getLocale()->dateFormat, strtotime($date));
        }
        return strftime($this->getLocale()->dateFormat, $date->getTimestamp());
    }

    /**
     * @param $date
     * @return string
     */
    public function formatDateTime($date = null)
    {
        if ($date === null) {
            return strftime($this->getLocale()->dateTimeFormat, time());
        } elseif (is_string($date)) {
            return strftime($this->getLocale()->dateTimeFormat, strtotime($date));
        }
        return strftime($this->getLocale()->dateTimeFormat, $date->getTimestamp());
    }
}
