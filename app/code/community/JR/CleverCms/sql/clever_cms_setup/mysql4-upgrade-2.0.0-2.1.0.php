<?php
$installer = $this;
/* @var $installer JR_CleverCms_Model_Resource_Setup */
$installer->startSetup();

$connection = $installer->getConnection();
$tablePageStore = $installer->getTable('cms_page_store');
$tablePageTree = $installer->getTable('cms_page_tree');
$tablePageTreeStore = $installer->getTable('cms_page_tree_store');

$installer->run("
    DROP TABLE IF EXISTS `{$tablePageTreeStore}`;
    CREATE TABLE `{$tablePageTreeStore}` LIKE `{$tablePageStore}`;
");

Mage::app()->reinitStores(); // needed to have store list
$storeIds = array_keys(Mage::app()->getStores(false));
foreach ($storeIds as $storeId) {
    $installer->run("
        INSERT INTO `{$tablePageTreeStore}` (`page_id`, `store_id`)
        SELECT `page_id`, {$storeId} AS `store_id` FROM `{$tablePageTree}` WHERE `store_id` = '0';
    ");
}

$installer->endSetup();
