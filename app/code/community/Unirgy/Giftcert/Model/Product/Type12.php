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

class Unirgy_GiftCert_Model_Product_Type12 extends Mage_Catalog_Model_Product_Type_Abstract
{
    /**
    * Generate product options for cart item
    *
    * @param Varien_Object $buyRequest
    */
    public function prepareForCart(Varien_Object $buyRequest)
    {
        $product = $this->getProduct();

        $result = parent::prepareForCart($buyRequest, $product);

        if (is_string($result)) {
            return $result;
        }

        $hlp = Mage::helper('ugiftcert');

        $amountConfig = $hlp->getAmountConfig($product);

        if ($store->isAdmin()) {
            $amount = $product->getPrice();
        // Attempt to add to cart from product list
        // Need to update info_buyRequest[amount] somehow
        //if ($amountConfig['type']=='fixed') {
        //    $amount = $amountConfig['amount'];
        } else {
            $amount = $buyRequest->getAmount();
            if (!$amount) {
                return Mage::helper('ugiftcert')->__('Please enter gift certificate information');
            }
        }

        // maintain same price for not base currency
        $amount /= Mage::app()->getStore()->getCurrentCurrencyRate();

        #$buyRequest->setAmount($amount);

        $product->addCustomOption('amount', $amount);

        $fields = array();
        foreach ($hlp->getGiftcertOptionVars() as $k=>$l) {
            $fields[$k] = $k;
        }
        $fields['message'] = 'recipient_message'; // legacy templates (before 0.7.5)

        foreach ($fields as $p=>$k) {
            if ($v = $buyRequest->getData($p)) {
                $product->addCustomOption($k, $v);
            }
        }

        return $result;
    }

    /**
    * Check whether quote item is virtual
    *
    * @return boolean
    */
    public function isVirtual()
    {
        $product = $this->getProduct();
        if (Mage::getStoreConfig('ugiftcert/address/always_virtual', $product->getStoreId())) {
            return true;
        }

        $item = $product->getQuoteItem();
        if (!$item) {
            return false;
        }

        $options = array();
        foreach ($item->getOptions() as $option) {
            $options[$option->getCode()] = $option->getValue();
        }
        if ((!empty($options['recipient_email']) && empty($options['recipient_address']))
            || (empty($options['recipient_name']) && empty($options['toself_printed']))
            ) {
            return true;
        } else {
            return false;
        }
    }
}
