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

class Fontis_Blog_RssController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (Mage::getStoreConfig("fontis_blog/rss/enabled")) {
            $this->getResponse()->setHeader("Content-type", 'text/xml; charset=UTF-8');
            $this->loadLayout(false);
            $this->renderLayout();
        } else {
            $this->_forward("NoRoute");
        }
    }

    public function noRouteAction($coreRoute = null)
    {
        $this->getResponse()->setHeader("HTTP/1.1", "404 Not Found");
        $this->getResponse()->setHeader("Status", "404 File not found");

        $pageId = Mage::getStoreConfig("web/default/cms_no_route");
        if (!Mage::helper("cms/page")->renderPage($this, $pageId)) {
            $this->_forward("defaultNoRoute");
        }
    }
}
