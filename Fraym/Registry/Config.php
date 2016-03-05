<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Registry;

/**
 * Class Config
 * @package Fraym\Registry
 * @Injectable(lazy=true)
 */
class Config
{
    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @param $key
     * @return Entity\Config
     */
    public function get($key)
    {
        $obj = null;
        if ($this->db->getEntityManager()) {
            $obj = $this->db->getRepository('\Fraym\Registry\Entity\Config')->findOneByName(strtoupper($key));
        }
        return $obj ? : new \Fraym\Registry\Entity\Config;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    public function set($key, $val)
    {
        $obj = null;
        if ($this->db->getEntityManager()) {
            $obj = $this->db->getRepository('\Fraym\Registry\Entity\Config')->findOneByName(strtoupper($key));
            if ($obj === null) {
                $obj = new \Fraym\Registry\Entity\Config;
                $obj->name = strtoupper($key);
                $this->db->persist($obj);
            }
            $obj->value = $val;
            $this->db->flush();
        }
        return $this;
    }
}
