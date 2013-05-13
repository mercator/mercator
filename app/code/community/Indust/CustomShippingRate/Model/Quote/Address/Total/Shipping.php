<?php
/**
 * Admin Custom Shipping Rate
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to jeffk@industrialtechware.com so we can send you a copy immediately.
 *
 * @category   Indust
 * @package    Indust_CustomShippingRate
 * @author     Jeff Kieke <jeffk@industrialtechware.com>
 * @copyright  Copyright (c) 2011, Jeff Kieke
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Indust_CustomShippingRate_Model_Quote_Address_Total_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping
{
    /**
     * Collect totals information about shipping
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $_code = 'customshippingrate_customshippingrate';

        $method = $address->getShippingMethod();

        if ($method == $_code) {

            $amountPrice = $address->getQuote()->getStore()->convertPrice($address->getBaseShippingAmount(), false);
            if (Mage::helper('customshippingrate')->isMage13()) {
                $address->setShippingAmount($amountPrice);
                $address->setBaseShippingAmount($address->getBaseShippingAmount(), true);
                $address->setGrandTotal($address->getGrandTotal() + $address->getShippingAmount());
                $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseShippingAmount());
            } else {
                $this->_setAddress($address);
                $this->_setAmount($amountPrice);
                $this->_setBaseAmount($address->getBaseShippingAmount());
            }

            return $this;
        } else {
            return parent::collect($address);
        }

    }

}
