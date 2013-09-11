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

class Fontis_Blog_Block_Archive extends Fontis_Blog_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_archives";

    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    public function getPosts()
    {
        $collection = Mage::getModel("blog/post")->getCollection()
            ->setOrder("created_time", "desc");

        $request = $this->getRequest();
        $archiveType = $request->getParam("type");
        $dateParams = $request->getParam("date");
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::WHERE);
        switch ($archiveType)
        {
            case Fontis_Blog_Model_System_Archivetype::DAILY:
                $select->where(new Zend_Db_Expr("day(created_time) = " . $dateParams[2]));
            case Fontis_Blog_Model_System_Archivetype::MONTHLY:
                $select->where(new Zend_Db_Expr("month(created_time) = " . $dateParams[1]));
            case Fontis_Blog_Model_System_Archivetype::YEARLY:
            default:
                $select->where(new Zend_Db_Expr("year(created_time) = " . $dateParams[0]));
                break;
        }
        $collection->addStoreFilter(Mage::app()->getStore()->getId());

        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);

        foreach ($collection as $item) {
            $this->processPost($item, true);
        }
        return $collection;
    }
}
