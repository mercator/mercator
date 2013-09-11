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

class Fontis_Blog_Block_Rss extends Mage_Rss_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_rss";

    protected function _prepareLayout()
    {
        Mage::helper("blog")->addRequestTag(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    protected function _construct()
    {
        //setting cache to save the rss for 10 minutes
        $this->setCacheKey("rss_catalog_category_"
            . $this->getRequest()->getParam("cid") . "_"
            . $this->getRequest()->getParam("sid")
        );
        $this->setCacheLifetime(600);
    }

    protected function _toHtml()
    {
        $rssObj = Mage::getModel("rss/rss");
        $url = $this->getUrl(Mage::helper("blog")->getBlogRoute());
        $title = Mage::getStoreConfig("fontis_blog/rss/title");
        if (!$title) {
            $title = Mage::getStoreConfig("fontis_blog/blog/title");
        }
        $data = array(
            "title"         => $title,
            "description"   => $title,
            "link"          => $url,
            "charset"       => "UTF-8"
        );

        if ($rssImage = Mage::getStoreConfig("fontis_blog/rss/image")) {
            $data["image"] = $this->getSkinUrl($rssImage);
        }

        $rssObj->_addHeader($data);

        $collection = Mage::getModel("blog/post")->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder("created_time ", "desc");

        $identifier = $this->getRequest()->getParam("identifier");
        if ($cat_id = Mage::getSingleton("blog/cat")->load($identifier)->getCatId()) {
            Mage::getSingleton("blog/status")->addCatFilterToCollection($collection, $cat_id);
        }
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);

        $collection->setPageSize((int) Mage::getStoreConfig("fontis_blog/rss/posts"));
        $collection->setCurPage(1);

        if ($collection->getSize() > 0) {
            foreach ($collection as $post) {
                $data = array(
                    "title"         => $post->getTitle(),
                    "link"          => $url . $post->getIdentifier(),
                    "description"   => Mage::getStoreConfig("fontis_blog/rss/usesummary") ? $post->getSummaryContent() : $post->getPostContent(),
                    "lastUpdate"    => strtotime($post->getCreatedTime()),
                );

                $rssObj->_addEntry($data);
            }
        }

        return $rssObj->createRssXml();
    }
}
