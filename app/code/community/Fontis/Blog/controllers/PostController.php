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

class Fontis_Blog_PostController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $request = $this->getRequest();
        $identifier = $request->getParam("identifier", $request->getParam("id", false));

        if ($data = $request->getPost()) {
            $blogHelper = Mage::helper("blog");
            $customerSession = Mage::getSingleton("customer/session");

            if (!Mage::getStoreConfig("fontis_blog/comments/enabled")) {
                $customerSession->addError($blogHelper->__("Comments are not enabled."));
                if (!Mage::helper("blog/post")->renderPage($this, $identifier)) {
                    $this->_forward("NoRoute");
                }
                return;
            }

            $commentsLogin = Mage::getStoreConfig("fontis_blog/comments/login");
            if (!$customerSession->isLoggedIn() && $commentsLogin) {
                $customerSession->addError($blogHelper->__("You must be logged in to comment."));
                if (!Mage::helper("blog/post")->renderPage($this, $identifier)) {
                    $this->_forward("NoRoute");
                }
                return;
            }

            $entryErrors = array();
            if (trim($data["comment"]) == "") {
                $entryErrors[] = $blogHelper->__("No comment was entered.");
            }
            if (trim($data["user"]) == "") {
                $entryErrors[] = $blogHelper->__("No name was entered.");
            }
            if (trim($data["email"]) == "") {
                $entryErrors[] = $blogHelper->__("No email address was entered.");
            }
            if (count($entryErrors)) {
                foreach ($entryErrors as $error) {
                    $customerSession->addError($error);
                }
                if (!Mage::helper("blog/post")->renderPage($this, $identifier)) {
                    $this->_forward("NoRoute");
                }
                return;
            }
            unset($entryErrors);

            if (!$data["in_reply_to"]) {
                $data["in_reply_to"] = null;
            }

            $model = Mage::getModel("blog/comment");
            $model->setData($data);

            try {
                if (Mage::helper("blog")->useRecaptcha()) {
                    $privatekey = Mage::getStoreConfig("fontis_recaptcha/setup/private_key");
                    $resp = Mage::helper("fontis_recaptcha")->recaptcha_check_answer($privatekey,
                        $_SERVER["REMOTE_ADDR"],
                        $data["recaptcha_challenge_field"],
                        $data["recaptcha_response_field"]
                    );

                    if ($resp == false) {
                        $customerSession->addError($blogHelper->__("Your Recaptcha solution was incorrect. Please try again."));
                        if (!Mage::helper("blog/comment")->renderPage($this, $identifier, $data)) {
                            $this->_forward("NoRoute");
                        }
                        return;
                    }
                }

                $model->setCreatedTime(now());
                $model->setComment(htmlspecialchars($model->getComment(), ENT_QUOTES));
                if (Mage::getStoreConfig("fontis_blog/comments/approval")) {
                    $model->setStatus(2);
                } else if ($customerSession->isLoggedIn() && Mage::getStoreConfig("fontis_blog/comments/loginauto")) {
                    $model->setStatus(2);
                } else {
                    $model->setStatus(1);
                }
                $model->save();

                if ($model->getStatus() == 1) {
                    $customerSession->addSuccess($blogHelper->__("Your comment has been submitted and is awaiting approval."));
                } else {
                    $customerSession->addSuccess($blogHelper->__("Your comment has been submitted."));
                }

                $comment_id = $model->getCommentId();
            } catch (Exception $e) {
                $customerSession->addError($blogHelper->__("An error occurred. Please try again."));
                if (!Mage::helper("blog/post")->renderPage($this, $identifier)) {
                    $this->_forward("NoRoute");
                }
            }

            if (Mage::getStoreConfig("fontis_blog/comments/recipient_email") != null && $model->getStatus() == 1 && isset($comment_id)) {
                $translate = Mage::getSingleton("core/translate");
                /* @var $translate Mage_Core_Model_Translate */
                $translate->setTranslateInline(false);
                try {
                    $data["url"] = Mage::getUrl("blog/manage_comment/edit/id/" . $comment_id);
                    $postObject = new Varien_Object();
                    $postObject->setData($data);
                    $mailTemplate = Mage::getModel("core/email_template");
                    /* @var $mailTemplate Mage_Core_Model_Email_Template */
                    $mailTemplate->setDesignConfig(array("area" => "frontend"))
                        ->sendTransactional(
                            Mage::getStoreConfig("fontis_blog/comments/email_template"),
                            Mage::getStoreConfig("fontis_blog/comments/sender_email_identity"),
                            Mage::getStoreConfig("fontis_blog/comments/recipient_email"),
                            null,
                            array("data" => $postObject)
                        );
                    $translate->setTranslateInline(true);
                } catch (Exception $e) {
                    $translate->setTranslateInline(true);
                }
            }
            if (!Mage::helper("blog/post")->renderPage($this, $identifier)) {
                $this->_forward("NoRoute");
            }
        } else {
            if (!Mage::helper("blog/post")->renderPage($this, $identifier)) {
                $this->_forward("NoRoute");
            }
        }
    }

    public function noRouteAction($coreRoute = null)
    {
        $this->getResponse()->setHeader("HTTP/1.1", "404 Not Found");
        $this->getResponse()->setHeader("Status", "404 File not found");

        $pageId = Mage::getStoreConfig("web/default/cms_no_route");
        if (!Mage::helper("cms/page")->renderPage($this, $pageId)) {
            $this->_forward("defaultNoRoute");
        }
    }
}
