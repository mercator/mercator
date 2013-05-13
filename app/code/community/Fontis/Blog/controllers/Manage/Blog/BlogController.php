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

class Fontis_Blog_Manage_Blog_BlogController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('blog/posts');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('blog/blog')->load($id);

        if ($model->getId() || $id == 0) {
            $this->_renderBlogEditPage($model);
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Post does not exist.'));
            $this->_redirect('*/*/');
        }
    }
 
    public function newAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('blog/blog')->load($id);

        $this->_renderBlogEditPage($model);
    }

    protected function _renderBlogEditPage($model)
    {
        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register("blog_data", $model);

        $this->loadLayout();
        $this->_setActiveMenu("blog/cat");

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('blog/manage_blog_edit'))
            ->_addLeft($this->getLayout()->createBlock('blog/manage_blog_edit_tabs'));

        $this->renderLayout();
    }
 
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('blog/post');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));
            
            try {
                $nowTime = now();
                if ($this->getRequest()->getParam('created_time') == NULL) {
                    $model->setCreatedTime($nowTime)->setUpdateTime($nowTime);
                } else {
                    $model->setUpdateTime($nowTime);
                }    

                $userString = Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton('admin/session')->getUser()->getLastname();
                if ($this->getRequest()->getParam('user') == NULL) {
                    $model->setUser($userString)->setUpdateUser($userString);
                } else {
                    $model->setUpdateUser($userString);
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Post was saved successfully.'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find post to save.'));
        $this->_redirect('*/*/');
    }
 
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('blog/blog');
                 
                $model->setId($this->getRequest()->getParam('id'))->delete();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Post was successfully deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $blogIds = $this->getRequest()->getParam('blog');
        if (!is_array($blogIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select post(s).'));
        } else {
            try {
                foreach ($blogIds as $blogId) {
                    $blog = Mage::getModel('blog/blog')->load($blogId);
                    $blog->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d post(s) were successfully deleted', count($blogIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massStatusAction()
    {
        $blogIds = $this->getRequest()->getParam('blog');
        if(!is_array($blogIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select post(s).'));
        } else {
            try {
                foreach ($blogIds as $blogId) {
                    $blog = Mage::getSingleton('blog/blog')
                        ->load($blogId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated.', count($blogIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
