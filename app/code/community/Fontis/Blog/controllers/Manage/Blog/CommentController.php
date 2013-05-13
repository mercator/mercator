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

class Fontis_Blog_Manage_Blog_CommentController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu("blog/comment")
            ->_addBreadcrumb($text = Mage::helper("adminhtml")->__("Comment Manager"), $text);
        
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
                $comment = Mage::getModel('blog/comment');
                $comment->setId($this->getRequest()->getParam('id'))->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Comment was successfully deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function approveAction()
    {
        if( $this->getRequest()->getParam("id") > 0 ) {
            $this->_changeSingleCommentStatus(2);
        }
        $this->_redirect('*/*/');
    }
    
    public function unapproveAction()
    {
        if ($this->getRequest()->getParam("id") > 0) {
            $this->_changeSingleCommentStatus(1);
        }
        $this->_redirect('*/*/');
    }

    protected function _changeSingleCommentStatus($status)
    {
        try {
            $comment = Mage::getModel("blog/comment");

            $comment->setId($this->getRequest()->getParam('id'))
                ->setStatus($status)
                ->save();

            //TODO: Find appropriate place to create constants for these values.
            if ($status == 1) {
                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Comment was unapproved."));
            } else {
                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Comment was approved."));
            }
            $this->_redirect("*/*/");
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam('id')));
        }
    }

    public function massDeleteAction()
    {
        $commentIds = $this->getRequest()->getParam('blog');
        if (!is_array($commentIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select comment(s).'));
        } else {
            try {
                foreach ($commentIds as $commentId) {
                    $comment = Mage::getModel('blog/comment')->load($commentId);
                    $comment->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d comments(s) were successfully deleted', count($commentIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function massApproveAction()
    {
        $commentIds = $this->getRequest()->getParam("blog");
        if(!is_array($commentIds)) {
            Mage::getSingleton("adminhtml/session")->addError($this->__("Please select comment(s)."));
        } else {
            $this->_changeMassCommentStatus($commentIds, 2);
        }
        $this->_redirect("*/*/");
    }
    
    public function massUnapproveAction()
    {
        $commentIds = $this->getRequest()->getParam("blog");
        if(!is_array($commentIds)) {
            Mage::getSingleton("adminhtml/session")->addError($this->__("Please select comment(s)."));
        } else {
            $this->_changeMassCommentStatus($commentIds, 1);
        }
        $this->_redirect("*/*/");
    }

    protected function _changeMassCommentStatus($commentIds, $status)
    {
        try {
            foreach ($commentIds as $commentId) {
                $comment = Mage::getSingleton("blog/comment")
                    ->load($commentId)
                    ->setStatus($status)
                    ->setIsMassupdate(true)
                    ->save();
            }
            if ($status == 1) {
                $this->_getSession()->addSuccess(
                    $this->__("Total of %d comment(s) were successfully unapproved.", count($commentIds))
                );
            } else {
                $this->_getSession()->addSuccess(
                    $this->__("Total of %d comment(s) were successfully approved.", count($commentIds))
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
    }
    
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('blog/comment')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('blog_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('blog/posts');
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('blog/manage_comment_edit'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Comment does not exist'));
            $this->_redirect('*/*/');
        }
    }
    
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('blog/comment');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            
            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Comment was successfully saved.'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find comment to save.'));
        $this->_redirect('*/*/');
    }
}
