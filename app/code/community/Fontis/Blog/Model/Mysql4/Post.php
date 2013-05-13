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

class Fontis_Blog_Model_Mysql4_Post extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('blog/post', 'post_id');
    }

    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (strcmp($value, (int) $value) !== 0) {
            $field = 'identifier';
        }
        return parent::load($object, $value, $field);
    }
	
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$this->getIsUniqueIdentifier($object)) {
            Mage::throwException(Mage::helper('blog')->__('Post Identifier already exists.'));
        }

        if ($this->isNumericIdentifier($object)) {
            Mage::throwException(Mage::helper('blog')->__('Post Identifier cannot consist only of numbers.'));
        }

        return $this;
    }
	
    public function getIsUniqueIdentifier(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getWriteAdapter()->select()
                ->from($this->getMainTable())
                ->where($this->getMainTable() . '.identifier = ?', $object->getData('identifier'));
        if ($object->getId()) {
            $select->where($this->getMainTable() . '.post_id <> ?', $object->getId());
        }

        if ($this->_getWriteAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }
	
    protected function isNumericIdentifier (Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }
	
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('post_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('store'), $condition);
        if ($object->getData('stores')) {
            foreach ((array) $object->getData('stores') as $store) {
                $storeArray = array();
                $storeArray['post_id'] = $object->getId();
                $storeArray['store_id'] = $store;
                $this->_getWriteAdapter()->insert($this->getTable('store'), $storeArray);
            }
        } else {
            $storeArray = array();
            $storeArray['post_id'] = $object->getId();
            $storeArray['store_id'] = Mage::app()->getStore(true)->getId();
            $this->_getWriteAdapter()->insert($this->getTable('store'), $storeArray);
        }

        $condition = $this->_getWriteAdapter()->quoteInto('post_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('post_cat'), $condition);
		
        foreach ((array) $object->getData('cats') as $catId) {
            $storeArray = array();
            $storeArray['post_id'] = $object->getId();
            $storeArray['cat_id'] = $catId;
            $this->_getWriteAdapter()->insert($this->getTable('post_cat'), $storeArray);
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
            $select->join(array('cps' => $this->getTable('store')), $this->getMainTable().'.post_id = `cps`.post_id')
                ->where('`cps`.store_id in (0, ?) ', $object->getStoreId())
                ->order('store_id DESC')
                ->limit(1);
        }
        return $select;
    }
}
