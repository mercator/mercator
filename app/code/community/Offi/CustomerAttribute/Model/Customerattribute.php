<?php

class Offi_CustomerAttribute_Model_CustomerAttribute extends Mage_Eav_Model_Entity_Attribute
{
    public function _construct()
    {
        parent::_construct();
        
        
    }
    
 protected function _beforeSave()
    {
    	if ( $this->getFrontendInput()=="image"){
						$this->setBackendModel('catalog/category_attribute_backend_image');
						$this->setBackendType('varchar');
		}
		
    if ( $this->getFrontendInput()=="date"){
						$this->setBackendModel('eav/entity_attribute_backend_datetime');
						$this->setBackendType('datetime');
		}
		
    if ( $this->getFrontendInput()=="textarea" ){
						
						$this->setBackendType('text');
		}
		
     if ( $this->getFrontendInput()=="text" ){
						
						$this->setBackendType('varchar');
		}
		
    if ( ($this->getFrontendInput()=="multiselect" || $this->getFrontendInput()=="select") ){
						$this->setData('source_model', 'eav/entity_attribute_source_table');
						$this->setBackendType('varchar');
		}
		
		
		
    if ($this->getFrontendInput()=="boolean"){
						$this->setFrontendInput("select");
						$this->setBackendType('int');
						$this->setData('source_model', 'eav/entity_attribute_source_boolean');
						
					
		}

        return parent::_beforeSave();
    }
}
?>