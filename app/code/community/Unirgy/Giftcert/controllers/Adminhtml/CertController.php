<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Adminhtml_CertController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('customer/ugiftcert');
        $this->_addBreadcrumb($this->__('Gift Certificates'), $this->__('Gift Certificates'));
        $this->_addContent($this->getLayout()->createBlock('ugiftcert/adminhtml_cert'));

        Mage::helper('ugiftcert')->addAdminhtmlVersion();

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('customer/ugiftcert');
        $this->_addBreadcrumb($this->__('Gift Certificates'), $this->__('Gift Certificates'));

        $this->_addContent($this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit'))
            ->_addLeft($this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit_tabs'));

        Mage::helper('ugiftcert')->addAdminhtmlVersion();

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function saveAction()
    {
        $r = $this->getRequest();
        if ( $r->getPost() ) {
            try {
                $id = $r->getParam('id');
                $new = !$id;
                $model = Mage::getModel('ugiftcert/cert')
                    ->setId($id)
                    ->setCertNumber($r->getParam('cert_number'))
                    ->setPosNumber($r->getParam('pos_number'))
                    ->setBalance($r->getParam('balance'))
                    ->setCurrencyCode($r->getParam('currency_code'))
                    ->setStoreId($r->getParam('store_id'))
                    ->setStatus($r->getParam('status1'))
                    ->setExpireAt($r->getParam('expire_at'))
                    ->setSenderName($r->getParam('sender_name'))
                ;
                if ($pin = $r->getParam('pin')) {
                    $model->setPin($pin);
                }

                $model->setRecipientName($r->getParam('recipient_name'))
                    ->setRecipientEmail($r->getParam('recipient_email'))
                    ->setRecipientAddress($r->getParam('recipient_address'))
                    ->setRecipientMessage($r->getParam('recipient_message'));

                $data = array(
                    'user_id'     => Mage::getSingleton('admin/session')->getUser()->getId(),
                    'username'    => Mage::getSingleton('admin/session')->getUser()->getUsername(),
                    'ts'          => now(),
                    'amount'      => $r->getParam('balance'),
                    'currency_code' => $r->getParam('currency_code'),
                    'status'      => $r->getParam('status1'),
                    'comments'    => $r->getParam('comments'),
                    'action_code' => 'update',
                );

                if ($new) {
                    $qty = (int)$r->getParam('qty');
                    if ($qty<1) {
                        $qty = 1;
                    }

                    $num = $model->getCertNumber();
                    if (!Mage::helper('ugiftcert')->isPattern($num)) {
                        if ($new && $qty>1) {
                            throw new Exception($this->__('Can not create multiple Gift Certificates with the same code.'));
                        }

                        $dup = Mage::getModel('ugiftcert/cert')->load($num, 'cert_number');
                        if ($dup->getId() && ($new || $dup->getId()!=$model->getId())) {
                            throw new Exception($this->__('Duplicate Gift Certificate Code was found.'));
                        }
                    }

                    $data['action_code'] = 'create';
                    if ($data['order_increment_id'] = $r->getParam('order_increment_id')) {
                        if ($order = Mage::getModel('sales/order')->loadByIncrementId($data['order_increment_id'])) {
                            $data['order_id'] = $order->getId();
                            $data['customer_id'] = $order->getCustomerId();
                            $data['customer_email'] = $order->getCustomerEmail();
                        }
                    }
                    for ($i=0; $i<$qty; $i++) {
                        $clone = clone $model;
                        $clone->save();
                        $clone->addHistory($data);
                    }
                } else {
                    $model->save();
                    $model->addHistory($data);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift certificate was successfully saved'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $r->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if(($id = $this->getRequest()->getParam('id')) > 0 ) {
            try {
                Mage::getModel('ugiftcert/cert')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift certificate was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/ugiftcert');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_grid')->toHtml()
        );
    }

    /**
     * Export subscribers grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'giftcertificates.csv';
        $content    = $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export subscribers grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'giftcertificates.xml';
        $content    = $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $certIds = $this->getRequest()->getParam('cert');
        if (!is_array($certIds)) {
            $this->_getSession()->addError($this->__('Please select gift certificates(s)'));
        }
        else {
            try {
                $cert = Mage::getSingleton('ugiftcert/cert');
                foreach ($certIds as $certId) {
                    $cert->setId($certId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($certIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $certIds = (array)$this->getRequest()->getParam('cert');
        $status     = (string)$this->getRequest()->getParam('status');

        try {
            $cert = Mage::getSingleton('ugiftcert/cert');
            foreach ($certIds as $certId) {
                $cert->setId($certId)->setStatus($status)->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully updated', count($certIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession->addException($e, $this->__('There was an error while updating certificate(s) status'));
        }

        $this->_redirect('*/*/');
    }

    public function massEmailAction()
    {
        $certIds = $this->getRequest()->getParam('cert');
        if (!is_array($certIds)) {
            $this->_getSession()->addError($this->__('Please select gift certificates(s)'));
        }
        else {
            try {
                $stats = Mage::helper('ugiftcert')->sendGiftcertEmails($certIds);

                if ($stats['old']) {
                    $this->_getSession()->addWarning(
                    $this->__('In current release you can not send emails for certificates that were generated in admin (%d selected)', $stats['old'])
                );
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d gift certificates(s) and %d emails were successfully sent', $stats['certs'], $stats['emails'])
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function historyGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit_tab_history', 'admin.ugiftcert.history')
                ->setCertId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }
}
