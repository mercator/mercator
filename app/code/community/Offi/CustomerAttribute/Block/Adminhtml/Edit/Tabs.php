<?php

class Offi_CustomerAttribute_Block_Adminhtml_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerattribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Attribute Information'));
    }

    protected function _beforeToHtml()
    {
        $model = Mage::registry('entity_attribute');
        
        $this->addTab('main', array(
            'label'     => Mage::helper('catalog')->__('Properties'),
            'title'     => Mage::helper('catalog')->__('Properties'),
            'content'   => $this->getLayout()->createBlock('customerattribute/adminhtml_edit_tab_main')->toHtml(),
            'active'    => true
        ));


        $this->addTab('labels', array(
            'label'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'title'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'content'   => $this->getLayout()->createBlock('customerattribute/adminhtml_edit_tab_options')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }

}
