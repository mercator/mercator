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

class Fontis_Blog_Block_Menu extends Fontis_Blog_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_menu";

    protected $_activeCatLink = false;

    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getArchiveLabel()
    {
        return Fontis_Blog_Model_System_Archivetype::getTypeLabel(Mage::getStoreConfig("fontis_blog/archives/type"));
    }

    public function getArchives()
    {
        if ($this->getBlogHelper()->isArchivesEnabled()) {
            $collection = Mage::getModel("blog/post")->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder("created_time", Mage::getStoreConfig("fontis_blog/archives/order"));
            Mage::getSingleton('blog/status')->addEnabledFilterToCollection($collection);

            $archiveType = Mage::getStoreConfig("fontis_blog/archives/type");
            if ($archiveType == Fontis_Blog_Model_System_Archivetype::YEARLY) {
                $columns = array(new Zend_Db_Expr("year(created_time) as year"));
                $group = "year(created_time)";
            } elseif ($archiveType == Fontis_Blog_Model_System_Archivetype::MONTHLY) {
                $columns = array(
                    new Zend_Db_Expr("year(created_time) as year"),
                    new Zend_Db_Expr("month(created_time) as month")
                );
                $group = "year(created_time), month(created_time)";
            } elseif ($archiveType == Fontis_Blog_Model_System_Archivetype::DAILY) {
                $columns = array(
                    new Zend_Db_Expr("year(created_time) as year"),
                    new Zend_Db_Expr("month(created_time) as month"),
                    new Zend_Db_Expr("day(created_time) as day"),
                );
                $group = "year(created_time), month(created_time), day(created_time)";
            }
            if ($this->showPostCount()) {
                $columns[] = new Zend_Db_Expr("count(main_table.post_id) as postcount");
            }
            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns($columns)
                ->group($group);
            if ($limit = Mage::getStoreConfig("fontis_blog/archives/limit")) {
                $collection->getSelect()->limit($limit);
            }

            $dateString = Fontis_Blog_Model_System_Archivetype::getTypeFormat($archiveType);
            $route = $this->getBlogHelper()->getBlogRoute();
            foreach ($collection as $item) {
                $archiveRoute = "archive/" . $item->getYear();
                if ($item->getMonth()) {
                    $archiveRoute .= "/" . $item->getMonth();
                    if ($item->getDay()) {
                        $archiveRoute .= "/" . $item->getDay();
                    }
                }
                $item->setAddress($this->getUrl($route) . $archiveRoute);
                if (!$item->getDay()) {
                    $item->setDay(1);
                }
                if (!$item->getMonth()) {
                    $item->setMonth(1);
                }
                $item->setDateString(date($dateString, mktime(0, 0, 0, $item->getMonth(), $item->getDay(), $item->getYear())));
            }
            return $collection;
        } else {
            return false;
        }
    }

    public function showPostCount()
    {
        return Mage::getStoreConfig("fontis_blog/archives/showcount");
    }

    public function getRecent()
    {
        if ($recentCount = Mage::getStoreConfig("fontis_blog/menu/recent")) {
            $posts = Mage::getModel("blog/post")->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder("created_time", "desc");
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($posts);
            $posts->setPageSize($recentCount)
                ->setCurPage(1);

            $route = $this->getBlogHelper()->getBlogRoute();
            foreach ($posts as $post) {
                $post->setAddress($this->getUrl($route) . $post->getIdentifier());
            }
            return $posts;
        } else {
            return false;
        }
    }

    public function getCategories()
    {
        $categories = Mage::getModel("blog/cat")->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder("sort_order", "asc");

        $route = $this->getBlogHelper()->getBlogRoute();

        foreach ($categories as $category) {
            $category->setAddress($this->getUrl($route . "/cat") . $category->getIdentifier());
        }
        return $categories;
    }

    /**
     * @param Fontis_Blog_Model_Cat $cat
     * @return bool
     */
    public function isCatActive($cat)
    {
        if ($this->_activeCatLink === false) {
            $request = $this->getRequest();
            if ($request->getModuleName() == "blog" && $request->getControllerName() == "cat" && $request->getActionName() == "view") {
                $this->_activeCatLink = $request->getParam("identifier");
            } else {
                $this->_activeCatLink = null;
                return false;
            }
        }
        if ($this->_activeCatLink === null) {
            return false;
        }

        if ($this->_activeCatLink == $cat->getIdentifier()) {
            return true;
        } else {
            return false;
        }
    }
}
