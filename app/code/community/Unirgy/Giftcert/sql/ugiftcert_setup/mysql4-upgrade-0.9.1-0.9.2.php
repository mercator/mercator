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
$conn->addColumn($this->getTable('ugiftcert/cert'), 'store_id', 'smallint unsigned not null');
$conn->addColumn($this->getTable('ugiftcert/cert'), 'sender_name', 'varchar(100) not null');

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$eav->addAttribute('order', 'giftcert_amount_invoiced', array('type' => 'decimal'));
$eav->addAttribute('order', 'base_giftcert_amount_invoiced', array('type' => 'decimal'));
$eav->addAttribute('order', 'giftcert_amount_credited', array('type' => 'decimal'));
$eav->addAttribute('order', 'base_giftcert_amount_credited', array('type' => 'decimal'));

$eav->addAttribute('invoice', 'giftcert_amount', array('type' => 'decimal'));
$eav->addAttribute('invoice', 'base_giftcert_amount', array('type' => 'decimal'));
$eav->addAttribute('creditmemo', 'giftcert_amount', array('type' => 'decimal'));
$eav->addAttribute('creditmemo', 'base_giftcert_amount', array('type' => 'decimal'));

$this->endSetup();