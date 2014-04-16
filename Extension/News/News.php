<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
namespace Extension\News;

use Fraym\Annotation\Registry;
use Fraym\Block\BlockMetadata;

/**
 * @package Extension\News
 * @Registry(
 * name="News",
 * description="Create news articles on your website.",
 * version="1.0.0",
 * author="Fraym.org",
 * website="http://www.fraym.org",
 * repositoryKey="FRAYM_EXT_NEWS",
 * entity={
 *      "\Fraym\Block\Entity\BlockExtension"={
 *          {
 *           "name"="News",
 *           "description"="Create news articles on your website.",
 *           "class"="\Extension\News\News",
 *           "configMethod"="getBlockConfig",
 *           "execMethod"="execBlock",
 *           "saveMethod"="saveBlockConfig",
 *           "metadataMethod"="getBlockMetadata"
 *           },
 *      },
 *      "\Fraym\EntityManager\Entity\Entity"={
 *          {
 *           "className"="\Extension\News\Entity\News",
 *           "name"="News entry",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="News"
 *                      }
 *                   },
 *           },
 *           {
 *           "className"="\Extension\News\Entity\Category",
 *           "name"="News category",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="News"
 *                      }
 *                   },
 *           },
 *           {
 *           "className"="\Extension\News\Entity\Tag",
 *           "name"="News tag",
 *           "group"={
 *                      "\Fraym\EntityManager\Entity\Group"={
 *                          "name"="News"
 *                      }
 *                   },
 *           },
 *       },
 * },
 * files={
 *      "Extension/News/*",
 *      "Extension/News/",
 *      "Template/Default/Extension/News/*",
 *      "Template/Default/Extension/News/"
 * }
 * )
 * @Injectable(lazy=true)
 */
class News
{
    /**
     * @Inject
     * @var \Extension\News\NewsController
     */
    protected $newsController;

    /**
     * @Inject
     * @var \Fraym\Route\Route
     */
    protected $route;

    /**
     * @Inject
     * @var \Fraym\Database\Database
     */
    protected $db;

    /**
     * @Inject
     * @var \Fraym\Block\BlockParser
     */
    protected $blockParser;

    /**
     * @Inject
     * @var \Fraym\Request\Request
     */
    public $request;

    /**
     * @Inject
     * @var \Fraym\Template\Template
     */
    protected $template;

    /**
     * @param $blockId
     * @param \Fraym\Block\BlockXML $blockXML
     * @return \Fraym\Block\BlockXML
     */
    public function saveBlockConfig($blockId, \Fraym\Block\BlockXML $blockXML)
    {
        $blockConfig = $this->request->getGPAsObject();
        $customProperties = new \Fraym\Block\BlockXMLDom();
        $element = $customProperties->createElement('view');
        $element->appendChild($customProperties->createCDATASection($blockConfig->newsView));
        $customProperties->appendChild($element);
        if ($blockConfig->newsView === 'detail' || $blockConfig->newsView === 'detail-category' || $blockConfig->newsView === 'detail-tag') {
            $element = $customProperties->createElement('checkRoute');
            $element->appendChild($customProperties->createCDATASection('newsRouteCheck'));
            $customProperties->appendChild($element);
            $element = $customProperties->createElement('listPage', intval($blockConfig->listPage));
            $customProperties->appendChild($element);
        } elseif ($blockConfig->newsView === 'list-category' || $blockConfig->newsView === 'list-tag') {
            $element = $customProperties->createElement('checkRoute');
            $element->appendChild($customProperties->createCDATASection('newsRouteCheck'));
            $customProperties->appendChild($element);
            $element = $customProperties->createElement('listPage', $this->route->getCurrentMenuItem()->id);
            $customProperties->appendChild($element);
        } elseif ($blockConfig->newsView === 'list') {
            $element = $customProperties->createElement('checkRoute');
            $element->appendChild($customProperties->createCDATASection('newsListRouteCheck'));
            $customProperties->appendChild($element);

            $element = $customProperties->createElement('limit');
            $element->nodeValue = $blockConfig->limit;
            $customProperties->appendChild($element);

            $forceShowOnDetail = property_exists(
                $blockConfig,
                'forceShowOnDetail'
            ) && $blockConfig->forceShowOnDetail == '1' ? 1 : 0;
            $element = $customProperties->createElement('forceShowOnDetail', $forceShowOnDetail);
            $customProperties->appendChild($element);
            if (isset($blockConfig->detailPage)) {
                $element = $customProperties->createElement('detailPage', intval($blockConfig->detailPage));
                $customProperties->appendChild($element);
            }

            $element = $customProperties->createElement('listItems');
            if (isset($blockConfig->newsListItems)) {
                $element->appendChild($customProperties->createCDATASection(implode(',', $blockConfig->newsListItems)));
            }

            $customProperties->appendChild($element);

            $element = $customProperties->createElement('newsListSort');
            $element->nodeValue = $blockConfig->newsListSort;
            $customProperties->appendChild($element);
        }
        $blockXML->setCustomProperty($customProperties);
        return $blockXML;
    }

    /**
     * @return bool
     */
    public function newsRouteCheck()
    {
        $newsItem = $this->getCurrentNewsItem();
        if ($newsItem) {
            $slugTitle = $this->route->createSlug($newsItem->title);
            $fullSlug = "$slugTitle-" . $newsItem->id;
            if (ltrim($this->route->getAddionalURI(), '/') === $fullSlug) {
                return true;
            } elseif (!empty($fullSlug)) {
                $this->route->redirectToURL($this->route->getCurrentMenuItem()->getUrl($this->route, true) . $fullSlug);
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    function newsListRouteCheck()
    {
        $filter = $this->getNewsListFilter();
        if ($filter) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function getNewsListFilter()
    {
        $slug = $this->route->getAddionalURI();
        $slug = array_reverse(explode('/', $slug));
        if (isset($slug[0]) && isset($slug[1])) {
            if ($slug[1] === 'c' || $slug[1] === 't' || $slug[1] === 'page') {
                return array($slug[1], urldecode($slug[0]));
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getCurrentNewsItem()
    {
        $slug = $this->route->getAddionalURI();
        $newsId = intval(substr($slug, strrpos($slug, '-') + 1));
        return $this->db->getRepository('\Extension\News\Entity\News')->findOneById($newsId);
    }

    /**
     * @param $xml
     */
    protected function newsList($xml)
    {
        $itemsPerPage = 10;
        $currentPage = 1;
        $entryIds = empty($xml->listItems) ? array() : explode(',', trim($xml->listItems));
        $sort = (string)$xml->newsListSort;
        $filter = $this->getNewsListFilter();

        $qb = $this->db->createQueryBuilder();

        $newsItems = $qb
            ->select("n")
            ->from('\Extension\News\Entity\News', 'n')
            ->leftJoin(
                'n.sites',
                's',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                $qb->expr()->eq('s.id', $this->route->getCurrentMenuItem()->site->id)
            );

        if (count($entryIds)) {
            $newsItems->andWhere("n.id IN (:ids)")
                ->setParameter('ids', $entryIds);
        }

        if ($sort == 'date_asc') {
            $newsItems->orderBy('n.date', 'ASC');
        } else {
            if ($sort == 'title_asc') {
                $newsItems->orderBy('n.title', 'ASC');
            } else {
                $newsItems->orderBy('n.date', 'DESC');
            }
        }

        if (is_numeric((string)$xml->limit)) {
            $itemsPerPage = (int)$xml->limit;
            $newsItems->setMaxResults($itemsPerPage);
        } else {
            $newsItems->setMaxResults($itemsPerPage);
        }

        if ($filter) {
            if ($filter[0] === 'c') {
                $newsItems->join('n.categories', 'c')
                    ->andWhere("c.name = :category")
                    ->setParameter('category', $filter[1]);
            } elseif ($filter[0] === 't') {
                $newsItems->join('n.tags', 't')
                    ->andWhere("t.name = :tag")
                    ->setParameter('tag', $filter[1]);
            } elseif ($filter[0] === 'page') {
                $currentPage = $filter[1];
                if ($currentPage == '1') {
                    $this->route->redirectToURL($this->route->getCurrentMenuItem()->getUrl($this->route, true));
                }
                $newsItems->setFirstResult(($currentPage * $itemsPerPage) - $itemsPerPage);
            }
        }

        $detailPageUrl = '';
        $detailPage = $this->db
            ->getRepository('\Fraym\Menu\Entity\MenuItem')
            ->findOneById($xml->detailPage);

        if ($detailPage) {
            $detailPageUrl = $detailPage->getUrl($this->route, true);
        }

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($newsItems, false);

        $c = count($paginator);
        $pagination = array('currentPage' => $currentPage, 'count' => $c, 'itemsPerPage' => $itemsPerPage);
        return $this->newsController->renderNewsList($detailPageUrl, $paginator, $pagination);
    }

    /**
     * @param $xml
     */
    protected function listFilter($xml)
    {
        $listPageUrl = '';
        $listPage = $this->db
            ->getRepository('\Fraym\Menu\Entity\MenuItem')
            ->findOneById($xml->listPage);

        if ($listPage) {
            $listPageUrl = $listPage->getUrl($this->route, true);
        }

        if ((string)$xml->view == 'list-category') {
            $categories = $this->db
                ->createQueryBuilder()
                ->select("c")
                ->from('\Extension\News\Entity\Category', 'c')
                ->join('c.news', 'n')
                ->leftJoin('n.sites', 's')
                ->where("s = :site OR n.sites IS EMPTY")
                ->orderBy("c.name", 'asc')
                ->setParameter('site', $this->route->getCurrentMenuItem()->site)
                ->getQuery()
                ->getResult();

            return $this->newsController->renderNewsCategories($listPageUrl, $categories);
        } elseif ((string)$xml->view == 'list-tag') {
            $tags = $this->db
                ->createQueryBuilder()
                ->select("t")
                ->from('\Extension\News\Entity\Tag', 't')
                ->join('t.news', 'n')
                ->leftJoin('n.sites', 's')
                ->where("s = :site OR n.sites IS EMPTY")
                ->orderBy("t.name", 'asc')
                ->setParameter('site', $this->route->getCurrentMenuItem()->site)
                ->getQuery()
                ->getResult();

            return $this->newsController->renderNewsTags($listPageUrl, $tags);
        }
    }

    /**
     * @param $xml
     * @param $currentNewsItem
     */
    protected function newsDetail($xml, $currentNewsItem)
    {
        $listPageUrl = '';
        $listPage = $this->db
            ->getRepository('\Fraym\Menu\Entity\MenuItem')
            ->findOneById($xml->listPage);

        if ($listPage) {
            $listPageUrl = $listPage->getUrl($this->route, true);
        }

        // Replace hashtags
        $currentNewsItem->description = preg_replace_callback(
            '/(^|\s)*#[A-Za-z0-9_]+/is',
            function ($found) use ($listPageUrl) {
                $found = str_ireplace('#', '', $found[0]);
                return '<a href="' . rtrim($listPageUrl, '/') . '/t/' . urlencode(
                    trim($found)
                ) . '">' . $found . '</a>';
            },
            $currentNewsItem->description
        );

        if ((string)$xml->view == 'detail-category') {
            return $this->newsController->renderNewsItemCategories($listPageUrl, $currentNewsItem);
        } elseif ((string)$xml->view == 'detail') {

            return $this->newsController->renderNews($listPageUrl, $currentNewsItem);
        } elseif ((string)$xml->view == 'detail-tag') {
            return $this->newsController->renderNewsItemTags($listPageUrl, $currentNewsItem);
        }
    }

    /**
     * @param $xml
     * @return mixed
     */
    public function execBlock($xml)
    {
        $currentNewsItem = $this->getCurrentNewsItem();
        if ((string)$xml->view === 'list' && (!$currentNewsItem || (string)$xml->forceShowOnDetail == '1')) {
            return $this->newsList($xml);
        }
        if (in_array((string)$xml->view, array('list-category', 'list-tag'))) {
            return $this->listFilter($xml);
        } elseif (in_array((string)$xml->view, array('detail-category', 'detail-tag', 'detail')) && $currentNewsItem) {
            return $this->newsDetail($xml, $currentNewsItem);
        }
        $this->template->setTemplate('string:');
    }

    /**
     * @param null $blockId
     */
    public function getBlockConfig($blockId = null)
    {
        $configXml = null;
        if ($blockId) {
            $block = $this->db->getRepository('\Fraym\Block\Entity\Block')->findOneById($blockId);
            $configXml = $this->blockParser->getXMLObjectFromString($this->blockParser->wrapBlockConfig($block));
        }
        $this->newsController->getBlockConfig($configXml);
    }

    /**
     * @param $news
     * @param $eventArgs
     * @param $event
     */
    public function setTags($news, $eventArgs, $event)
    {
        $em = $eventArgs->getEntityManager();
        $tags = array_merge($this->getTags($news->shortDescription), $this->getTags($news->description));
        $news->tags->clear();

        foreach ($tags as $tag) {
            $newTag = $this->db->getRepository('\Extension\News\Entity\Tag')->findOneByName($tag);

            if (!$newTag) {
                $newTag = new \Extension\News\Entity\Tag();
                $newTag->name = $tag;
                if ($event === 'onFlush') {
                    $newTag->news->add($news);
                    $news->tags->add($newTag);
                } else {
                    $em->persist($newTag);
                    $em->flush($newTag);
                }
            }

            if (!$news->tags->contains($newTag)) {
                $newTag->news->add($news);
                $news->tags->add($newTag);
            }
        }

        if ($event === 'onFlush') {
            $cm = $em->getClassMetadata(get_class($news));
            $em->getUnitOfWork()->computeChangeSet($cm, $news);
        }
    }

    /**
     * @param $text
     * @return array
     */
    protected function getTags($text)
    {
        $text = strip_tags(html_entity_decode($text));
        if (preg_match_all('/(^|\s)*#[A-Za-z0-9_]+/is', $text, $matches)) {
            return array_map(
                function ($var) {
                    return ltrim(trim($var), '#');
                },
                $matches[0]
            );
        }
        return array();
    }

    /**
     * @param $menuItemTranslation
     * @return BlockMetadata
     */
    public function getBlockMetadata(\Fraym\Menu\Entity\MenuItemTranslation $menuItemTranslation)
    {
        $metaData = new BlockMetadata();
        // TODO: Get news uris and add it to the block meta data object
        return $metaData;
    }
}
