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

class Fontis_Blog_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED          = "fontis_blog/blog/enabled";
    const XML_PATH_TITLE            = "fontis_blog/blog/title";
    const XML_PATH_MENU_LEFT        = "fontis_blog/blog/menuLeft";
    const XML_PATH_MENU_RIGHT       = "fontis_blog/blog/menuRoght";
    const XML_PATH_FOOTER_ENABLED   = "fontis_blog/blog/footerEnabled";
    const XML_PATH_LAYOUT           = "fontis_blog/blog/layout";
    const BLOG_COMMENTS_ENABLED     = "fontis_blog/comments/enabled";
    const BLOG_COMMENTS_LOGIN       = "fontis_blog/comments/login";
    const BLOG_TITLE                = "fontis_blog/blog/title";
    const BLOG_ARCHIVES_ENABLED     = "fontis_blog/archives/enabled";

    const GLOBAL_CACHE_TAG          = "fontis_blog";

    protected $_route = null;
    protected $_bmImagesRoute = null;
    protected $_fpcProcessor = false;

    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    public function isTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_TITLE);
    }

    public function isMenuLeft()
    {
        return Mage::getStoreConfig(self::XML_PATH_MENU_LEFT);
    }

    public function isMenuRight()
    {
        return Mage::getStoreConfig(self::XML_PATH_MENU_RIGHT);
    }

    public function isFooterEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_FOOTER_ENABLED);
    }

    public function isLayout()
    {
        return Mage::getStoreConfig(self::XML_PATH_LAYOUT);
    }

    public function isCommentsEnabled()
    {
        return Mage::getStoreConfig(self::BLOG_COMMENTS_ENABLED);
    }

    public function isLoginRequired()
    {
        return Mage::getStoreConfig(self::BLOG_COMMENTS_LOGIN);
    }

    /**
     * @return string
     */
    public function getBlogTitle()
    {
        return Mage::getStoreConfig(self::BLOG_TITLE);
    }

    public function isArchivesEnabled()
    {
        return Mage::getStoreConfig(self::BLOG_ARCHIVES_ENABLED);
    }

    public function getUserName()
    {
        $customer = Mage::getSingleton("customer/session")->getCustomer();
        return trim($customer->getFirstname() . " " . $customer->getLastname());
    }

    public function getUserEmail()
    {
        $customer = Mage::getSingleton("customer/session")->getCustomer();
        return $customer->getEmail();
    }

    /**
     * @return string
     */
    public function getPostUrl($post)
    {
        return Mage::getUrl($this->getBlogRoute()) . $post->getIdentifier();
    }

    /**
     * @return string
     */
    public function getCatUrl($cat)
    {
        return Mage::getUrl($this->getBlogRoute()) . "cat/" . $cat->getIdentifier();
    }

    /**
     * @return string
     */
    public function getBlogRoute()
    {
        if ($this->_route === null) {
            $this->_route = Mage::getStoreConfig("fontis_blog/blog/route");
            if (!$this->_route) {
                $this->_route =  "blog";
            }
        }
        return $this->_route;
    }

    public function getBookmarkImagesRoute()
    {
        if ($this->_bmImagesRoute === null) {
            $this->_bmImagesRoute = Mage::getDesign()->getSkinUrl("fontis/blog/images/communityIcons");
        }
        return $this->_bmImagesRoute;
    }

    public function useRecaptcha()
    {
        if (Mage::getStoreConfig("fontis_blog/comments/recaptcha")) {
            if (Mage::helper("core")->isModuleOutputEnabled("Fontis_Recaptcha") && Mage::helper("fontis_recaptcha")->isEnabled()) {
                if (!(Mage::getStoreConfig("fontis_recaptcha/recaptcha/when_loggedin") && (Mage::getSingleton('customer/session')->isLoggedIn()))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return object|null
     */
    public function getFpcProcessor()
    {
        if ($this->_fpcProcessor === false) {
            $fpcProcessor = Mage::getConfig()->getNode("global/cache/fpc_processor");
            if ($fpcProcessor) {
                $this->_fpcProcessor = Mage::getSingleton($fpcProcessor);
            } else {
                $this->_fpcProcessor = null;
            }
        }
        return $this->_fpcProcessor;
    }

    public function addTagToFpc($tag)
    {
        if ($fpc = $this->getFpcProcessor()) {
            $fpc->addRequestTag($tag);
        }
    }

    /**
     * @param array|string $tag
     */
    public function clearFpcTags($tags)
    {
        if (!is_array($tags)) {
            $tags = array($tags);
        }
        Mage::app()->cleanCache($tags);
    }

    /**
     * If a new post is created, or an existing post is enabled or unhidden, clear any FPC entries
     * for pages that this new post should show up on.
     *
     * @param Fontis_Blog_Model_Post $post
     */
    public function enablePost(Fontis_Blog_Model_Post $post)
    {
        $tags = array(
            Fontis_Blog_Block_Blog::CACHE_TAG,
            Fontis_Blog_Block_Rss::CACHE_TAG,
            Fontis_Blog_Block_Archive::CACHE_TAG,
        );
        foreach ($post->getCats() as $cat) {
            $tags[] = Fontis_Blog_Block_Cat::CACHE_TAG . "_" . $cat;
        }
        $this->clearFpcTags($tags);
    }

    /**
     * When a post is deleted, hidden or disabled, it needs to be removed from the frontend immediately.
     * All FPC entries with blog content should be cleared to ensure this happens.
     */
    public function disablePost()
    {
        $this->clearFpcTags(self::GLOBAL_CACHE_TAG);
    }
}
