<?php

class JR_CleverCms_Model_Resource_Cms_Page_Permission extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('cms/page_permission', 'permission_id');
    }

    public function getPagesByStoreAndCustomerGroup($storeId, $customerGroupId)
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable(), 'page_id')
            ->where('store_id = ?', $storeId)
            ->where('customer_group_id = ?', $customerGroupId);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    public function savePermissions($storeId, $customerGroupId, $pages)
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->delete(
            $this->getMainTable(),
            $adapter->quoteInto('store_id = ?', $storeId) . ' AND '
            . $adapter->quoteInto('customer_group_id = ?', $customerGroupId));

        $insert = array();
        foreach ($pages as $pageId) {
            if ($pageId) {
                $insert[] = array(
                    'store_id' => $storeId,
                    'customer_group_id' => $customerGroupId,
                    'page_id' => $pageId,
                );
            }
        }
        if (! empty($insert)) {
            $adapter->insertMultiple($this->getMainTable(), $insert);
        }

        return $this;
    }

    public function exists($storeId, $customerGroupId, $pageId)
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable())
            ->where('store_id IN (0, ?)', $storeId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('page_id = ?', $pageId);

        return $this->_getReadAdapter()->fetchRow($select);
    }
}