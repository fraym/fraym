<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\LanguageMenu;

use \Fraym\Block\BlockXml as BlockXml;
use Fraym\Annotation\Registry;

/**
 * @package Extension\LanguageMenu
 * @Registry(
 * name="Language Menu",
 * description="Create a language menu.",
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * repositoryKey="FRAYM_EXT_LANGMENU",
 * entity={
 *      "\Fraym\Block\Entity\Extension"={
 *          {
 *           "name"="Language Menu",
 *           "description"="Create a language menu.",
 *           "class"="\Extension\LanguageMenu\LanguageMenu",
 *           "execMethod"="execBlock"
 *           },
 *      }
 * },
 * files={
 *      "Extension/LanguageMenu/",
 *      "Template/Default/Extension/LanguageMenu/"
 * }
 * )
 * @Injectable(lazy=true)
 */
class LanguageMenu
{
    /**
     * @Inject
     * @var \Extension\LanguageMenu\LanguageMenuController
     */
    protected $languageMenuController;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Locale\Locale
     */
    protected $locale;

    /**
     * @Inject
     * @var \Fraym\Route\Route
     */
    protected $route;

    /**
     * @param $xml
     * @return mixed
     */
    public function execBlock($xml)
    {
        $languageMenu = [];
        $currentLocale = $this->locale->getLocale()->id;
        $defaultLocale = $this->locale->getDefaultLocale();
        $currentMenuItem = $this->route->getCurrentMenuItem();
        $locales = $this->db->getRepository('\Fraym\Locale\Entity\Locale')->findAll();

        foreach ($currentMenuItem->translations as $translation) {
            $url = $translation->url === '' ? '/' : $translation->url;
            $languageMenu[$translation->locale->id] = [
                'url' => $url,
                'active' => $translation->locale->id === $currentLocale,
                'name' => $translation->locale->name
            ];
        }

        foreach ($locales as $locale) {
            if (!isset($languageMenu[$locale->id])) {
                $menuItem = $currentMenuItem;
                do {
                    $m = $menuItem->parent;
                    if ($m) {
                        $menuItem = $m;
                    }
                } while ($m);

                foreach ($menuItem->translations as $mTranslation) {
                    if ($mTranslation->locale->id === $locale->id) {
                        $url = $mTranslation->url;
                        break;
                    } elseif ($mTranslation->locale->id === $defaultLocale->id) {
                        $url = $mTranslation->url;
                    }
                }

                $languageMenu[$locale->id] = [
                    'url' => $url === '' ? '/' : $url,
                    'active' => $locale->id === $currentLocale,
                    'name' => $locale->name
                ];
            }
        }
        $this->languageMenuController->renderHtml($languageMenu);
    }
}
