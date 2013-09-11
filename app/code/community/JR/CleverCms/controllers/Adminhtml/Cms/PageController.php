<?php

require_once 'Mage/Adminhtml/controllers/Cms/PageController.php';

class JR_CleverCms_Adminhtml_Cms_PageController extends Mage_Adminhtml_Cms_PageController
{
    public function preDispatch()
    {
        parent::preDispatch();
        Mage::getDesign()->setTheme('clever');

        return $this;
    }

    protected function _initPage()
    {
        $pageId = (int) $this->getRequest()->getParam('id');
        $page = Mage::getModel('cms/page')->load($pageId);
        Mage::register('cms_page', $page);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));

        return $page;
    }

    public function indexAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        if (null === $storeId) {
            $storeId = Mage::getSingleton('admin/session')->getCmsLastViewedStore();
            if (null === $storeId) {
                if (Mage::app()->isSingleStoreMode()) {
                    $storeId = Mage::app()->getDefaultStoreView()->getId();
                } else {
                    $storeId = 0;
                }
            }
            $this->_redirect('*/*/', array('store' => $storeId));
            return;
        }

        if ($pageId = Mage::getSingleton('admin/session')->getLastEditedPage()) {
            $page = Mage::getModel('cms/page')->load($pageId);
            if ($page->getId()) {
                Mage::register('cms_page', $page);
            }
        }

        $this->_title($this->__('CMS'))
             ->_title($this->__('Pages'))
             ->_title($this->__('Manage Content'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function addAction()
    {
        Mage::getSingleton('admin/session')
            ->unsLastEditedPage();
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('CMS'))
             ->_title($this->__('Pages'))
             ->_title($this->__('Manage Content'));

        // 1. Get ID and create model
        $storeId = (int) $this->getRequest()->getParam('store');
        $parentId = (int) $this->getRequest()->getParam('parent');
        $id = (int) $this->getRequest()->getParam('id');

        if ($storeId && !$id && !$parentId) {
            $store = Mage::app()->getStore($storeId);
        }

        $page = Mage::getModel('cms/page');

        // 2. Initial checking
        if ($id) {
            $page->load($id);
            if (! $page->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('This page no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $page->setStoreId($storeId);
            if ($storeId === 0 && ! Mage::app()->isSingleStoreMode()) {
                $page->setStores(array_keys(Mage::app()->getStores()));
            }
        }

        $this->_title($page->getId() ? $page->getTitle() : $this->__('New Page'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $page->setData($data);
        }

        Mage::getSingleton('admin/session')
            ->setCmsLastViewedStore($storeId);
        Mage::getSingleton('admin/session')
            ->setLastEditedPage($page->getId());

        // 4. Register model to use later in blocks
        Mage::register('cms_page', $page);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('cms')->__('Edit Page') : Mage::helper('cms')->__('New Page'), $id ? Mage::helper('cms')->__('Edit Page') : Mage::helper('cms')->__('New Page'));

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getLayout()->getBlock('root')->setTemplate('cms/page/ajax-edit.phtml');
        }

        $this->renderLayout();
    }

    public function treeAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $pageId = (int) $this->getRequest()->getParam('id');

        if ($storeId) {
            if (!$pageId) {
                $rootId = Mage::getResourceModel('cms/page')->getStoreRootId($storeId);
                $this->getRequest()->setParam('id', $rootId);
            }
            Mage::getSingleton('admin/session')
                ->setCmsLastViewedStore($storeId);
        }

        $page = $this->_initPage();
        $block = $this->getLayout()->createBlock('jr_clevercms/adminhtml_cms_page_tree');
        $root = $block->getRoot();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'data' => $block->getTree(),
            'parameters' => array(
                'text'        => $block->buildNodeName($root),
                'draggable'   => false,
                'allowDrop'   => ($root->getIsVisible()) ? true : false,
                'id'          => (int) $root->getId(),
                'expanded'    => (int) $block->getIsWasExpanded(),
                'store_id'    => (int) $block->getStore()->getId(),
                'page_id'     => (int) $page->getId(),
                'root_visible'=> (int) $root->getIsVisible()
        ))));
    }

    public function moveAction()
    {
        $pageId = (int) $this->getRequest()->getParam('id');
        $page = $this->_initPage();
        if (!$page) {
            $this->getResponse()->setBody(Mage::helper('cms')->__('Page move error'));
            return;
        }
        /**
         * New parent page identifier
         */
        $parentNodeId   = $this->getRequest()->getPost('pid', false);
        /**
         * Page id after which we have put our page
         */
        $prevNodeId     = $this->getRequest()->getPost('aid', false);

        try {
            $page->move($parentNodeId, $prevNodeId);
            $this->getResponse()->setBody("SUCCESS");
        }
        catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
        catch (Exception $e){
            $this->getResponse()->setBody(Mage::helper('cms')->__('Page move error'.$e));
            Mage::logException($e);
        }
    }

    public function pagesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($pageId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $pageId);

            if (!$page = $this->_initPage()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('jr_clevercms/adminhtml_cms_page_tree')
                    ->getTreeJson($page)
            );
        }
    }

    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            $data = $this->_filterPostData($data);
            //init model and set data
            $model = Mage::getModel('cms/page');

            if ($id = $this->getRequest()->getParam('page_id')) {
                $model->load($id);
            }

            $data['parent_id'] = $this->getRequest()->getParam('parent');
            $data['store_id'] = $this->getRequest()->getParam('store');

            if (!isset($data['stores'])) {
                $data['stores'] = array();
            }

            $model->addData($data);

            Mage::dispatchEvent('cms_page_prepare_save', array('page' => $model, 'request' => $this->getRequest()));

            // try to save it
            try {
                // save the data
                $model->save();

                Mage::getSingleton('admin/session')
                    ->setLastEditedPage($model->getId());

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('The page has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('cms')->__('An error occurred while saving the page.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/index', array('store' => $data['store_id']));
            return;
        }
        $this->_redirect('*/*/');
    }

    public function switchAction()
    {
        Mage::getSingleton('admin/session')
            ->unsCmsLastViewedStore()
            ->unsLastEditedPage();
        $this->_forward('index');
    }
}