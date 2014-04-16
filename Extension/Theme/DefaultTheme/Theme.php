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
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * repositoryKey="FRAYM_EXT_DEFAULT_THEME",
 * files={
 *      "Extension/Theme/Default/*",
 *      "Extension/Theme/Default/",
 *      "Template/Default/Extension/Theme/Default/*",
 *      "Template/Default/Extension/Theme/Default/",
 *      "Public/js/default_theme/*",
 *      "Public/js/default_theme/",
 *      "Public/css/default_theme/*",
 *      "Public/css/default_theme/",
 *      "Public/images/default_theme/*",
 *      "Public/images/default_theme/"
 * },
 * entity={
 *      "\Fraym\Template\Entity\Template"={
 *          {
 *           "name"="Fraym Default Theme",
 *           "filePath"="Template/Default/Extension/Theme/DefaultTheme/Index.tpl"
 *           }
 *      }
 *    },
 * afterRegister="install"
 * )
 * @Injectable(lazy=true)
 */
class Theme
{

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
     * @var \Fraym\Translation\Translation
     */
    protected $translation;

    private $site = null;

    public function install()
    {
        $user = $this->db->getRepository('\Fraym\User\Entity\User')->findOneById(1);
        $this->site = $this->db->getRepository('\Fraym\Site\Entity\Site')->findOneById(1);

        /**
         * Footer Partial
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Block\Block'
        );
        $config = '<active>1</active><cache>1</cache><template type="string"><![CDATA[Copyright &copy; Company {date(\'Y\')}]]></template>';
        $this->createBlock('footer', null, $extension, $config);

        /**
         * Home Partial
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Block\Block'
        );
        $config = '<active>1</active><cache>1</cache><template type="file"><![CDATA[Template/Default/Extension/Theme/DefaultTheme/Home.tpl]]></template>';
        $this->createBlock('main-wrapper', 1, $extension, $config);

        /**
         * 404
         */
        $this->createBlock('main-wrapper', 2, $extension, $config);

        /**
         * HTML / Text 404
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\HtmlEditor\HtmlEditor'
        );
        $config = '<html locale="1"><![CDATA[<h1>404 Page not found</h1>]]></html><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('home-content-1', 2, $extension, $config);

        /**
         * HTML / 404 Lorem text
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\HtmlEditor\HtmlEditor'
        );
        $config = '<html locale="1"><![CDATA[<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi commodo, ipsum sed pharetra gravida, orci magna rhoncus neque, id pulvinar odio lorem non turpis. Nullam sit amet enim. Suspendisse id velit vitae ligula volutpat condimentum. Aliquam erat volutpat. Sed quis velit.</p><p>Vivamus pharetra posuere sapien. Nam consectetuer. Sed aliquam, nunc eget euismod ullamcorper, lectus nunc ullamcorper orci, fermentum bibendum enim nibh eget ipsum.</p>]]></html><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('home-content-2', 2, $extension, $config);

        /**
         * Blog Partial
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Block\Block'
        );
        $config = '<active>1</active><cache>1</cache><locale/><template type="file"><![CDATA[Template/Default/Extension/Theme/DefaultTheme/News.tpl]]></template>';
        $this->createBlock('main-wrapper', 3, $extension, $config);

        /**
         * Menu Header
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Menu\MenuController'
        );
        $config = '<active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('nav', null, $extension, $config);

        /**
         * Menu Footer
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Menu\MenuController'
        );
        $config = '<active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('footer-menu-right', null, $extension, $config);

        /**
         * FlexSlider
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\FlexSlider\FlexSlider'
        );
        $config = '<sliderConfig><numberOfSlides>3</numberOfSlides><animationLoop>true</animationLoop><slideshow>true</slideshow><useCSS>true</useCSS><controlNav>true</controlNav><directionNav>true</directionNav><animation>fade</animation><direction>horizontal</direction><startAt>0</startAt><slideshowSpeed>7000</slideshowSpeed><animationSpeed>600</animationSpeed><initDelay>0</initDelay><prevText/><nextText/><playText/><minItems>0</minItems><maxItems>0</maxItems><move>0</move><itemWidth>0</itemWidth><itemMargin>0</itemMargin></sliderConfig><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('home-content-1', 1, $extension, $config);

        /**
         * FlexSlider Image
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Image\Image'
        );
        $config = '<image_file>Public/images/default_theme/slider/item_5.jpg</image_file><image_auto_size>1</image_auto_size><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('flexslider-9-1', 1, $extension, $config);

        /**
         * FlexSlider Image
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Image\Image'
        );
        $config = '<image_file>Public/images/default_theme/slider/item_4.jpg</image_file><image_auto_size>1</image_auto_size><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('flexslider-9-2', 1, $extension, $config);

        /**
         * FlexSlider Image
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Fraym\Image\Image'
        );
        $config = '<image_file>Public/images/default_theme/slider/item_2.jpg</image_file><image_auto_size>1</image_auto_size><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('flexslider-9-3', 1, $extension, $config);

        /**
         * HTML / Text
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\HtmlEditor\HtmlEditor'
        );
        $config = '<html locale="1"><![CDATA[<h2>Web design, Web Development, Graphic Design</h2><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi commodo, ipsum sed pharetra gravida, orci magna rhoncus neque, id pulvinar odio lorem non turpis. Nullam sit amet enim. Suspendisse id velit vitae ligula volutpat condimentum. Aliquam erat volutpat. Sed quis velit.</p><p>Vivamus pharetra posuere sapien. Nam consectetuer. Sed aliquam, nunc eget euismod ullamcorper, lectus nunc ullamcorper orci, fermentum bibendum enim nibh eget ipsum.</p>]]></html><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('home-content-2', 1, $extension, $config);

        /**
         * News Category
         */
        $newsCategory = new \Extension\News\Entity\Category();
        $newsCategory->name = 'Lorem';

        /**
         * News Items
         */
        $news = new \Extension\News\Entity\News();
        $news->title = 'Welcome to my website';
        $news->subtitle = 'Powered by Fraym CMS';
        $news->shortDescription = '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed ...</p>';
        $news->description = '<p>Lorem #ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. #Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>';
        $news->author = $this->db->getRepository('\Fraym\User\Entity\User')->findOneById(1);
        $news->categories->add($newsCategory);
        $news->author = $user;
        $news->image = 'Public/images/default_theme/slider/item_1.jpg';
        $news->date = new \DateTime();
        $this->db->persist($news);

        $news = new \Extension\News\Entity\News();
        $news->title = 'Lorem ipsum dolor sit amet';
        $news->subtitle = 'Lorem ipsum dolor sit amet';
        $news->shortDescription = '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed ...</p>';
        $news->description = '<p>Lorem #ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. #Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>';
        $news->author = $this->db->getRepository('\Fraym\User\Entity\User')->findOneById(1);
        $news->author = $user;
        $news->image = 'Public/images/default_theme/slider/item_2.jpg';
        $news->date = (new \DateTime())->modify('-1 week');
        $this->db->persist($news);

        $news = new \Extension\News\Entity\News();
        $news->title = 'Lorem ipsum dolor sit amet';
        $news->subtitle = 'Lorem ipsum dolor sit amet';
        $news->shortDescription = '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed ...</p>';
        $news->description = '<p>Lorem sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>';
        $news->author = $this->db->getRepository('\Fraym\User\Entity\User')->findOneById(1);
        $news->image = 'Public/images/default_theme/slider/item_3.jpg';
        $news->date = (new \DateTime())->modify('-3 week');
        $news->author = $user;
        $this->db->persist($news);

        /**
         * News Liste Home -> 3 News Items
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\News\News'
        );
        $config = '<view><![CDATA[list]]></view><checkRoute><![CDATA[newsListRouteCheck]]></checkRoute><forceShowOnDetail>0</forceShowOnDetail><listItems sort="date_desc"><![CDATA[1,2,3]]></listItems><detailPage>3</detailPage><active>1</active><cache>1</cache><template type="file"><![CDATA[Template/Default/Extension/Theme/DefaultTheme/HomeNewsList.tpl]]></template>';
        $this->createBlock('home-content-1', 1, $extension, $config);

        /**
         * News Overview -> list all news
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\News\News'
        );
        $config = '<view><![CDATA[list]]></view><checkRoute><![CDATA[newsListRouteCheck]]></checkRoute><forceShowOnDetail>0</forceShowOnDetail><listItems sort="date_desc"/><active>1</active><cache>1</cache><detailPage>3</detailPage><template type="file"><![CDATA[Template/Default/Extension/Theme/DefaultTheme/NewsList.tpl]]></template>';
        $this->createBlock('news-list', 3, $extension, $config);

        /**
         * News Detail
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\News\News'
        );
        $config = '<view><![CDATA[detail]]></view><checkRoute><![CDATA[newsRouteCheck]]></checkRoute><listPage>3</listPage><active>1</active><cache>1</cache><template type="file"><![CDATA[Template/Default/Extension/Theme/DefaultTheme/NewsDetail.tpl]]></template>';
        $this->createBlock('news-list', 3, $extension, $config);

        /**
         * News item categories on detail page
         */
        $extension = $this->db->getRepository('\Fraym\Block\Entity\BlockExtension')->findOneByClass(
            '\Extension\News\News'
        );
        $config = '<view><![CDATA[detail-category]]></view><checkRoute><![CDATA[newsRouteCheck]]></checkRoute><listPage>3</listPage><active>1</active><cache>1</cache><template type="file"><![CDATA[]]></template>';
        $this->createBlock('news-list-side-a', 3, $extension, $config);

        $this->db->flush();
    }

    private function createBlock($contentId, $menuItemId, $extension, $config) {
        $block = new \Fraym\Block\Entity\Block();
        $block->extension = $extension;
        $block->contentId = $contentId;
        $block->menuItem = $this->db->getRepository('\Fraym\Menu\Entity\MenuItem')->findOneById($menuItemId);
        $block->site = $this->site;
        $block->config = $config;
        $this->db->persist($block);
        $this->db->flush();
    }
}
