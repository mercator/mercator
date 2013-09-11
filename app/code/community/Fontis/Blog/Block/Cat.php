<?php
/**
 * Fontis Blog Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Parts of this software are derived from code originally developed by
 * Robert Chambers <magento@robertchambers.co.uk>
 * and released as "Lazzymonk's Blog" 0.5.8 in 2009.
 *
 * @category   Fontis
 * @package    Fontis_Blog
 * @copyright  Copyright (c) 2013 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Blog_Block_Cat extends Fontis_Blog_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_cat";

    protected function _prepareLayout()
    {
        $cat = $this->getCat();

        // Show breadcrumbs
        if (Mage::getStoreConfig("fontis_blog/blog/blogcrumbs") && ($breadcrumbs = $this->getLayout()->getBlock("breadcrumbs"))) {
            $blogTitle = Mage::getStoreConfig("fontis_blog/blog/title");
            $breadcrumbs->addCrumb("home", array(
                "label" => $this->getBlogHelper()->__("Home"),
                "title" => $this->getBlogHelper()->__("Go to Home Page"),
                "link"  => Mage::getBaseUrl()
            ));
            $breadcrumbs->addCrumb("blog", array(
                "label" => $blogTitle,
                "title" => $this->getBlogHelper()->__("Return to") . " $blogTitle",
                "link"  => $this->getUrl($this->getBlogHelper()->getBlogRoute())
            ));
            $breadcrumbs->addCrumb("blog_page", array(
                "label" => $cat->getTitle(),
                "title" => $cat->getTitle()
            ));
        }

        if ($head = $this->getLayout()->getBlock("head")) {
            $head->setTitle($cat->getTitle());
            $head->setKeywords($cat->getMetaKeywords());
            $head->setDescription($cat->getMetaDescription());
        }

        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG . "_" . $cat->getId(), Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
    }

    public function getPosts()
    {
        $cat = $this->getCat();
        if ($cat->getCatId() == null) {
            return false;
        }

        $page = (int) $this->getRequest()->getParam("page");
        $posts = Mage::getModel("blog/post")->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addCatFilter($cat->getCatId())
            ->setOrder("created_time", "desc");
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($posts);

        $posts->setPageSize((int) Mage::getStoreConfig("fontis_blog/blog/perpage"));
        $posts->setCurPage($page);

        foreach ($posts as $post) {
            $this->processPost($post, true);
        }
        $this->setData("cat", $posts);
        return $this->getData("cat");
    }

    public function getCat()
    {
        return Mage::getSingleton("blog/cat");
    }

    public function getPages()
    {
        if ($perPage = (int) Mage::getStoreConfig("fontis_blog/blog/perpage")) {
            $collection = Mage::getModel("blog/post")->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder("created_time ", "desc");
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);

            $cats = Mage::getSingleton("blog/cat");
            Mage::getSingleton("blog/status")->addCatFilterToCollection($collection, $cats->getCatId());

            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array(new Zend_Db_Expr("count(main_table.post_id) as postcount")));
            $collection->load();

            $currentPage = (int) $this->getRequest()->getParam("page");
            $cat = $this->getRequest()->getParam("identifier");

            if (!$currentPage) {
                $currentPage = 1;
            }

            $route = $this->getBlogHelper()->getBlogRoute();
            $pages = ceil($collection->getFirstItem()->getPostcount() / $perPage);
            $links = "";
            unset($collection);

            if ($currentPage > 1) {
                $links .= '<div class="left"><a href="' . $this->getUrl($route . "/cat") . $cat . "/page/" .($currentPage - 1) . '">Newer Posts</a></div>';
            }
            if ($currentPage < $pages) {
                $links .= '<div class="right"><a href="' . $this->getUrl($route . "/cat") . $cat . "/page/" . ($currentPage + 1) . '">Older Posts</a></div>';
            }
            echo $links;
        }
    }
}
