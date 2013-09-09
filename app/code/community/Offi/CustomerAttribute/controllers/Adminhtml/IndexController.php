<?php

class Offi_CustomerAttribute_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action {

    protected $_entityTypeId;

    public function preDispatch() {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('Customer')->getTypeId();
    }

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('customer/customer')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Customer Attributes Manager'), Mage::helper('adminhtml')->__('Customer Attributes Manager'));
        return $this;
    }

    public function indexAction() {
        $this->_initAction()->_addContent($this->getLayout()->createBlock('customerattribute/adminhtml_Grid'));
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = Mage::getModel('eav/entity_attribute');
        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('customerattribute')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('customerattribute')->__('This attribute cannot be edited.'));
                $this->_redirect('*/*/');
                return;
            }
            
            //get Used in forms
            $config = Mage::getSingleton('eav/config')
                            ->getAttribute('customer', $model->getAttributeCode());        
            $model->addData(array('used_in_forms'=>$config->getUsedInForms()));
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getAttributeData(true);
        
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('entity_attribute', $model);

        $this->_initAction();

        $this->_title($id ? $model->getName() : $this->__('New Attribute'));

        $item = $id ? Mage::helper('customerattribute')->__('Edit Customer Attribute') : Mage::helper('customerattribute')->__('New Customer Attribute');

        $this->_addBreadcrumb($item, $item);

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this
                ->_addContent($this->getLayout()->createBlock('customerattribute/adminhtml_edit'))
                ->_addLeft($this->getLayout()->createBlock('customerattribute/adminhtml_edit_tabs'))
        ;



        $this->renderLayout();
    }

    public function validateAction() { 
        $response = new Varien_Object();
        $response->setError(false);
        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('attribute_id');        
        $attribute = Mage::getModel('eav/entity_attribute')
                ->loadByCode($this->_entityTypeId, $attributeCode);


        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('customerattribute')->__('Attribute with the same code already exists'));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }
    
    public function saveAction() {

        $data = $this->getRequest()->getPost();
        if ($data) {
            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('adminhtml/session');

            $redirectBack   = $this->getRequest()->getParam('back', false);
            /* @var $model Mage_Catalog_Model_Entity_Attribute */
            $model = Mage::getModel('customerattribute/customerattribute');
            /* @var $helper Mage_Catalog_Helper_Product */
            $helper = Mage::helper('customerattribute');

            $id = $this->getRequest()->getParam('attribute_id');

            //validate attribute_code
            if (isset($data['attribute_code'])) {
                $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z_]{1,255}$/'));
                if (!$validatorAttrCode->isValid($data['attribute_code'])) {
                    $session->addError(
                        $helper->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));
                    $this->_redirect('*/*/edit', array('attribute_id' => $id, '_current' => true));
                    return;
                }
            }


            //validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var $validatorInputType Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator */
                $validatorInputType = Mage::getModel('eav/adminhtml_system_config_source_inputtype_validator');
                if (!$validatorInputType->isValid($data['frontend_input'])) {
                    foreach ($validatorInputType->getMessages() as $message) {
                        $session->addError($message);
                    }
                    $this->_redirect('*/*/edit', array('attribute_id' => $id, '_current' => true));
                    return;
                }
            }

            if ($id) {
                $model->load($id);

                if (!$model->getId()) {
                    $session->addError(
                        Mage::helper('customerattribute')->__('This Attribute no longer exists'));
                    $this->_redirect('*/*/');
                    return;
                }

                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $session->addError(
                        Mage::helper('customerattribute')->__('This attribute cannot be updated.'));
                    $session->setAttributeData($data);
                    $this->_redirect('*/*/');
                    return;
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                /**
                * @todo add to helper and specify all relations for properties
                */
                $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
                $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
            }

            if (!isset($data['is_configurable'])) {
                $data['is_configurable'] = 0;
            }
            if (!isset($data['is_filterable'])) {
                $data['is_filterable'] = 0;
            }
            if (!isset($data['is_filterable_in_search'])) {
                $data['is_filterable_in_search'] = 0;
            }

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if(!isset($data['apply_to'])) {
                $data['apply_to'] = array();
            }

            //filter
            $data = $this->_filterPostData($data);

            $model->addData($data);

            if (!$id) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }


            if ($this->getRequest()->getParam('set') && $this->getRequest()->getParam('group')) {
                // For creating product attribute on product page we need specify attribute set and group
                $model->setAttributeSetId($this->getRequest()->getParam('set'));
                $model->setAttributeGroupId($this->getRequest()->getParam('group'));
            }

            try {
                $model->save();
                $used_in_forms = $this->getRequest()->getParam('used_in_forms');
                if($model->getId() && is_array($used_in_forms))
                {                    
                    Mage::getSingleton('eav/config')
                            ->getAttribute('customer', $model->getAttributeCode())
                            ->setData('used_in_forms', $used_in_forms)                            
                            ->save();
                    
                }
                $session->addSuccess(
                    Mage::helper('customerattribute')->__('The product attribute has been saved.'));

                /**
                 * Clear translation cache because attribute labels are stored in translation
                 */
                Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                $session->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $this->_redirect('adminhtml/catalog_product/addAttribute', array(
                        'id'       => $this->getRequest()->getParam('product'),
                        'attribute'=> $model->getId(),
                        '_current' => true
                    ));
                } elseif ($redirectBack) {
                    $this->_redirect('*/*/edit', array('attribute_id' => $model->getId(),'_current'=>true));
                } else {
                    $this->_redirect('*/*/', array());
                }
                return;
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $session->setAttributeData($data);
                $this->_redirect('*/*/edit', array('attribute_id' => $id, '_current' => true));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        if ($data) {
            /** @var $helperCatalog Mage_Catalog_Helper_Data */
            $helperCatalog = Mage::helper('customerattribute');
            //labels
            foreach ($data['frontend_label'] as & $value) {
                if ($value) {
                    $value = $helperCatalog->stripTags($value);
                }
            }
            //options
            if (!empty($data['option']['value'])) {
                foreach ($data['option']['value'] as &$options) {
                    foreach ($options as &$label) {
                        $label = $helperCatalog->stripTags($label);
                    }
                }
            }
            //default value
            if (!empty($data['default_value'])) {
                $data['default_value'] = $helperCatalog->stripTags($data['default_value']);
            }
            if (!empty($data['default_value_text'])) {
                $data['default_value_text'] = $helperCatalog->stripTags($data['default_value_text']);
            }
            if (!empty($data['default_value_textarea'])) {
                $data['default_value_textarea'] = $helperCatalog->stripTags($data['default_value_textarea']);
            }
        }
        return $data;
    }
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('attribute_id')) {
            $model = Mage::getModel('customerattribute/customerattribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('customerattribute')->__('This attribute cannot be deleted.'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('customerattribute')->__('The product attribute has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('customerattribute')->__('Unable to find an attribute to delete.'));
        $this->_redirect('*/*/');
    }
}