<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * Class BlockXmlDom
 * @package Fraym\Block
 */
class BlockXmlDom extends \DomDocument
{
    /**
     * @return string
     */
    public function __toString()
    {
        $xmlString = $this->saveXML();
        return substr($xmlString, strpos($xmlString, "\n"));
    }
}
