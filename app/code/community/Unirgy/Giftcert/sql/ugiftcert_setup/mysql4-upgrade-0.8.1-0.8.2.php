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
$this->startSetup();

$conn = $this->_conn;

$conn->addColumn($this->getTable('ugiftcert_cert'), 'toself_printed', 'tinyint not null');

$table = $this->getTable('ugiftcert_history');

$conn->addColumn($table, 'order_item_id', 'int(10) unsigned after order_increment_id');
$conn->addConstraint('FK_ugiftcert_history_order_item', $table, 'order_item_id', $this->getTable('sales_order_entity'), 'entity_id', 'SET NULL', 'SET NULL');

$this->endSetup();
