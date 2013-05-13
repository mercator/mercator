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

class Indust_CustomShippingRate_Block_Express_Review extends Mage_Paypal_Block_Express_Review
{
    protected $_code = 'customshippingrate';

    /**
     * Return shipping rates
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (!Mage::helper('customshippingrate')->isMage141Plus()) {
            if (empty($this->_rates)) {
                $groups = $this->getAddress()->getGroupedAllShippingRates();
                if (!empty($groups)) {
                    foreach ($groups as $code => $groupItems) {
                        if ($code == $this->_code) {
                            unset($groups[$code]);
                        }
                    }
                }
                return $this->_rates = $groups;
            }
            return $this->_rates;
        }
    }

    /**
     * Retrieve payment method and assign additional template values
     *
     * @return Mage_Paypal_Block_Express_Review
     */
    protected function _beforeToHtml()
    {
        if (Mage::helper('customshippingrate')->isMage141Plus()) {
            $methodInstance = $this->_quote->getPayment()->getMethodInstance();
            $this->setPaymentMethodTitle($methodInstance->getTitle());

            $this->setShippingRateRequired(true);
            if ($this->_quote->getIsVirtual()) {
                $this->setShippingRateRequired(false);
            } else {
                // prepare shipping rates
                $this->_address = $this->_quote->getShippingAddress();
                $groups = $this->_address->getGroupedAllShippingRates();
                if (!empty($groups)) {
                    foreach ($groups as $code => $groupItems) {
                        if ($code == $this->_code) {
                            unset($groups[$code]);
                        }
                    }
                }
                if ($groups && $this->_address) {
                    $this->setShippingRateGroups($groups);
                    // determine current selected code & name
                    foreach ($groups as $code => $rates) {
                        foreach ($rates as $rate) {
                            if ($this->_address->getShippingMethod() == $rate->getCode()) {
                                $this->_currentShippingRate = $rate;
                                break(2);
                            }
                        }
                    }
                }

                // misc shipping parameters
                $this->setShippingMethodSubmitUrl($this->getUrl("{$this->_paypalActionPrefix}/express/saveShippingMethod"))
                    ->setCanEditShippingAddress($this->_quote->getMayEditShippingAddress())
                    ->setCanEditShippingMethod($this->_quote->getMayEditShippingMethod())
                ;
            }

            $this->setEditUrl($this->getUrl("{$this->_paypalActionPrefix}/express/edit"))
                ->setPlaceOrderUrl($this->getUrl("{$this->_paypalActionPrefix}/express/placeOrder"));

            //return parent::_beforeToHtml();
        }
    }
}
