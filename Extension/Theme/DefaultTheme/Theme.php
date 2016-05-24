<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\Theme\DefaultTheme;

use Fraym\Annotation\Registry;

/**
 * @package Extension\Theme\DefaultTheme
 * @Registry(
 * name="Fraym Default Theme",
 * description="Default Theme for Fraym",
 * version="1.0.1",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * repositoryKey="FRAYM_EXT_DEFAULT_THEME",
 * files={
 *      "Extension/Theme/DefaultTheme/",
 *      "Template/Default/Extension/Theme/DefaultTheme/",
 *      "Public/js/default_theme/",
 *      "Public/css/default_theme/",
 *      "Public/images/default_theme/"
 * },
 * entity={
 *      "\Fraym\Template\Entity\Template"={
 *          {
 *           "name"="Fraym Default Theme",
 *           "filePath"="Template/Default/Extension/Theme/DefaultTheme/Index.tpl"
 *           }
 *      }
 *    }
 * )
 * @Injectable(lazy=true)
 */
class Theme
{
}
