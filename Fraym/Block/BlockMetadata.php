<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Fraym\Block;

/**
 * This class for retrieving special information about a block.
 * Some blocks creating virutal URI's, this class will provide the information for it.
 * For example, a search extension will receive the virutal URI's from an extension to indexing the page.
 *
 * Class BlockMetadata
 * @package Fraym\Block
 */
class BlockMetadata
{
    private $uris = [];

    /**
     * @param $uri
     */
    public function addURI($uri)
    {
        $this->uris[$uri] = $uri;
    }

    /**
     * @return array
     */
    public function getURI()
    {
        return $this->uris;
    }
}
