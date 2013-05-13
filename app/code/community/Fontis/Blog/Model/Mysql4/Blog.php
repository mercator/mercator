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

class Fontis_Blog_Model_Mysql4_Blog extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the blog_id refers to the key field in your database table.
        $this->_init('blog/blog', 'post_id');
    }
	
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('post_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('store'), $condition);

        if ($object->getData('stores')) {
            foreach ((array)$object->getData('stores') as $store) {
                $storeArray = array();
                $storeArray['post_id'] = $object->getId();
                $storeArray['store_id'] = $store;
                $this->_getWriteAdapter()->insert($this->getTable('store'), $storeArray);
            }
        } else {
            $storeArray = array();
            $storeArray['post_id'] = $object->getId();
            $storeArray['store_id'] = '0';
            $this->_getWriteAdapter()->insert($this->getTable('store'), $storeArray);
        }

        return parent::_afterSave($object);
    }
	
	/**
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('store'))
            ->where('post_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }
		
		$select = $this->_getReadAdapter()->select()
            ->from($this->getTable('post_cat'))
            ->where('post_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $catsArray = array();
            foreach ($data as $row) {
                $catsArray[] = $row['cat_id'];
            }
            $object->setData('cat_id', $catsArray);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->join(array('cps' => $this->getTable('store')), $this->getMainTable() . '.post_id = `cps`.post_id')
                ->where('`cps`.store_id in (0, ?) ', $object->getStoreId())
                ->order('store_id DESC')
                ->limit(1);
        }
        return $select;
    }
}
