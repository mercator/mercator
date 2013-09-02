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
class Unirgy_Giftcert_Block_Product_Type extends Mage_Catalog_Block_Product_View_Abstract
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::getStoreConfig('ugiftcert/custom/message_preview')) {
            $media = $this->getLayout()->getBlock('product.info.media');
            if ($media) {
                $media->setTemplate('unirgy/giftcert/product/media.phtml');
            }
        }
    }

    /**
    * @deprecated since 0.8.2 for getAmountConfig()
    */
    public function getAmountRangeFrom($fromAttr='giftcert_amount_from')
    {
        $from = $this->getProduct()->getDataUsingMethod($fromAttr);
        if (!$from) {
            $from = Mage::getStoreConfig('ugiftcert/custom/amount_from');
        }
        return $from;
    }

    /**
    * @deprecated since 0.8.2 for getAmountConfig()
    */
    public function getAmountRangeTo($toAttr='giftcert_amount_to')
    {
        $to = $this->getProduct()->getDataUsingMethod($toAttr);
        if (!$to) {
            $to = Mage::getStoreConfig('ugiftcert/custom/amount_to');
        }
        return $to;
    }

    public function getAmountConfig($attr='ugiftcert_amount_config')
    {
        return Mage::helper('ugiftcert')->getAmountConfig($this->getProduct(), $attr);
    }

    public function getAllowEmail()
    {
        return Mage::getStoreConfig('ugiftcert/email/enabled');
    }

    public function getAllowAddress()
    {
        return Mage::getStoreConfig('ugiftcert/address/enabled');
    }

    public function getAllowMessage()
    {
        return Mage::getStoreConfig('ugiftcert/custom/allow_message');
    }

    public function getMessageMaxLength()
    {
        $len = Mage::getStoreConfig('ugiftcert/custom/message_max_length');
        return $len;
    }
}
