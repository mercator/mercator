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

$this->run("
CREATE TABLE {$this->getTable('ugiftcert_cert')} (
`cert_id` int(10) unsigned NOT NULL auto_increment,
`cert_number` varchar(40) NOT NULL,
`balance` decimal(12,4) NOT NULL,
`pin` varchar(20) NOT NULL,
`pin_hash` varchar(40) NOT NULL,
`status` char(1) NOT NULL default 'P',
`expire_at` date default NULL,
`recipient_name` varchar(127) default NULL,
`recipient_email` varchar(127) default NULL,
`recipient_address` text,
`recipient_message` text,
PRIMARY KEY  (`cert_id`),
KEY `KEY_cert_number` (`cert_number`),
KEY `KEY_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('ugiftcert_history')} (
 `history_id` int(10) unsigned NOT NULL auto_increment,
 `cert_id` int(10) unsigned NOT NULL,
 `action_code` varchar(20) NOT NULL,
 `ts` datetime NOT NULL,
 `amount` decimal(12,4) NOT NULL,
 `status` char(1) NOT NULL,
 `comments` text,
 `customer_id` int(10) unsigned default NULL,
 `customer_email` varchar(255) default NULL,
 `order_id` int(10) unsigned default NULL,
 `order_increment_id` varchar(50) default NULL,
 `user_id` mediumint(9) unsigned default NULL,
 `username` varchar(40) default NULL,
 PRIMARY KEY  (`history_id`),
 KEY `FK_ugiftcert_history` (`cert_id`),
 KEY `FK_ugiftcert_history_customer` (`customer_id`),
 KEY `FK_ugiftcert_history_order` (`order_id`),
 KEY `FK_ugiftcert_history_user` (`user_id`),
 CONSTRAINT `FK_ugiftcert_history` FOREIGN KEY (`cert_id`) REFERENCES `{$this->getTable('ugiftcert_cert')}` (`cert_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `FK_ugiftcert_history_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE SET NULL ON UPDATE SET NULL,
 CONSTRAINT `FK_ugiftcert_history_order` FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales_order')}` (`entity_id`) ON DELETE SET NULL ON UPDATE SET NULL,
 CONSTRAINT `FK_ugiftcert_history_user` FOREIGN KEY (`user_id`) REFERENCES `{$this->getTable('admin_user')}` (`user_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");


$this->_conn->addColumn($this->getTable('sales_flat_quote'), 'giftcert_code', 'varchar(100)');
$this->_conn->addColumn($this->getTable('sales_flat_quote_address'), 'giftcert_amount', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales_flat_quote_address'), 'base_giftcert_amount', 'decimal(12,4)');

/*
$sales = new Mage_Sales_Model_Mysql4_Setup();
$sales->addAttribute('quote', 'giftcert_code', array('type'=>'varchar'));
$sales->addAttribute('quote_address', 'giftcert_amount', array('type'=>'decimal'));
$sales->addAttribute('quote_address', 'base_giftcert_amount', array('type'=>'decimal'));
*/

$this->endSetup();
