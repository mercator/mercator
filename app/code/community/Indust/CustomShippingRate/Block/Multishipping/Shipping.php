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

class Indust_CustomShippingRate_Block_Multishipping_Shipping extends Mage_Checkout_Block_Multishipping_Shipping
{
    protected $_code = 'customshippingrate';

    /**
     * Return shipping rates
     *
     * @return array
     */
    public function getShippingRates($address)
    {
        $groups = $address->getGroupedAllShippingRates();
        if (!empty($groups)) {
            foreach ($groups as $code => $groupItems) {
                if ($code == $this->_code) {
                    unset($groups[$code]);
                }
            }
        }
        return $groups;
    }

}
