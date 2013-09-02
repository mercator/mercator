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
class Unirgy_Giftcert_Helper_Payment extends Mage_Payment_Helper_Data
{
    /**
    * Show "Fully paid by GC" during checkout instead of payment methods
    *
    * @param mixed $store
    * @param mixed $quote
    * @return mixed
    */
    public function getStoreMethods($store=null, $quote=null)
    {
        $fullyPaidByGiftcert = ($quote instanceof Mage_Sales_Model_Quote)
            && ($quote->getGiftcertCode())
            && ($quote->getBaseGrandTotal()==0);
        if ($fullyPaidByGiftcert) {
            return array(Mage::getModel(Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS.'/ugiftcert/model', $store)));
        }
        return parent::getStoreMethods($store, $quote);
    }
}
