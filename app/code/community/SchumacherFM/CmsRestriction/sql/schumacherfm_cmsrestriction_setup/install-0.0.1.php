<?php
/**
 * @category    SchumacherFM_CmsRestriction
 * @package     Setup
 * @author      Cyrill at Schumacher dot fm (@SchumacherFM)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Magento-CmsRestriction/issues
 */

$installer = $this;
$installer->startSetup();

/*
 * Change columns
 */
$tables = array(
    $installer->getTable('cms/page')  => array(
        'columns' => array(
            'allow_customer_groups' => array(
                'type'     => Varien_Db_Ddl_Table::TYPE_BIGINT,
                'default'  => '0',
                'length'   => 25,
                'comment'  => 'Allowed Customer Groups'
            ),
            'allow_customer_ids'    => array(
                'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'  => '64K',
                'comment' => 'Allowed Customer IDs'
            ),
        ),
    ),
    $installer->getTable('cms_block') => array(
        'columns' => array(
            'allow_customer_groups' => array(
                'type'     => Varien_Db_Ddl_Table::TYPE_BIGINT,
                'default'  => '0',
                'length'   => 25,
                'comment'  => 'Allowed Customer Groups'
            ),
            'allow_customer_ids'    => array(
                'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'  => '64K',
                'comment' => 'Allowed Customer IDs'
            ),
        ),
    ),
);

foreach ($tables as $table => $columnsAll) {

    $columns = $columnsAll['columns'];

    foreach ($columns as $columnName => $properties) {

        $installer->getConnection()->addColumn($table, $columnName, $properties);

    }

}

$installer->endSetup();
