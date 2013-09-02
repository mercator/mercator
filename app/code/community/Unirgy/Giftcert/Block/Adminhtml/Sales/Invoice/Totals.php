<?php

class Unirgy_Giftcert_Block_Adminhtml_Sales_Invoice_Totals
    extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals
{
    protected function _initTotals()
    {
        parent::_initTotals();

        if (((float)$this->getSource()->getGiftcertAmount()) != 0) {
            if ($this->getSource()->getGiftcertCode()) {
                $giftcertLabel = $this->helper('sales')->__('Gift Certificates (%s)', $this->getSource()->getGiftcertCode());
            } else {
                $giftcertLabel = $this->helper('sales')->__('Gift Certificates');
            }
            $this->_totals['giftcert'] = new Varien_Object(array(
                'code'      => 'giftcert',
                'value'     => -$this->getSource()->getGiftcertAmount(),
                'base_value'=> -$this->getSource()->getBaseGiftcertAmount(),
                'label'     => $giftcertLabel
            ));
        }
    }
}