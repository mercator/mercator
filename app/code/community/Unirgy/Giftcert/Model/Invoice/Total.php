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
class Unirgy_Giftcert_Model_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $baseGcAmount = $order->getBaseGiftcertAmount();
        $baseGcInvoiced = $order->getBaseGiftcertAmountInvoiced();
        $baseInvoiceTotal = $invoice->getBaseGrandTotal();
        $gcAmount = $order->getGiftcertAmount();
        $gcInvoiced = $order->getGiftcertAmountInvoiced();
        $invoiceTotal = $invoice->getGrandTotal();

        if (!$baseGcAmount || $baseGcInvoiced==$baseGcAmount) {
            return $this;
        }

        $baseGcBalance = $baseGcAmount-$baseGcInvoiced;

        if ($baseGcBalance >= $baseInvoiceTotal) {
            $baseGcUsed = $baseInvoiceTotal;
            $gcUsed = $invoiceTotal;
            $baseInvoiceTotal = 0;
            $invoiceTotal = 0;
        } else {
            $baseGcUsed = $baseGcBalance;
            $gcUsed = $gcAmount-$gcInvoiced;
            $baseInvoiceTotal -= $baseGcUsed;
            $invoiceTotal -= $gcUsed;
        }

        $invoice->setBaseGrandTotal($baseInvoiceTotal);
        $invoice->setGrandTotal($invoiceTotal);

        $invoice->setBaseGiftcertAmount($baseGcUsed);
        $invoice->setGiftcertAmount($gcUsed);

        $order->setBaseGiftcertAmountInvoiced($baseGcInvoiced+$baseGcUsed);
        $order->setGiftcertAmountInvoiced($gcInvoiced+$gcUsed);
/*
        $order->setBaseTotalDue($order->getBaseTotalDue()-$baseGcUsed);
        $order->setTotalDue($order->getTotalDue()-$gcUsed);
*/
        return $this;
    }
}