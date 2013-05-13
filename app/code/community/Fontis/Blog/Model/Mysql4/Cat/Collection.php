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

class Fontis_Blog_Model_Mysql4_Cat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('blog/cat');
    }

	public function toOptionArray()
    {
        return $this->_toOptionArray('identifier', 'title');
    }

    protected function _afterLoad()
    {
		$items = $this->getColumnValues('identifier');
		if (count($items)) {
			$select = $this->getConnection()->select()
				->from($this->getTable('cat'));
			if ($result = $this->getConnection()->fetchPairs($select)) {
				foreach ($this as $item) {
					if (!isset($result[$item->getData('identifier')])) {
						continue;
					}
				}
			}
		}

        parent::_afterLoad();
    }
	
	public function addCatFilter($catId)
    {
        if (!Mage::app()->isSingleStoreMode()) {
			$this->getSelect()->join(
				array('cat_table' => $this->getTable('post_cat')),
				'main_table.post_id = cat_table.post_id',
				array()
			)
			->where('cat_table.cat_id = ?', $catId);
	
			return $this;
		}
		return $this;
    }
	
	public function addStoreFilter($store)
    {
		if (!Mage::app()->isSingleStoreMode()) {
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array($store->getId());
			}
	
			$this->getSelect()->join(
				array('store_table' => $this->getTable('cat_store')),
				'main_table.cat_id = store_table.cat_id',
				array()
			)
			->where('store_table.store_id in (?)', array(0, $store));
	
			return $this;
		}
		return $this;
	}
	
	public function addPostFilter($postId)
    {
		$this->getSelect()->join(
			array('cat_table' => $this->getTable('post_cat')),
			'main_table.cat_id = cat_table.cat_id',
			array()
		)
		->where('cat_table.post_id = ?', $postId);

		return $this;
    }
}
