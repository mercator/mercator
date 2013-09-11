<?php
/**
 * Fontis Recaptcha Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Fontis
 * @package    Fontis_Recaptcha
 * @author     Denis Margetic
 * @author     Chris Norton
 * @copyright  Copyright (c) 2011 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
include_once "Mage/Sendfriend/controllers/ProductController.php";

class Fontis_Recaptcha_ProductController extends Mage_Sendfriend_ProductController
{
    public function sendmailAction()
    {
        if (Mage::helper("fontis_recaptcha")->showForm("sendfriend", true)) {
            $privatekey = Mage::getStoreConfig("fontis_recaptcha/setup/private_key");
            // check response
            $resp = Mage::helper("fontis_recaptcha")->recaptcha_check_answer($privatekey,
                                                                             $_SERVER["REMOTE_ADDR"],
                                                                             $_POST["recaptcha_challenge_field"],
                                                                             $_POST["recaptcha_response_field"]
                                                                            );
            $data = $this->getRequest()->getPost();
            if ($resp == true) {
                // if recaptcha response is correct, use core functionality
                parent::sendmailAction();
            } else {
                // if recaptcha response is incorrect, reload the page
                Mage::getSingleton('catalog/session')->addError($this->__('Your reCAPTCHA entry is incorrect. Please try again.'));
                Mage::getSingleton('catalog/session')->setSendfriendFormData($data);
                $this->_redirectReferer();
                return;
            }
        } else {
            // if recaptcha is not enabled, use core function alone
            parent::sendmailAction();
        }
    }
}
