<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */

if ($this->getTable('ugiftcert_cert') !== 'ugiftcert_cert') {
    $this->startSetup();

    $conn = $this->_conn;
    $table = $this->getTable('ugiftcert_history');
    $conn->dropForeignKey($table, 'FK_ugiftcert_history');
    $conn->dropForeignKey($table, 'FK_ugiftcert_history_customer');
    $conn->dropForeignKey($table, 'FK_ugiftcert_history_order');
    $conn->dropForeignKey($table, 'FK_ugiftcert_history_user');

    $conn->addConstraint('FK_ugiftcert_history', $table, 'cert_id', $this->getTable('ugiftcert_cert'), 'cert_id', 'CASCADE', 'CASCADE');
    $conn->addConstraint('FK_ugiftcert_history_customer', $table, 'customer_id', $this->getTable('customer_entity'), 'entity_id', 'SET NULL', 'SET NULL');
    $conn->addConstraint('FK_ugiftcert_history_order', $table, 'order_id', $this->getTable('sales_order'), 'entity_id', 'SET NULL', 'SET NULL');
    $conn->addConstraint('FK_ugiftcert_history_user', $table, 'user_id', $this->getTable('admin_user'), 'user_id', 'SET NULL', 'SET NULL');

    $this->endSetup();
}
