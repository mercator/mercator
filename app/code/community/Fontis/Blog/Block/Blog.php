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

class Fontis_Blog_Block_Blog extends Fontis_Blog_Block_Abstract
{
    public function getPosts()
    {
        $collection = Mage::getModel("blog/blog")->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder("created_time", "desc");
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);

        $page = $this->getRequest()->getParam("page");
        $collection->setPageSize((int) Mage::getStoreConfig("fontis_blog/blog/perpage"));
        $collection->setCurPage($page);
        
        foreach ($collection as $item) {
            $this->processPost($item, true);
        }
        return $collection;
    }
    
    public function getPages()
    {
        if ($perPage = (int) Mage::getStoreConfig("fontis_blog/blog/perpage")) {
            $collection = Mage::getModel("blog/blog")->getCollection()
                ->setOrder("created_time ", "desc");
            
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);
            
            $currentPage = (int) $this->getRequest()->getParam("page");
    
            if (!$currentPage) {
                $currentPage = 1;
            }

            $route = $this->getBlogHelper()->getBlogRoute();
            $pages = ceil(count($collection) / $perPage);
            $links = "";
            
            if ($currentPage > 1) {
                $links .= '<div class="left"><a href="' . $this->getUrl($route . "/page") . ($currentPage - 1) . '">Newer Posts</a></div>';
            }
            if ($currentPage < $pages) {
                $links .= '<div class="right"><a href="' . $this->getUrl($route . "/page") . ($currentPage + 1) . '">Older Posts</a></div>';
            }
            echo $links;
        }
    }

    public function addTopLink()
    {
        $title = Mage::getStoreConfig("fontis_blog/blog/title");
        $this->getParentBlock()->addLink($title, $this->getBlogHelper()->getBlogRoute(), $title, true, array(), 15, null, 'class="top-link-blog"');
    }

    public function addFooterLink()
    {
        $title = Mage::getStoreConfig("fontis_blog/blog/title");
        $this->getParentBlock()->addLink($title, $this->getBlogHelper()->getBlogRoute(), $title, true);
    }

    public function addRssFeed()
    {
        if (Mage::getStoreConfig("fontis_blog/rss/enabled")) {
            if ($head = $this->getLayout()->getBlock("head")) {
                $head->addItem("rss", Mage::getUrl($this->getBlogHelper()->getBlogRoute()) . "rss");
            }
        }
    }
}
