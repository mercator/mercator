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
class Unirgy_Giftcert_Block_Adminhtml_Cert_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $cert = Mage::registry('giftcert_data');
        $hlp = Mage::helper('ugiftcert');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('giftcert_form', array(
            'legend'=>$hlp->__('Gift Certificate Info')
        ));

        $fieldset->addField('cert_number', 'text', array(
            'name'      => 'cert_number',
            'label'     => $hlp->__('Certificate Code'),
            'class'     => 'required-entry',
            'required'  => true,
            'note'      => $hlp->__('Enter a known certificate code or a pattern to autogenerate. Pattern examples:<br/><strong>[A*8] - 8 alpha chars<br/>[N*4] - 4 numerics<br/>[AN*5] - 5 alphanumeric<br/>CERT-[A*4]-[AN*6] - CERT-HQNB-8A1NO3</strong>'),
            'value'     => $id ? null : Mage::getStoreConfig('ugiftcert/default/cert_number'),
        ));
        
        $fieldset->addField('pos_number', 'text', array(
            'name'      => 'pos_number',
            'label'     => $hlp->__('POS Number'),
            'required'  => false,
        ));

        $fieldset->addField('balance', 'text', array(
            'name'      => 'balance',
            'label'     => $hlp->__('Balance'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('currency_code', 'select', array(
            'name'      => 'currency_code',
            'label'     => $hlp->__('Currency'),
            'class'     => 'required-entry',
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_currency')->toOptionArray(false),
            'value'     => Mage::app()->getStore()->getDefaultCurrency()->getCurrencyCode(),
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status1',
            'label'     => $hlp->__('Status'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => array(
                'P' => $hlp->__('Pending'),
                'A' => $hlp->__('Active'),
                'I' => $hlp->__('Inactive'),
            ),
        ));

        $arr = array(
            'name'   => 'expire_at',
            'label'  => $hlp->__('Expire On'),
            'title'  => $hlp->__('Expire On'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        );
        if (version_compare(Mage::getVersion(), '1.1.8', '>=')) {
            $arr['input_format'] = Varien_Date::DATE_INTERNAL_FORMAT;
        }
        $expireAt = $fieldset->addField('expire_at', 'date', $arr);

        if (!$id) {
            $fieldset->addField('order_increment_id', 'text', array(
                'name'      => 'order_increment_id',
                'label'     => $hlp->__('Order ID'),
            ));
        }

        if (Mage::getStoreConfig('ugiftcert/default/use_pin')) {
            $fieldset->addField('pin', 'text', array(
                'name'      => 'pin',
                'label'     => $hlp->__('PIN'),
                'note'      => $hlp->__('Accepts random patters same as Certificate Number'),
                'value'     => $id ? null : Mage::getStoreConfig('ugiftcert/default/pin'),
            ));
        }

        if (!$id) {
            $fieldset->addField('qty', 'text', array(
                'name'      => 'qty',
                'label'     => $hlp->__('Quantity of certificates to create'),
                'note'      => $hlp->__('If empty, only one certificate will be created'),
            ));

            $fieldset->addField('comments', 'textarea', array(
                'name'      => 'comments',
                'label'     => $hlp->__('Comments'),
            ));
        } else {
            $fieldset->addField('comments', 'textarea', array(
                'name'      => 'comments',
                'label'     => $hlp->__('Update Comments'),
                'style'     => 'height:70px',
                #'class'     => 'required-entry',
                #'required'  => true,
                'note'      => $hlp->__('For any update please enter your comments and reasons here'),
            ));
        }

        $fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => Mage::helper('core')->__('Store View'),
            'title'     => Mage::helper('core')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));

        $fieldset->addField('sender_name', 'text', array(
            'name'      => 'sender_name',
            'label'     => $hlp->__('Sender name'),
        ));

        $fieldset = $form->addFieldset('recipient_form', array(
            'legend'=>$hlp->__('Recipient Info')
        ));

        if ($id) {
            $fieldset->addField('toself_printed', 'select', array(
                'name'      => 'toself_printed',
                'label'     => $hlp->__('If sent to self:'),
                'options'   => array(
                    0 => $hlp->__('By email only'),
                    1 => $hlp->__('Requested a printed copy'),
                ),
            ));
        }

        $fieldset->addField('recipient_name', 'text', array(
            'name'      => 'recipient_name',
            'label'     => $hlp->__('Name'),
        ));

        $fieldset->addField('recipient_email', 'text', array(
            'name'      => 'recipient_email',
            'label'     => $hlp->__('Email'),
        ));

        $fieldset->addField('recipient_address', 'textarea', array(
            'name'      => 'recipient_address',
            'label'     => $hlp->__('Postal Address'),
            'style'     => 'height:70px',
        ));

        $fieldset->addField('recipient_message', 'textarea', array(
            'name'      => 'recipient_message',
            'label'     => $hlp->__('Custom Message'),
            'style'     => 'height:70px',
        ));

        if (Mage::registry('giftcert_data')) {
            $form->setValues(Mage::registry('giftcert_data')->getData());
        }

        return parent::_prepareForm();
    }
}