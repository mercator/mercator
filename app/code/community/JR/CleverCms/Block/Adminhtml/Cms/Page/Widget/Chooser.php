<?php

class JR_CleverCms_Block_Adminhtml_Cms_Page_Widget_Chooser extends Mage_Adminhtml_Block_Cms_Page_Widget_Chooser
{
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $tree = Mage::getResourceModel('cms/page_tree')->load();
        $element->setData('after_element_html', $tree->toSelectHtml($element->getName(), $element->getValue(), $element->getId()));
        $element->setValue(); // Not needed because page is already selected in select box

        return $element;
    }
}