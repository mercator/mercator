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

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

$table = $this->getTable(version_compare(Mage::getVersion(), '1.4', '<') ? 'eav_attribute' : 'catalog_eav_attribute');
$attrId = $this->_conn->fetchOne("select attribute_id from {$this->getTable('eav_attribute')} where attribute_code='tax_class_id' and entity_type_id={$eav->getEntityTypeId('catalog_product')}");
$this->run("update {$table} set apply_to=if(not find_in_set('ugiftcert','apply_to'),concat(apply_to,',ugiftcert'),'') where attribute_id={$attrId}");

$this->endSetup();