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

class Fontis_Blog_Block_Post extends Mage_Core_Block_Template
{
    const GRAVATAR_BASE_URL         = "http://www.gravatar.com/avatar/";
    const GRAVATAR_SECURE_BASE_URL  = "https://secure.gravatar.com/avatar/";

    protected function _prepareLayout()
    {
        $blogHelper = Mage::helper("blog");
        $post = $this->getPost();
        $blogTitle = Mage::getStoreConfig("fontis_blog/blog/title");

        // Show breadcrumbs
        if (Mage::getStoreConfig("fontis_blog/blog/blogcrumbs") && ($breadcrumbs = $this->getLayout()->getBlock("breadcrumbs"))) {
            $breadcrumbs->addCrumb("home", array(
                "label" => $blogHelper->__("Home"),
                "title" => $blogHelper->__("Go to Home Page"),
                "link"  => Mage::getBaseUrl()
            ));
            $breadcrumbs->addCrumb("blog", array(
                "label" => $blogTitle,
                "title" => $blogHelper->__("Return to") . " $blogTitle",
                "link"  => Mage::getUrl($blogHelper->getBlogRoute())
            ));
            $breadcrumbs->addCrumb("blog_page", array(
                "label" => $post->getTitle(),
                "title" => $post->getTitle()
            ));
        }

        if ($head = $this->getLayout()->getBlock("head")) {
            $head->setTitle($blogTitle . " - " . $post->getTitle());
            $head->setKeywords($post->getMetaKeywords());
            $head->setDescription($post->getMetaDescription());
        }
    }

    //TODO: this should probably be removed and needs to be checked to make sure it's okay to do so
    public function getFormAction()
    {
        return $this->getUrl("*/*/post");
    }

    //TODO: should this function be public or protected?
    public function getFormData()
    {
        return $this->getRequest();
    }

    public function getPost()
    {
        if (!$this->hasData("post")) {
            if ($this->getPostId()) {
                $post = Mage::getModel("blog/post")
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getPostId(), "post_id");
            } else {
                $post = Mage::getSingleton("blog/post");
            }

            $post->setPostContent(Mage::helper("cms")->getPageTemplateProcessor()->filter($post->getPostContent()));

            $helper = Mage::helper("blog");

            $post->setAddress($helper->getPostUrl($post));

            $dateFormat = Mage::getStoreConfig("fontis_blog/blog/dateformat");
            $post->setCreatedTime($this->formatTime($post->getCreatedTime(), $dateFormat, true));
            $post->setUpdateTime($this->formatTime($post->getUpdateTime(), $dateFormat, true));

            $cats = Mage::getModel("blog/cat")->getCollection()->addPostFilter($post->getPostId());
            $catUrls = array();
            foreach ($cats as $cat) {
                $catUrls[$cat->getTitle()] = $helper->getCatUrl($cat);
            }
            $post->setCats($catUrls);

            $this->setData("post", $post);
        }
        return $this->getData("post");
    }

    public function getBookmarkHtml($post)
    {
        if (Mage::getStoreConfig("fontis_blog/blog/bookmarkspost")) {
            $this->setTemplate("fontis/blog/bookmark.phtml");
            $this->setPost($post);
            return $this->toHtml();
        }
        return null;
    }

    public function getComment()
    {
        $post = $this->getPost();

        $collection = Mage::getModel("blog/comment")->getCollection()
            ->addPostFilter($post->getPostId())
            ->setOrder("created_time", "asc")
            ->addApproveFilter(2) // I haven't yet decided the appropriate place for comment status constants
            ->load();

        return $collection;
    }

    /**
     * This algorithm relies on the fact that comments are in the database
     * in numerical order. Don't go mucking with them!
     *
     * @param array $comments
     * @return array
     */
    public function commentsThread($comments)
    {
        $thread = array();
        foreach ($comments as $key => $comment) {
            $comment->setCreatedTime($this->formatTime($comment->getCreatedTime(), Mage::getStoreConfig("fontis_blog/blog/dateformat"), true));
            if ($repliedTo = $comment->getInReplyTo()) {
                $repliedToArray = &$this->array_search_recursive($repliedTo, $thread);
                $repliedToArray["children"][$key] = array("currentComment" => $comment, "children" => array());
            } else {
                $thread[$key] = array("currentComment" => $comment, "children" => array());
            }
        }
        return $thread;
    }

    private function &array_search_recursive($keyneedle, &$haystack)
    {
        $poster = null;
        foreach ($haystack as $key => &$value) {
            if ($key == $keyneedle) {
                return $value;
            } else if (is_array($value["children"])) {
                $poster = &$this->array_search_recursive($keyneedle, $value["children"]);
                if (isset($poster)) {
                    break;
                }
            }
        }
        return $poster;
    }

    public function getCommentTotalString($comments)
    {
        $commentCount = count($comments);
        if ($commentCount == 1) {
            return $commentCount . " " . Mage::helper("blog")->__("Comment");
        } else {
            return $commentCount . " " . Mage::helper("blog")->__("Comments");
        }
    }

    public function getCommentsEnabled()
    {
        return Mage::getStoreConfig("fontis_blog/comments/enabled");
    }

    public function getLoginRequired()
    {
        return Mage::getStoreConfig("fontis_blog/comments/login");
    }

    public function setCommentDetails($name, $email, $comment)
    {
        $this->_data["commentName"] = $name;
        $this->_data["commentEmail"] = $email;
        $this->_data["commentComment"] = $comment;
        return $this;
    }

    public function getCommentText()
    {
        if (!empty($this->_data["commentComment"])) {
            return $this->_data["commentComment"];
        }
        return null;
    }

    public function getCommentEmail()
    {
        if (!empty($this->_data["commentEmail"])) {
            return $this->_data["commentEmail"];
        }
        return null;
    }

    public function getCommentName()
    {
        if (!empty($this->_data["commentName"])) {
            return $this->_data["commentName"];
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isGravatarEnabled()
    {
        if (Mage::getStoreConfig("fontis_blog/comments/grav_enabled")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function getGravatarSize()
    {
        $size = Mage::getStoreConfig("fontis_blog/comments/grav_size");
        if (!$size || !is_numeric($size)) {
            $size = 75;
        } elseif ($size < 1) {
            $size = 1;
        } elseif ($size > 2048) {
            $size = 2048;
        }
        return $size;
    }

    /**
     * @param string $emailAddress
     * @return null|string
     */
    public function getGravatarUrl($emailAddress)
    {
        if (!$this->isGravatarEnabled()) {
            return null;
        }

        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $url = self::GRAVATAR_SECURE_BASE_URL;
        } else {
            $url = self::GRAVATAR_BASE_URL;
        }
        $url .= md5(strtolower(trim($emailAddress))) . ".jpg?";

        $url .= "s=" . $this->getGravatarSize();

        if (Mage::getStoreConfig("fontis_blog/comments/grav_default")) {
            $url .= "&d=mm";
        } else {
            $url .= "&d=blank";
        }

        return $url;
    }
}
