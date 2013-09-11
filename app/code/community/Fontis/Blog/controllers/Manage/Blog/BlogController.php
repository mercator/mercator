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
        $this->loadLayout()->_setActiveMenu("blog/posts");
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("blog/post")->load($id);

        if ($model->getId() || $id == 0) {
            $this->_renderBlogEditPage($model);
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("blog")->__("Post does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction()
    {
        $this->_renderBlogEditPage(Mage::getModel("blog/post"));
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

        $layout = $this->getLayout();
        $layout->getBlock("head")->setCanLoadExtJs(true);
        $this->_addContent($layout->createBlock("blog/manage_blog_edit"))
            ->_addLeft($layout->createBlock("blog/manage_blog_edit_tabs"));

        $this->renderLayout();
    }

    /**
     * Called whenever a new or existing post is saved.
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        if ($data = $request->getPost()) {
            $model = Mage::getModel("blog/post");
            if ($id = $request->getParam("id")) {
                $model->load($id);
                $newPost = false;
            } else {
                $newPost = true;
            }
            $model->setData($data)->setId($id);

            try {
                $nowTime = now();
                if ($request->getParam("created_time") == NULL) {
                    $model->setCreatedTime($nowTime)->setUpdateTime($nowTime);
                } else {
                    $model->setUpdateTime($nowTime);
                }

                $userString = Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton('admin/session')->getUser()->getLastname();
                if ($request->getParam("user") == NULL) {
                    $model->setUser($userString)->setUpdateUser($userString);
                } else {
                    $model->setUpdateUser($userString);
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blog')->__('Post was saved successfully.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // If this is a new post, we need to ensure it shows up immediately on the frontend.
                $newStatus = $model->getStatus();
                if ($newPost && $newStatus == Fontis_Blog_Model_Status::STATUS_ENABLED) {
                    Mage::helper("blog")->enablePost($model);
                } else if (!$newPost) {
                    $oldStatus = $model->getOrigData("status");
                    if ($oldStatus == Fontis_Blog_Model_Status::STATUS_ENABLED && ($newStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $newStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN)) {
                        Mage::helper("blog")->disablePost();
                    } else if (($oldStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $oldStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN) && $newStatus == Fontis_Blog_Model_Status::STATUS_ENABLED) {
                        Mage::helper("blog")->enablePost($model);
                    }
                }

                if ($request->getParam('back')) {
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
        $postId = $this->getRequest()->getParam("id");
        if ($postId > 0) {
            try {
                Mage::getModel("blog/post")->load($postId)->delete();
                Mage::helper("blog")->disablePost();

                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Post was successfully deleted."));
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $postId));
            }
        }
        $this->_redirect("*/*/");
    }

    public function massDeleteAction() {
        $postIds = $this->getRequest()->getParam("blog");
        if (!is_array($postIds)) {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("adminhtml")->__("Please select post(s)."));
        } else {
            try {
                foreach ($postIds as $postId) {
                    Mage::getModel("blog/post")->load($postId)->delete();
                }
                Mage::helper("blog")->disablePost();

                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("adminhtml")->__("Total of %d post(s) were successfully deleted", count($postIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            }
        }
        $this->_redirect("*/*/index");
    }

    public function massStatusAction()
    {
        $request = $this->getRequest();
        $postIds = $request->getParam("blog");
        if (!is_array($postIds)) {
            Mage::getSingleton("adminhtml/session")->addError($this->__("Please select post(s)."));
        } else {
            try {
                $helper = Mage::helper("blog");
                $newStatus = $request->getParam("status");
                foreach ($postIds as $postId) {
                    $post = Mage::getModel("blog/post")->load($postId);
                    $oldStatus = $post->getStatus();
                    $post->setStatus($newStatus)
                        ->setIsMassupdate(true)
                        ->save();

                    if ($oldStatus == Fontis_Blog_Model_Status::STATUS_ENABLED && ($newStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $newStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN)) {
                        $helper->disablePost();
                    } else if (($oldStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $oldStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN) && $newStatus == Fontis_Blog_Model_Status::STATUS_ENABLED) {
                        $helper->enablePost($post);
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__("Total of %d record(s) were successfully updated.", count($postIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect("*/*/index");
    }
}
