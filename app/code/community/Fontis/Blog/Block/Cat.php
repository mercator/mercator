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
    public function getPosts()
    {
        $cats = Mage::getSingleton("blog/cat");
        if ($cats->getCatId() == null) {
            return false;
        }
        $page = (int) $this->getRequest()->getParam("page");
        $posts = Mage::getModel("blog/blog")->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addCatFilter($cats->getCatId())
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
            $collection = Mage::getModel("blog/blog")->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder("created_time ", "desc");

            $cats = Mage::getSingleton("blog/cat");
            
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);
            Mage::getSingleton("blog/status")->addCatFilterToCollection($collection, $cats->getCatId());

            $currentPage = (int) $this->getRequest()->getParam("page");
            $cat = $this->getRequest()->getParam("identifier");

            if (!$currentPage) {
                $currentPage = 1;
            }

            $route = $this->getBlogHelper()->getBlogRoute();
            $pages = ceil(count($collection) / $perPage);
            $links = "";

            if ($currentPage > 1) {
                $links .= '<div class="left"><a href="' . $this->getUrl($route . "/cat") . $cat . "/page/" .($currentPage - 1) . '">Newer Posts</a></div>';
            }
            if ($currentPage < $pages) {
                $links .= '<div class="right"><a href="' . $this->getUrl($route . "/cat") . $cat . "/page/" . ($currentPage + 1) . '">Older Posts</a></div>';
            }
            echo $links;
        }
    }

    protected function _prepareLayout()
    {
        $post = $this->getCat();

        // Show breadcrumbs
        if (Mage::getStoreConfig("fontis_blog/blog/blogcrumbs") && ($breadcrumbs = $this->getLayout()->getBlock("breadcrumbs"))) {
            $blogTitle = Mage::getStoreConfig('fontis_blog/blog/title');
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
                "label" => $post->getTitle(),
                "title" => $post->getTitle()
            ));
        }
        
        if ($head = $this->getLayout()->getBlock("head")) {
            $head->setTitle($post->getTitle());
            $head->setKeywords($post->getMetaKeywords());
            $head->setDescription($post->getMetaDescription());
        }
    }
}
