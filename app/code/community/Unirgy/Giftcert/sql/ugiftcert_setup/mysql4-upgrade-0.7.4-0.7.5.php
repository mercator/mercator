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
$table = $this->getTable('ugiftcert_cert');

$conn->addColumn($table, 'recipient_name', 'varchar(127) NULL');
$conn->addColumn($table, 'recipient_email', 'varchar(127) NULL');
$conn->addColumn($table, 'recipient_address', 'text NULL');
$conn->addColumn($table, 'recipient_message', 'text NULL');

$this->endSetup();
