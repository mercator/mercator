<?php
/**
 * Netzarbeiter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupsCatalog2
 * @copyright  Copyright (c) 2013 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/*
 * Create product customer group index table
 */
$tableName = $installer->getTable('netzarbeiter_groupscatalog2/product_index');

// To recreate the product index tables first drop the old version
if ($installer->getConnection()->isTableExists($tableName)) {
    $installer->getConnection()->dropTable($tableName);
}

$table = $installer->getConnection()->newTable($tableName)

    ->addColumn('catalog_entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary' => true,
        'unsigned' => true,
        'nullable' => false,
    ), 'Product ID')

    ->addColumn('group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'primary' => true,
        'unsigned' => true,
        'nullable' => false,
    ), 'Customer Group Id')

    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'primary' => true,
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
    ), 'Store ID')

    ->addForeignKey(
        $installer->getFkName($tableName, 'catalog_entity_id', 'catalog/product', 'entity_id'),
        'catalog_entity_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey(
        $installer->getFkName($tableName, 'group_id', 'customer/customer_group', 'customer_group_id'),
        'group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey(
        $installer->getFkName($tableName, 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->setComment('GroupsCatalog2 Product Customer Group Index Table');
$installer->getConnection()->createTable($table);


/*
 * Create category customer group index table
 */
$tableName = $installer->getTable('netzarbeiter_groupscatalog2/category_index');

// To recreate the category index tables first drop the old version
if ($installer->getConnection()->isTableExists($tableName)) {
    $installer->getConnection()->dropTable($tableName);
}

$table = $installer->getConnection()->newTable($tableName)
    ->addColumn('catalog_entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary' => true,
        'unsigned' => true,
        'nullable' => false,
    ), 'Category ID')

    ->addColumn('group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'primary' => true,
        'unsigned' => true,
        'nullable' => false,
    ), 'Customer Group Id')

    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'primary' => true,
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
    ), 'Store ID')

    ->addForeignKey(
        $installer->getFkName($tableName, 'catalog_entity_id', 'catalog/category', 'entity_id'),
        'catalog_entity_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey(
        $installer->getFkName($tableName, 'group_id', 'customer/customer_group', 'customer_group_id'),
        'group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey(
        $installer->getFkName($tableName, 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->setComment('GroupsCatalog2 Category Customer Group Index Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();