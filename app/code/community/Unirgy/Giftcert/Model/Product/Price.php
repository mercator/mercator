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
class Unirgy_GiftCert_Model_Product_Price extends Mage_Catalog_Model_Product_Type_Price
{
    public function getPrice($product)
    {
        $amountConfig = Mage::helper('ugiftcert')->getAmountConfig($product);
        switch ($amountConfig['type']) {
        case 'range':
            $price = $amountConfig['from'];
            break;
        case 'dropdown':
            $o = $amountConfig['options'];
            $price = $o[0] ? $o[0] : $o[1];
            break;
        case 'fixed':
            $price = $amountConfig['amount'];
            break;
        default:
            $price = 0;
        }
        return $price;
    }

    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        if ($amountOption = $product->getCustomOption('amount')) {
            $finalPrice = $amountOption->getValue();
        }
        return $finalPrice;
    }
}
