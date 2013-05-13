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

    protected $_route = null;
    protected $_bmImagesRoute = null;

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

    public function getPostUrl($post)
    {
        return Mage::getUrl($this->getBlogRoute()) . $post->getIdentifier();
    }

    public function getCatUrl($cat)
    {
        return Mage::getUrl($this->getBlogRoute()) . "cat/" . $cat->getIdentifier();
    }

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
}
