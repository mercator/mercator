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
 * This class is not used currently, because I'm trying to avoid overloading methods
 * to have as much compatibility with other community extensions as possible.
 *
 * But I'm keeping it anyway in case I'll fail...
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Model_Quote extends Mage_Sales_Model_Quote
{
    public function isVirtual()
    {
        $isVirtual = true;
        $countItems = 0;
        foreach ($this->getItemsCollection() as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            if ($item->isDeleted() || $item->getParentItemId()) {
                continue;
            }
            $countItems ++;

            // If ugiftcert, check by quote item, not by product
            if ($item->getProductType()=='ugiftcert') {
                if (!$item->getProduct()->getTypeInstance()->isQuoteItemVirtual($item)) {
                    $isVirtual = false;
                }
            } elseif (!$item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
            }
        }
        return $countItems == 0 ? false : $isVirtual;
    }

    public function merge(Mage_Sales_Model_Quote $quote)
    {
        parent::merge($quote);
        if ($quote->getGiftcertCode()) {
            $this->setGiftcertCode($quote->getGiftcertCode());
        }
        return $this;
    }
}