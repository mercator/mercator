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

class Fontis_Blog_ArchiveController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (!Mage::helper("blog")->isArchivesEnabled()) {
            $this->_forward("NoRoute");
            return;
        }

        return $this->renderPage(Mage::helper("blog")->__("Archives"));
    }

    public function viewAction()
    {
        if (!Mage::helper("blog")->isArchivesEnabled()) {
            $this->_forward('NoRoute');
            return;
        }

        $request = $this->getRequest();
        $date = $request->getParam("date");
        // strip off any query string parameters, and any trailing slashes
        $date = explode("?", $date);
        if (empty($date[0])) {
            $this->_forward('NoRoute');
            return;
        }

        $date = rtrim($date[0], "/");
        $dateParams = explode("/", $date);
        if (count($dateParams) == 0) {
            $this->_forward('Index');
            return;
        } elseif (count($dateParams) > 3) {
            // Stops the user from putting random strings on the end of the URL
            $this->_forward('NoRoute');
            return;
        }

        $archiveType = Fontis_Blog_Model_System_Archivetype::YEARLY;
        $year = $dateParams[0];
        if (isset($dateParams[1])) {
            $month = $dateParams[1];
            $archiveType = Fontis_Blog_Model_System_Archivetype::MONTHLY;
        } else {
            $month = 1;
        }
        if (isset($dateParams[2])) {
            $day = $dateParams[2];
            $archiveType = Fontis_Blog_Model_System_Archivetype::DAILY;
        } else {
            $day = 1;
        }

        if (!checkdate($month, $day, $year)) {
            $this->_forward("NoRoute");
            return;
        }

        $request->setParam("type", $archiveType);
        $request->setParam("date", $dateParams);

        $archiveLabel = Mage::helper("blog")->__("Archives");
        $dateString = Fontis_Blog_Model_System_Archivetype::getTypeFormat($archiveType);
        return $this->renderPage($archiveLabel . " - " . date($dateString, mktime(0, 0, 0, $month, $day, $year)));
    }

    protected function renderPage($pageTitle)
    {
        $this->loadLayout();
        $blogTitle = Mage::getStoreConfig('fontis_blog/blog/title');
        if ($head = $this->getLayout()->getBlock("head")) {
            $head->setTitle($blogTitle . " - " . $pageTitle);
        }
        if ($root = $this->getLayout()->getBlock("root")) {
            $root->setTemplate(Mage::getStoreConfig("fontis_blog/blog/layout"));
        }
        $this->renderLayout();
        return true;
    }
}
