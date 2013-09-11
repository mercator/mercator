<?php

class JR_CleverCms_Adminhtml_Cms_Page_PermissionsController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('cms/page_permissions')
            ->_addBreadcrumb(Mage::helper('cms')->__('CMS'), Mage::helper('cms')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('cms')->__('Manage Pages Permissions'), Mage::helper('cms')->__('Manage Pages Permissions'));

        return $this;
    }

    public function preDispatch()
    {
        parent::preDispatch();
        Mage::getDesign()->setTheme('clever');

        return $this;
    }

    public function indexAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        $groupId = $this->getRequest()->getParam('group');
        if (null === $storeId || null === $groupId) {
            if (null === $storeId) {
                $storeId = Mage::getSingleton('admin/session')->getCmsLastViewedStore();
                if (null === $storeId) {
                    if (Mage::app()->isSingleStoreMode()) {
                        $storeId = Mage::app()->getDefaultStoreView()->getId();
                    } else {
                        $storeId = 0;
                    }
                }
            }
            if (! $groupId) {
                $groupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
            }
            $this->_redirect('*/*/', array('store' => $storeId, 'group' => $groupId));
            return;
        }

        Mage::getSingleton('admin/session')
            ->setCmsLastViewedStore($storeId);

        if (! Mage::getStoreConfigFlag('cms/clever/permissions_enabled')) {
            $this->_getSession()->addNotice($this->__('Permissions are currently disabled. To enable permissions, go to System > Configuration > Content Management > Clever CMS.'));
        }

        $this->_title($this->__('CMS'))
             ->_title($this->__('Pages'))
             ->_title($this->__('Manage Content Permissions'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function saveAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        $customerGroupId = $this->getRequest()->getParam('group');
        $pages = explode(',', $this->getRequest()->getPost('pages'));

        try {
            if (null === $storeId || null === $customerGroupId) {
                Mage::throwException($this->__('An error occurred while saving permissions.'));
            }
            Mage::getResourceModel('cms/page_permission')->savePermissions($storeId, $customerGroupId, $pages);
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Permissions have been successfully saved.'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/', array('store' => $storeId, 'group' => $customerGroupId));
        return;
    }
}