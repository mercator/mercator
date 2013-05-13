<?php
/**
 * Fontis Blog Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Parts of this software are derived from code originally developed by
 * Robert Chambers <magento@robertchambers.co.uk>
 * and released as "Lazzymonk's Blog" 0.5.8 in 2009.
 *
 * @category   Fontis
 * @package    Fontis_Blog
 * @copyright  Copyright (c) 2013 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Blog_Manage_Blog_CatController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('blog/cat')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Category Manager'), Mage::helper('adminhtml')->__('Category Manager'));
        
        return $this;
    }   
 
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
 
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('blog/cat');
                $model->setId($this->getRequest()->getParam('id'))->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Category was successfully deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $blogIds = $this->getRequest()->getParam('blog');
        if (!is_array($blogIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Please select categories'));
        } else {
            try {
                foreach ($blogIds as $blogId) {
                    $blog = Mage::getModel('blog/cat')->load($blogId);
                    $blog->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d categories were successfully deleted', count($blogIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function editAction()
    {
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("blog/cat")->load($id);

        if ($model->getId() || $id == 0) {
            $this->_renderCatEditPage($model);
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("blog")->__("Category does not exist."));
            $this->_redirect("*/*/");
        }
    }
    
    public function newAction() 
    {
        $id     = $this->getRequest()->getParam("id");
        $model  = Mage::getModel("blog/cat")->load($id);

        $this->_renderCatEditPage($model);
    }

    protected function _renderCatEditPage($model)
    {
        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register("blog_data", $model);

        $this->loadLayout();
        $this->_setActiveMenu("blog/cat");

        $blogHelper = Mage::helper("blog");
        $this->_addBreadcrumb($text = $blogHelper->__("Blog Manager"), $text);
        $this->_addBreadcrumb($text = $blogHelper->__("Category Manager"), $text);

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock("blog/manage_cat_edit"));

        $this->renderLayout();
    }
    
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (!$data["sort_order"]) {
                // If no sort order was specified, automatically make it one more than the current last
                $conn = Mage::getSingleton("core/resource")->getConnection("core_read");
                $maxSortOrder = $conn->select()
                    ->from(Mage::getResourceModel("blog/cat")->getMainTable(), array(new Zend_Db_Expr('max(sort_order)')));
                $data["sort_order"] = (int) $conn->fetchOne($maxSortOrder) + 1;
            }

            $model = Mage::getModel('blog/cat');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Category was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find category to save'));
        $this->_redirect('*/*/');
    }
}
