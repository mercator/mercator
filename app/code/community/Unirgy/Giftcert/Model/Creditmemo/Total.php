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
class Unirgy_Giftcert_Model_Creditmemo_Total extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $cm)
    {
        $order = $cm->getOrder();

        $baseGcAmount = $order->getBaseGiftcertAmount();
        $gcAmount = $order->getGiftcertAmount();

        $baseGcInvoiced = $order->getBaseGiftcertAmountInvoiced();
        $gcInvoiced = $order->getGiftcertAmountInvoiced();

        $baseGcCredited = $order->getBaseGiftcertAmountCredited();
        $gcCredited = $order->getGiftcertAmountCredited();

        $baseCmTotal = $cm->getBaseGrandTotal();
        $cmTotal = $cm->getGrandTotal();

        $baseGcBalance = $baseGcInvoiced-$baseGcCredited;

        if ($baseGcBalance<=0) {
            return $this;
        }

        if ($baseGcBalance >= $baseCmTotal) {
            $baseGcUsed = $baseCmTotal;
            $gcUsed = $cmTotal;
            $baseCmTotal = 0;
            $cmTotal = 0;
        } else {
            $baseGcUsed = $baseGcBalance;
            $gcUsed = $gcInvoiced-$gcCredited;
            $baseCmTotal -= $baseGcUsed;
            $cmTotal -= $gcUsed;
        }

        $cm->setBaseGrandTotal($baseCmTotal);
        $cm->setGrandTotal($cmTotal);

        $cm->setBaseGiftcertAmount($baseGcUsed);
        $cm->setGiftcertAmount($gcUsed);

        $order->setBaseGiftcertAmountCredited($baseGcCredited+$baseGcUsed);
        $order->setGiftcertAmountCredited($gcCredited+$gcUsed);

        return $this;
    }
}