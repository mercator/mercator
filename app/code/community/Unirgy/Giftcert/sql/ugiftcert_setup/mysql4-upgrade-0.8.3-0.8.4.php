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

$select = $conn->select()->from($this->getTable('core_config_data'), array('value'))
    ->where("scope='default' and path='currency/options/default'");

$currency = $conn->fetchOne($select);
if (!$currency) {
    $currency = 'USD';
}

$table = $this->getTable('ugiftcert_cert');
$conn->addColumn($table, 'currency_code', 'char(3) not null after balance');
$this->run("update $table set currency_code='$currency' where currency_code=''");

$table = $this->getTable('ugiftcert_history');
$conn->addColumn($table, 'currency_code', 'char(3) not null after amount');
$this->run("update $table set currency_code='$currency' where currency_code=''");

$eav = new Mage_Eav_Model_Entity_Setup('catalog_setup');
$eav->addAttribute('catalog_product', 'ugiftcert_amount_config', array(
    'type' => 'text',
    'input' => 'textarea',
    'label' => 'GC Amount Configuration (leave empty for default configuration)',
    'global' => 2,
    'user_defined' => 1,
    'apply_to' => 'ugiftcert',
    'required' => 0,
));

$this->endSetup();