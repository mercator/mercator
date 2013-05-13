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

class Fontis_Blog_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $blogTitle = Mage::getStoreConfig("fontis_blog/blog/title");
        $this->getLayout()->getBlock("root")->setTemplate(Mage::getStoreConfig("fontis_blog/blog/layout"));
        if (Mage::getStoreConfig("fontis_blog/blog/blogcrumbs") && ($breadcrumbs = $this->getLayout()->getBlock("breadcrumbs"))) {
            $breadcrumbs->addCrumb("home", array("label" => Mage::helper("cms")->__("Home"), "title" => Mage::helper("cms")->__("Go to Home Page"), "link" => Mage::getBaseUrl()));;
            $breadcrumbs->addCrumb("blog_blog", array("label" => $blogTitle, "title" => $blogTitle));
        }

        if ($head = $this->getLayout()->getBlock("head")) {
            $head->setTitle($blogTitle);
            $head->setKeywords(Mage::getStoreConfig("fontis_blog/blog/keywords"));
            $head->setDescription(Mage::getStoreConfig("fontis_blog/blog/description"));
        }
        $this->renderLayout();
    }
}
