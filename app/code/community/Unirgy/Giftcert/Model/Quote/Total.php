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
class Unirgy_Giftcert_Model_Quote_Total extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('giftcert');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $address->setGiftcertAmount(0);
        $address->setBaseGiftcertAmount(0);

        $quote = $address->getQuote();
        $addressType = $address->getAddressТype();
        if ($addressType=='billing' && !$quote->isVirtual()) {
            return $this;
        }

        if (!$quote->getGiftcertCode()) {
            return $this;
        }

        $store = $address->getQuote()->getStore();
        $cert = Mage::getModel('ugiftcert/cert');

        $certCodes = array_unique(preg_split('#\s*,\s*#', $quote->getGiftcertCode()));
        $finalCertCodes = array();
        $baseBalances = array();
        $balances = array();

        $totalBaseAmount = 0;
        $totalLocalAmount = 0;

        if ($address->getAllBaseTotalAmounts()) {
            $baseTotal = array_sum($address->getAllBaseTotalAmounts());
        } else {
            $baseTotal = $address->getBaseGrandTotal();
        }

        foreach ($certCodes as $certCode) {
            $cert->load($certCode, 'cert_number');
            if (!$cert->getId() || $cert->getStatus()!='A') {
                continue; // not found or not active
            }

            if (!($balance = $cert->getBalance())) {
                continue; // no funds
            }
            $finalCertCodes[] = $cert->getCertNumber();

            if ($baseTotal == 0) {
                continue;
            }

#Mage::log($cert->getBalance().','.$cert->getBaseBalance().','.$baseTotal);
            $baseAmount = min($cert->getBaseBalance(), $baseTotal);
            $totalBaseAmount += $baseAmount;
            $baseTotal -= $baseAmount;

            $baseBalances[] = $baseAmount;
            $balances[] = $store->convertPrice($baseAmount, false);
#Mage::log($baseAmount.','.$totalBaseAmount.','.$baseTotal);
        }

        $quote->setGiftcertCode(join(', ', $finalCertCodes));
        $quote->setBaseGiftcertBalances(join(',', $baseBalances));
        $quote->setGiftcertBalances(join(',', $balances));

        $address->setBaseGiftcertAmount($totalBaseAmount);
        $address->setGiftcertAmount($store->convertPrice($totalBaseAmount, false));

        if (version_compare(Mage::getVersion(), '1.4', '>=')) {
            $this->_addAmount(-$address->getGiftcertAmount());
            $this->_addBaseAmount(-$address->getBaseGiftcertAmount());
        } else {
            $address->setBaseGrandTotal($baseTotal);
            $address->setGrandTotal($store->convertPrice($baseTotal, false));
        }

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getGiftcertAmount();
        if ($amount!=0) {
            $quote = $address->getQuote();
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('ugiftcert')->__('Gift Certificates'),
                'value' => $amount,
                'giftcert_code' => $quote->getGiftcertCode(),
                'base_balances' => $quote->getBaseGiftcertBalances(),
                'balances' => $quote->getGiftcertBalances(),
            ));
        }
        return $this;
    }
}
