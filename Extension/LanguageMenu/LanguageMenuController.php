<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\LanguageMenu;

/**
 * Class LanguageMenuontroller
 * @package Extension\LanguageMenu
 * @Injectable(lazy=true)
 */
class LanguageMenuController extends \Fraym\Core
{
    /**
     * Render template
     */
    public function renderHtml($languageMenu)
    {
        $this->view->assign('languageMenu', $languageMenu);
        $this->view->setTemplate('Block');
    }
}
