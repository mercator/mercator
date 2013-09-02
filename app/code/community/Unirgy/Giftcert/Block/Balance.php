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
class Unirgy_Giftcert_Block_Balance extends Mage_Core_Block_Template
{
    public function getCert()
    {
        if (!$this->hasData('cert')) {
            $certNumber = strtoupper($this->getRequest()->getParam('cert_number'));
            if (!$certNumber) {
                return null;
            }
            $cert = Mage::getModel('ugiftcert/cert')->load($certNumber, 'cert_number');

            $pin = strtoupper($this->getRequest()->getParam('pin'));
            $cert = Mage::getModel('ugiftcert/cert')->load($certNumber, 'cert_number');
            if ($cert->getId() && (!Mage::getStoreConfig('ugiftcert/default/use_pin') || $cert->getPin()==$pin)) {
                $this->setData('cert', $cert);
            } else {
                $this->setData('cert', false);
            }
        }
        return $this->getData('cert');
    }

    public function getCertNumberMasked($showLast=4)
    {
        $num = $this->getCert()->getCertNumber();
        return str_pad('', strlen($num)-$showLast, 'X').' '.substr($num, -$showLast);

        // maybe for future
        $parts = explode('-', $num);
        if (sizeof($parts)==1) {
            return str_pad('', strlen($num)-$showLast, 'X').' '.substr($num, -$showLast);
        }
        for ($i = 0, $l = sizeof($parts)-1; $i < $l; $i++) {
            $parts[$i] = str_pad('', strlen($parts[$i]), 'X');
        }
        return join('-', $parts);
    }

    /**
    * Get formated balance amount for current currency
    *
    * @return string
    */
    public function getBalance()
    {
        $store = Mage::app()->getStore();
        if ($store->getCurrentCurrencyCode()===$this->getCert()->getCurrencyCode()) {
            return $store->formatPrice($this->getCert()->getBalance(), true, false);
        } else {
            return $store->convertPrice($this->getCert()->getBaseBalance(), true, false);
        }
    }
}
