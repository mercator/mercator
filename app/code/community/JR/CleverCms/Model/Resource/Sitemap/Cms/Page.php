<?php
/*
 * Modified by Mats-Ola Ström, matsola {at} stromconsulting.se
 *
 */
class JR_CleverCms_Model_Resource_Sitemap_Cms_Page extends Mage_Sitemap_Model_Mysql4_Cms_Page
{
    protected function _construct()
    {
        Mage::getSingleton('core/resource')->setMappedTableName('cms_page', Mage::getConfig()->getTablePrefix() . 'cms_page_tree');
        $this->_init('cms/page', 'page_id');
    }

    public function getCollection($storeId)
    {
        $pages = array();
        $select = $this->_getWriteAdapter()
            ->select()
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName(), 'identifier AS url'))
            ->where('main_table.is_active = 1')
            ->where('main_table.store_id IN (?)', array(0, $storeId));
        $query = $this->_getWriteAdapter()->query($select);

        while ($row = $query->fetch()) {
            if ($row['url'] == Mage_Cms_Model_Page::NOROUTE_PAGE_ID) {
                continue;
            }
            $page = $this->_prepareObject($row);
            $pages[$page->getId()] = $page;
        }

        return $pages;
    }
}
