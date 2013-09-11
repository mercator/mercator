<?php

class JR_CleverCms_Block_Page_Html_Topmenu extends JR_CleverCms_Block_Catalog_Navigation
{
    protected function _construct()
    {
        parent::_construct();
        if ($this->_canApplyCleverTheme()) {
            Mage::getDesign()->setTheme('clever');
        }
    }

    protected function _canApplyCleverTheme()
    {
        return Mage::getDesign()->getPackageName() == 'default'
            && Mage::getDesign()->getTheme('template') == 'default';
    }
}