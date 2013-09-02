<?php

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$sales->addAttribute('order', 'giftcert_code', array('type'=>'varchar'));
$sales->addAttribute('order', 'giftcert_amount', array('type'=>'decimal'));
$sales->addAttribute('order', 'base_giftcert_amount', array('type'=>'decimal'));

