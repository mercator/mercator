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
class Unirgy_Giftcert_Model_Cert extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ugiftcert/cert');
        parent::_construct();
    }

    public function getStatus()
    {
        if ($this->getData('status')=='A' && $this->getData('expire_at')) {
            if (strtotime($this->getData('expire_at'))<time()) {
                $this->setData('status', 'I');
                if ($this->getId()) {
                    $this->save();
                }
            }
        }
        return $this->getData('status');
    }

    public function getCurrencyRate()
    {
        if (!$this->hasData('currency_rate')) {
            $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            $rate = Mage::helper('directory')->currencyConvert(1, $baseCurrencyCode, $this->getCurrencyCode());
            $this->setData('currency_rate', $rate);
        }
        return $this->getData('currency_rate');
    }

    public function getBaseBalance()
    {
        return $this->getBalance()/$this->getCurrencyRate();
    }

    public function addHistory($data)
    {
        Mage::getModel('ugiftcert/history')->setCertId($this->getCertId())->addData($data)->save();
        return $this;
    }

    public function getHistory($actionCode=null)
    {
        $collection = Mage::getModel('ugiftcert/history')->getCollection()
            ->addCertFilter($this->getId());
        if (!is_null($actionCode)) {
            $collection->addActionFilter($actionCode);
        }
        return $collection;
    }

    public function addToQuote($quote=null)
    {
        if (is_null($quote)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        $codes = $quote->getGiftcertCode();
        if ($codes) {
            $c = array_map('trim', explode(',', $codes));
            $c[] = $this->getCertNumber();
            $codes = join(', ', array_unique($c));
        } else {
            $codes = $this->getCertNumber();
        }
        $quote->setGiftcertCode($codes);
        return $this;
    }

    public function _afterLoad()
    {
        parent::_afterLoad();
        $config = Mage::getStoreConfig('ugiftcert/default');
        if (!$this->getData('cert_number') && $config['auto_cert_number']) {
            $this->setData('cert_number', $config['cert_number']);
        }
        if ($config['use_pin'] && $config['auto_pin'] && !$this->getData('pin')) {
            $this->setData('pin', $config['pin']);
        }
    }

    public function _beforeSave()
    {
        parent::_beforeSave();

        $hlp = Mage::helper('ugiftcert');

        if ($hlp->isPattern($this->getData('cert_number'))) {
            $pattern = $this->getData('cert_number');
            $dup = Mage::getModel('ugiftcert/cert');
            $i = 0;
            while ($i++<10) { // 10 times can't find free slot - a problem
                $num = $hlp->processRandomPattern($pattern);
                $dup->unsetData()->load($num, 'cert_number');
                if (!$dup->getCertId()) {
                    break;
                }
                $num = false;
            }
            if ($num===false) {
                throw new Mage_Core_Exception('Exceeded maximum retries to find available random certificate number');
            }
            $this->setData('cert_number', $num);
        }

        if (Mage::getStoreConfig('ugiftcert/default/use_pin') && $hlp->isPattern($this->getData('pin'))) {
            $this->setData('pin', $hlp->processRandomPattern($this->getData('pin')));
        }

        if ($date = $this->getExpireAt()) {
            $locale = Mage::app()->getLocale();
            $format = $locale->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $dateObject = $locale->date($date, $format, null, false);
            $this->setExpireAt($dateObject->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        } else {
            $this->setExpireAt(null);
        }
    }
}
