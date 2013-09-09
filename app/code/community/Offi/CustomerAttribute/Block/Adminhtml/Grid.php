<?php

class Offi_CustomerAttribute_Block_Adminhtml_Grid  extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();      
      $this->setId('customerattributegrid');
      $this->setDefaultSort('attribute_code');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
      $this->setTemplate('customerattribute/grid.phtml');
  }

  protected function _prepareCollection()
  {      
      $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType('customer')->getTypeId() )
            ->addFilter("is_visible", 1);
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
      
  }

  protected function _prepareColumns()
  {
      $this->addColumn('attribute_code', array(
            'header'=>Mage::helper('catalog')->__('Attribute Code'),
            'sortable'=>true,
            'index'=>'attribute_code'
        ));

        $this->addColumn('frontend_label', array(
            'header'=>Mage::helper('catalog')->__('Attribute Label'),
            'sortable'=>true,
            'index'=>'frontend_label'
        ));

        

        $this->addColumn('is_required', array(
            'header'=>Mage::helper('catalog')->__('Required'),
            'sortable'=>true,
            'index'=>'is_required',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));

        $this->addColumn('is_user_defined', array(
            'header'=>Mage::helper('catalog')->__('System'),
            'sortable'=>true,
            'index'=>'is_user_defined',
            'type' => 'options',
            'align' => 'center',
            'options' => array(
                '0' => Mage::helper('catalog')->__('Yes'),   // intended reverted use
                '1' => Mage::helper('catalog')->__('No'),    // intended reverted use
            ),
        ));

		
		$this->addExportType('*/*/exportCsv', Mage::helper('customerattribute')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('customerattribute')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  public function addNewButton(){
  	return $this->getButtonHtml(
  		Mage::helper('customerattribute')->__('New Attribute'), //label
  		"setLocation('".$this->getUrl('*/*/new')."')", //url
  		"scalable add" //classe css
  		);
  }
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('attribute_id' => $row->getAttributeId()));
  }

}

?>