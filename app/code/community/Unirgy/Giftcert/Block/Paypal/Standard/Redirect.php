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
class Unirgy_Giftcert_Block_Paypal_Standard_Redirect extends Mage_Core_Block_Abstract
{
    /**
    * I prefer to overload block than model
    * for less compatibility issues with other extensions
    *
    */
    protected function _toHtml()
    {
        $standard = Mage::getModel('paypal/standard');

        $form = new Varien_Data_Form();
        $actionUrl = $standard->getConfig() ? $standard->getConfig()->getPaypalUrl() : $standard->getPaypalUrl();
        $form->setAction($actionUrl)
            ->setId('paypal_standard_checkout')
            ->setName('paypal_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);

        $fields = $standard->getStandardCheckoutFormFields();
        if (!empty($fields['amount']) && !empty($fields['invoice'])) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($fields['invoice']);
            $fields['amount'] = max(0, $fields['amount']-$order->getBaseGiftcertAmount());
        }

        foreach ($fields as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Paypal in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("paypal_standard_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}