<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\Breadcrumb;

use Fraym\Annotation\Registry;

/**
 * @package Extension\Breadcrumb
 * @Registry(
 * name="Breadcrumb",
 * description="Create a simple breadcrumb navigation.",
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://fraym.org",
 * repositoryKey="FRAYM_EXT_BREADCRUMB",
 * entity={
 *      "\Fraym\Block\Entity\Extension"={
 *          {
 *           "name"="Breadcrumb",
 *           "description"="Create a simple breadcrumb navigation.",
 *           "class"="\Extension\Breadcrumb\BreadcrumbController",
 *           "execMethod"="execBlock"
 *           },
 *      }
 * },
 * files={
 *      "Extension/Breadcrumb/",
 *      "Template/Default/Extension/Breadcrumb/"
 * }
 * )
 * @Injectable(lazy=true)
 */
class BreadcrumbController extends \Fraym\Core
{
    /**
     * @param $xml
     */
    public function execBlock($xml)
    {
        $menuItem = $this->route->getCurrentMenuItem();
        if ($menuItem) {
            $menuItems = [$menuItem->getCurrentTranslation()];
            do {
                $menuItem = $menuItem->parent;
                if ($menuItem) {
                    $menuItems[] = $menuItem->getCurrentTranslation();
                }
            } while ($menuItem);
            $menuItems = array_reverse($menuItems);
            $this->view->assign('menuItems', $menuItems);
            $this->view->assign('currentMenuItem', $menuItem);
            $this->view->setTemplate('BreadcrumbNav');
        }
    }
}
