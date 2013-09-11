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

abstract class Fontis_Blog_Block_Abstract extends Mage_Core_Block_Template
{
    protected $_blogHelper = null;
    protected $_cmsProcessor = null;

    /**
     * @return Fontis_Blog_Helper_Data
     */
    protected function getBlogHelper()
    {
        if (!$this->_blogHelper) {
            $this->_blogHelper = Mage::helper("blog");
        }
        return $this->_blogHelper;
    }

    protected function getCmsProcessor()
    {
        if (!$this->_cmsProcessor) {
            $this->_cmsProcessor = Mage::helper("cms")->getPageTemplateProcessor();
        }
        return $this->_cmsProcessor;
    }

    protected function processPost($post, $showTime = false)
    {
        $post->setAddress($this->getBlogHelper()->getPostUrl($post));

        $dateFormat = Mage::getStoreConfig("fontis_blog/blog/dateformat");
        $post->setCreatedTime($this->formatDate($post->getCreatedTime(), $dateFormat, $showTime));
        $post->setUpdateTime($this->formatDate($post->getUpdateTime(), $dateFormat, $showTime));

        // Check if we need to use summary content and make adjustments as necessary
        if (Mage::getStoreConfig("fontis_blog/blog/usesummary") && ($summaryContent = $post->getSummaryContent())) {
            $summaryContent .= ' ...&nbsp;&nbsp;<a href="' . $post->getAddress() . '">' . $this->__("Read More") . '</a>';
            $post->setPostContent($summaryContent);
        } else if ($readMore = (int) Mage::getStoreConfig('fontis_blog/blog/readmore')) {
            $content = $post->getPostContent();
            if (strlen($content) >= $readMore) {
                $content = substr($content, 0, $readMore);
                $content = substr($content, 0, strrpos($content, '. ') + 1);
                $content = $this->closeTags($content);
                $content .= ' ...&nbsp;&nbsp;<a href="' . $post->getAddress() . '">' . $this->__("Read More") . '</a>';
            }
            $post->setPostContent($content);
        }
        $post->setPostContent($this->getCmsProcessor()->filter($post->getPostContent()));

        // Get comment count
        $comments = Mage::getModel("blog/comment")->getCollection()
            ->addPostFilter($post->getPostId())
            ->addApproveFilter(2);
        $post->setCommentCount(count($comments));

        // Get the categories this post is in
        $cats = Mage::getModel("blog/cat")->getCollection()
            ->addPostFilter($post->getPostId());
        $catUrls = array();
        $helper = Mage::helper("blog");
        foreach ($cats as $cat) {
            $catUrls[$cat->getTitle()] = $helper->getCatUrl($cat);
        }
        $post->setCats($catUrls);
    }

    public function getBookmarkHtml($post)
    {
        if (Mage::getStoreConfig("fontis_blog/blog/bookmarkslist")) {
            $this->setTemplate("fontis/blog/bookmark.phtml");
            $this->setPost($post);
            return $this->toHtml();
        }
        return null;
    }

    public function getCommentsEnabled()
    {
        return Mage::getStoreConfig("fontis_blog/comments/enabled");
    }

    public function closeTags($html)
    {
        // Put all opened tags into an array
        preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
        $openedTags = $result[1];

        // Put all closed tags into an array
        preg_match_all("#</([a-z]+)>#iU", $html, $result);
        $closedTags = $result[1];
        $lenOpened = count($openedTags);

        // All tags are closed
        if (count($closedTags) == $lenOpened) {
            return $html;
        }
        $openedTags = array_reverse($openedTags);

        // Close tags
        for ($i = 0; $i < $lenOpened; $i++) {
            if (!in_array($openedTags[$i], $closedTags)) {
                $html .= "</" . $openedTags[$i] . ">";
            } else {
                unset($closedTags[array_search($openedTags[$i], $closedTags)]);
            }
        }

        return $html;
    }
}
