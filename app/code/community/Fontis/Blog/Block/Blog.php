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
    const CACHE_TAG = "fontis_blog_index";

    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    public function getPosts()
    {
        $collection = Mage::getModel("blog/post")->getCollection()
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
            $collection = Mage::getModel("blog/post")->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder("created_time ", "desc");
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);

            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array(new Zend_Db_Expr("count(main_table.post_id) as postcount")));
            $collection->load();

            $currentPage = (int) $this->getRequest()->getParam("page");

            if (!$currentPage) {
                $currentPage = 1;
            }

            $route = $this->getBlogHelper()->getBlogRoute();
            $pages = ceil($collection->getFirstItem()->getPostcount() / $perPage);
            $links = "";
            unset($collection);

            if ($currentPage > 1) {
                $links .= '<div class="left"><a href="' . $this->getUrl($route . "/page") . ($currentPage - 1) . '">Newer Posts</a></div>';
            }
            if ($currentPage < $pages) {
                $links .= '<div class="right"><a href="' . $this->getUrl($route . "/page") . ($currentPage + 1) . '">Older Posts</a></div>';
            }
            echo $links;
        }
    }
}
