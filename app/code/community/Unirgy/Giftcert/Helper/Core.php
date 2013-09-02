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
class Unirgy_Giftcert_Helper_Core extends Mage_Core_Helper_Data
{
    /**
    * Used only to show GC price in product list - disable currency conversion
    *
    * @param string $value
    * @param bool $format
    * @param bool $includeContainer
    * @return string
    */
    public static function currency($value, $format=true, $includeContainer = true)
    {
        try {
            $value = Mage::app()->getStore()->formatPrice($value, $includeContainer);
        }
        catch (Exception $e){
            $value = $e->getMessage();
        }
        return $value;
    }
}