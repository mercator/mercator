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

class Indust_CustomShippingRate_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled() {
        return Mage::getStoreConfig('carriers/customshippingrate/active');
    }

    public function isMage13() {
        $i = Mage::getVersion();
        $i = explode(".", $i);
        if ($i[1] == 3) { // Check for Magento 1.3.x.x
            return true;
        } else {
            return false;
        }
    }

    public function isMage141Plus() {
        $i = Mage::getVersion();
        $i = explode(".", $i);
        if ($i[1] == 4 && $i[2] != 0) { // Check for Magento 1.4.1.x or 1.4.2.0
            return true;
        } else if ($i[1] == 5) { // Check for Magento 1.5.x.x
            return true;
        } else {
            return false;
        }
    }
}
