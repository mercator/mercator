<?php

class Offi_CustomerAttribute_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    protected function _prepareLayout() {

        //$this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_mode . '_form'));
        return parent::_prepareLayout();
    }

    public function __construct() {

        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'customerattribute';
        parent::__construct();

        if ($this->getRequest()->getParam('popup')) {
            $this->_removeButton('back');
            $this->_addButton(
                    'close', array(
                'label' => Mage::helper('catalog')->__('Close Window'),
                'class' => 'cancel',
                'onclick' => 'window.close()',
                'level' => -1
                    )
            );
        }

        $this->_updateButton('save', 'label', Mage::helper('catalog')->__('Save Attribute'));

        if (!Mage::registry('entity_attribute')->getIsUserDefined()) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', Mage::helper('catalog')->__('Delete Attribute'));
            $this->_updateButton('delete', 'onclick', "deleteConfirm(
            		'" . Mage::helper('adminhtml')->__('Are you sure you want to do this?') . "',
            		'" . $this->getUrl('*/*/delete/type/' . $this->getRequest()->getParam('type') . '/attribute_id/' . $this->getRequest()->getParam('attribute_id')
                    ) . "')");
        }
    }

    public function getHeaderText() {
        if (Mage::registry('entity_attribute')->getId()) {
            return Mage::helper('customerattribute')->__('Edit %s Attribute "%s"', 'Customer', $this->htmlEscape(Mage::registry('entity_attribute')->getFrontendLabel()));
        } else {
            return Mage::helper('customerattribute')->__('New %s Attribute', 'Customer');
        }
    }

    public function getValidationUrl() {
        return $this->getUrl('*/*/validate', array('_current' => true));
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save', array('_current' => true, 'back' => null));
    }

}
