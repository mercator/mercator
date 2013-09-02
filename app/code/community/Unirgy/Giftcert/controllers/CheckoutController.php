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
class Unirgy_Giftcert_CheckoutController extends Mage_Core_Controller_Front_Action
{
    public function removeAction()
    {
        $gc = $this->getRequest()->getParam('gc');
        $gcs = Mage::getSingleton('checkout/session')->getQuote()->getGiftcertCode();
        if (!$gc || !$gcs || strpos($gcs, $gc)===false) {
            Mage::throwException('Invalid request.');
        }

        $gcsArr = array();
        foreach (explode(', ', $gcs) as $gc1) {
            if ($gc1!==$gc) {
                $gcsArr[] = $gc1;
            }
        }
        Mage::getSingleton('checkout/session')->getQuote()->setGiftcertCode(join(', ', $gcsArr))->save();

        $this->_redirect('checkout/cart');
    }
}
