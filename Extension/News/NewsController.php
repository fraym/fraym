<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\News;

use \DI\Annotation\Inject;

/**
 * Class NewsController
 * @package Extension\News
 * @Injectable(lazy=true)
 */
class NewsController extends \Fraym\Core
{
    /**
     * @var mixed
     */
    private $newsItem = null;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @param null $blockConfig
     */
    public function getBlockConfig($blockConfig = null)
    {
        $newsListItems = $this->db->getRepository('\Extension\News\Entity\News')->findAll();

        $selectedNewsItems = isset($blockConfig->listItems) ? explode(',', $blockConfig->listItems) : array();

        $this->view->assign('selectedNewsItems', $selectedNewsItems);
        $this->view->assign('blockConfig', $blockConfig);
        $this->view->assign('newsListItems', $newsListItems);
        $this->view->render('NewsConfig');
    }

    /**
     * @param $listPageUrl
     * @param $newsItem
     */
    public function renderNews($listPageUrl, $newsItem)
    {
        if ($newsItem) {
            $this->newsItem = $newsItem;
            $this->view->setPageTitle($newsItem->title);
            $route = $this->route;

            $this->view->assign(
                'getNewsListTagUrl',
                function ($tag) use ($route, $listPageUrl) {
                    return rtrim($listPageUrl, '/') . '/t/' . urlencode($tag->name);
                }
            );
        }

        $this->view->addOutputFilter(array($this, 'addMetaTags'));
        $this->view->assign('newsItem', $newsItem);
        $this->view->setTemplate('NewsDetail');
    }

    /**
     * @param $source
     * @return mixed
     */
    public function addMetaTags($source)
    {
        $replacePropertyTags = array(
            'og:url',
            'og:image',
            'og:description',
            'og:title',
        );
        $replaceItempropTags = array(
            'name',
            'description',
            'image',
        );

        $source = preg_replace_callback('#<meta\s.*\/>#im', function ($match) use ($replacePropertyTags, $replaceItempropTags) {
            $xml = simplexml_load_string($match[0]);
            if ($xml !== false) {
                if (isset($xml->attributes()->property) && in_array($xml->attributes()->property, $replacePropertyTags)) {
                    return '';
                } elseif (isset($xml->attributes()->itemprop) && in_array($xml->attributes()->itemprop, $replaceItempropTags)) {
                    return '';
                }
            }
        }, $source);

        $url = $this->route->getRequestRoute(false, false, true);
        $image = $this->route->getHostnameWithBasePath() . '/' . substr($this->newsItem->image, 7);
        $title = $this->newsItem->title;
        $desc = str_replace(array("\n", "\r\n", "\r"), '', strip_tags($this->newsItem->shortDescription));

        $source = str_ireplace('</head>', "<meta property='og:url' content='{$url}' />
        <meta property='og:image' content='{$image}' />
        <meta property='og:description' content='{$desc}' />
        <meta property='og:title' content='{$title}' />
        <meta property='og:site_name' content='{$url}' />
        <meta property='og:type' content='blog'/>
        <meta itemprop='name' content='{$title}' />
        <meta itemprop='description' content='{$desc}' />
        <meta itemprop='image' content='{$image}' /></head>", $source);

        return $source;
    }

    /**
     * @param $listPageUrl
     * @param $newsItem
     */
    public function renderNewsItemCategories($listPageUrl, $newsItem)
    {
        $route = $this->route;
        $this->view->assign(
            'getNewsListCategoryUrl',
            function ($category) use ($route, $listPageUrl) {
                return rtrim($listPageUrl, '/') . '/c/' . urlencode($category->name);
            }
        );
        $this->view->assign('newsItem', $newsItem);
        $this->view->setTemplate('NewsItemCategories');
    }

    /**
     * @param $listPageUrl
     * @param $newsItem
     */
    public function renderNewsItemTags($listPageUrl, $newsItem)
    {
        $route = $this->route;
        $this->view->assign(
            'getNewsListTagUrl',
            function ($tag) use ($route, $listPageUrl) {
                return rtrim($listPageUrl, '/') . '/t/' . urlencode($tag->name);
            }
        );
        $this->view->assign('newsItem', $newsItem);
        $this->view->setTemplate('NewsItemTags');
    }

    /**
     * @param $listPageUrl
     * @param $categories
     */
    public function renderNewsCategories($listPageUrl, $categories)
    {
        $route = $this->route;
        $this->view->assign(
            'getNewsListCategoryUrl',
            function ($category) use ($route, $listPageUrl) {
                return rtrim($listPageUrl, '/') . '/c/' . urlencode($category->name);
            }
        );

        $this->view->assign('categories', $categories);
        $this->view->setTemplate('NewsCategories');
    }

    /**
     * @param $listPageUrl
     * @param $tags
     */
    public function renderNewsTags($listPageUrl, $tags)
    {
        $route = $this->route;
        $this->view->assign(
            'getNewsListTagUrl',
            function ($tag) use ($route, $listPageUrl) {
                return rtrim($listPageUrl, '/') . '/t/' . urlencode($tag->name);
            }
        );
        $this->view->assign('tags', $tags);
        $this->view->setTemplate('NewsTags');
    }

    /**
     * @param $detailPageUrl
     * @param $newsItems
     * @param $pagination
     */
    public function renderNewsList($detailPageUrl, $newsItems, $pagination)
    {
        $route = $this->route;
        $this->view->assign(
            'getNewsItemUrl',
            function ($newsItem) use ($route, $detailPageUrl) {
                return rtrim($detailPageUrl, '/') . '/' . $route->createSlug($newsItem->title) . '-' . $newsItem->id;
            }
        );
        $listPageUrl = $this->route->getCurrentMenuItem()->getUrl($this->route, true);
        $route = $this->route;
        $this->view->assign(
            'getNewsListTagUrl',
            function ($tag) use ($route, $listPageUrl) {
                return rtrim($listPageUrl, '/') . '/t/' . urlencode($tag->name);
            }
        );

        if ($pagination['currentPage'] > 1) {
            $this->view->assign(
                'prevPage',
                rtrim($listPageUrl, '/') . '/page/' . ($pagination['currentPage'] - 1)
            );
        }

        if (($pagination['currentPage'] * $pagination['itemsPerPage']) < $pagination['count']) {
            $this->view->assign(
                'nextPage',
                rtrim($listPageUrl, '/') . '/page/' . ($pagination['currentPage'] + 1)
            );
        }
        $this->view->assign('newsItems', $newsItems);
        $this->view->setTemplate('NewsList');
    }
}
