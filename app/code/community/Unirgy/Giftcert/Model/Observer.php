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
class Unirgy_Giftcert_Model_Observer
{
    /**
    * GC used in order
    *
    * @var array
    */
    protected $_orderUpdates = array();

    /**
    * Whether the order contains new GCs
    *
    * @var boolean
    */
    protected $_newGcs = false;

    /**
    * Whether the GC items were invoiced and paid
    *
    * @var boolean
    */
    protected $_gcInvoiced = false;

    /**
    * Catch GC codes applied in cart
    *
    * @param mixed $observer
    */
    public function controller_action_predispatch_checkout_cart_couponPost($observer)
    {
        $action = $observer->getEvent()->getControllerAction();

        $code = trim($action->getRequest()->getParam('coupon_code'));

        $cert = Mage::getModel('ugiftcert/cert')->load($code, 'cert_number');
        if ($cert->getId() && $cert->getStatus()=='A' && $cert->getBalance()>0) {
            $session = Mage::getSingleton('checkout/session');
            $quote = $session->getQuote();
            $cert->addToQuote($quote);
            $quote->collectTotals()->save();
            $session->addSuccess(Mage::helper('ugiftcert')->__("Gift voucher '%s' was applied to your order.", $cert->getCertNumber()));
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
        }
    }

    /**
    * In case customer's quote is loaded, remember current GC codes
    *
    * @param mixed $observer
    */
    public function controller_action_predispatch_customer_account_loginPost($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setGiftcertCode($session->getQuote()->getGiftcertCode());
    }

    /**
    * Restore and merge GC codes for customer's quote
    *
    * @param mixed $observer
    */
    public function customer_login($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        if ($session->getGiftcertCode()) {
            $gc1 = preg_split('#\s*,\s*#', $session->getGiftcertCode(true));
            $gc2 = preg_split('#\s*,\s*#', $session->getQuote()->getGiftcertCode());
            $gc = join(',', array_unique(array_merge($gc1, $gc2)));
            $session->getQuote()->setGiftcertCode($gc)->save();
        }
    }

    /**
    * Trying not to overload sales/quote ...
    *
    * @param mixed $observer
    */
    public function apply_quote_item_to_products($observer)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $item->getProduct()->setQuoteItem($item);
        }
    }

    public function sales_order_payment_place_start($observer)
    {
        $order = $observer->getEvent()->getPayment()->getOrder();

        if (!$order) {
            return $this;
        }

        // new GCs were purchased
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType()=='ugiftcert') {
                $this->_newGcs = true;
                break;
            }
        }

        $certIds = explode(',', $order->getGiftcertCode());
        $certIds = array_unique($certIds);

        $totalAmount = $order->getGiftcertAmount();
        $baseTotalAmount = $order->getBaseGiftcertAmount();

        $this->_orderUpdates = array();
        foreach ($certIds as $certId) {
            if (!$certId) {
                continue;
            }
            $cert = Mage::getModel('ugiftcert/cert')->load(trim($certId), 'cert_number');

            if (!$cert->getId() || $cert->getStatus()!='A' || $cert->getBalance()==0) {
                continue;
            }

            $baseAmount = min($baseTotalAmount, $cert->getBaseBalance());

            $amount = $baseAmount*$cert->getCurrencyRate();
            $cert->setAmount($amount)->setBalance($cert->getBalance()-$amount);

            if ($cert->getBalance()<=.001) {
                $cert->setStatus('I');
            }
            $this->_orderUpdates[] = $cert;

            $baseTotalAmount -= $baseAmount;
            if ($baseTotalAmount==0) {
                break;
            }
        }

        if ($baseTotalAmount>0 && $order->getBaseGrandTotal()==0) {
            Mage::throwException(Mage::helper('ugiftcert')->__('Gift vouchers applied to this order have changed.
Unable to proceed, please return to shopping cart to see the changes.'));
        }
    }
    
    public function salesOrderBeforeSave($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->getPayment()->getMethodInstance()->getCode() != 'ugiftcert') {
            return $this;
        }

        if ($order->canUnhold()) {
            return $this;
        }

        if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED ) {
            return $this;
        }
        if (!$order->hasForcedCanCreditmemo()) {
            $order->setForcedCanCreditmemo(true);
        }
        return $this;
    }

    public function sales_order_save_after($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();

        // default history row values
        $data = array(
            'ts'                 => now(),
            'action_code'        => 'order',
            'customer_id'        => $order->getCustomerId() ? $order->getCustomerId() : null,
            'customer_email'     => $order->getCustomerEmail(),
            'order_id'           => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'store_id'           => $storeId,
        );

        /* Set order status to 'complete' if it has been shipped and the status is still 'processing'. This was done to
           to fix the problem of an order not being 'complete' when a gift voucher was used resulting in an amount of
           $0. Occasionally, after the order was invoiced and shipped, the status would still be 'processing'.
        */
        if(($_POST['history']['status'] == 'complete') && (strcmp($order->getStatus(),
            Mage_Sales_Model_Order::STATE_PROCESSING) == 0))
        {
            $itemsToShip = 0;
            $itemsToInvoice = 0;

            /* This was added to make sure that when items in an order are shipped separately, the status of the order
               won't become 'complete'. It will only become 'complete' if there are no more items to invoice and ship.
            */
            $orderItems = $order->getAllItems();
            foreach($orderItems as $item)
            {
                $itemsToShip += $item->getQtyToShip();
                $itemsToInvoice += $item->getQtyToInvoice();
            }

            if(($itemsToInvoice == 0) && ($itemsToShip == 0)) {
                $order->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE);
                $order->save();
            }
        }

        // process applied gift certificates
        foreach ($this->_orderUpdates as $cert) {
            $cert->save();
            $data['amount'] = $cert->getAmount();
            $data['status'] = $cert->getStatus();
            $data['currency_code'] = $cert->getCurrencyCode();
            $cert->addHistory($data);
        }
        $this->_orderUpdates = array();

        // process purchased gift certificates
        if ($this->_newGcs) {
            $config = Mage::getStoreConfig('ugiftcert/default');
            $reqVars = array_keys(Mage::helper('ugiftcert')->getGiftcertOptionVars());
            $autoSend = Mage::getStoreConfig('ugiftcert/email/auto_send', $storeId);
            $changeStatus = Mage::getStoreConfig('ugiftcert/default/active_on_payment', $storeId);

            $data['action_code'] = 'create';
            $data['currency_code'] = $order->getOrderCurrencyCode();
            $data['order_id'] = $order->getId();
            $data['status'] = ($this->_gcInvoiced && $changeStatus) ? 'A' : $config['status'];

            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType()!='ugiftcert') {
                    continue;
                }
                $options = $item->getProductOptions();
                $r = $options['info_buyRequest'];
                $data['order_item_id'] = $item->getId();
                $data['amount'] = isset($r['amount']) ? $r['amount'] : $item->getPrice();

                for ($i=0; $i<$item->getQtyOrdered(); $i++) {
                    $cert = Mage::getModel('ugiftcert/cert')
                        ->setStatus($data['status'])
                        ->setBalance($data['amount'])
                        ->setCurrencyCode($data['currency_code'])
                        ->setStoreId($storeId);
                    if ($config['auto_cert_number']) {
                        $cert->setCertNumber($config['cert_number']);
                    }
                    if ($config['auto_pin']) {
                        $cert->setPin($config['pin']);
                    }
                    if (($days = intval($config['expire_timespan']))) {
                        $cert->setExpireAt(date('Y-m-d', time()+$days*86400));
                    }
                    foreach ($reqVars as $f) {
                        if (!empty($r[$f])) {
                            $cert->setData($f, $r[$f]);
                        }
                    }
                    $cert->save();
                    $cert->addHistory($data);
                }
                if ((Unirgy_Giftcert_Model_Source_Autosend::ORDER == $autoSend)
                    || ((Unirgy_Giftcert_Model_Source_Autosend::PAYMENT == $autoSend) && $this->_gcInvoiced)) {

                    Mage::helper('ugiftcert')->sendOrderItemEmail($item);
                    $item->setQtyShipped($item->getQtyOrdered());
                }
            }
            $this->_newGcs = false;
            if ($this->_gcInvoiced) {
                $order->save();
            }
        }
    }

    /**
    * Send new GC confirmation on invoice pay
    *
    * This event can be launched before sales_order_save_after or after
    *
    * @author vmaillot (Vincent)
    * @param mixed $observer
    */
    public function sales_order_invoice_pay($observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();

        // order has been saved already
        if ($order->getId()) {
            $changeStatus = Mage::getStoreConfig('ugiftcert/default/active_on_payment', $order->getStoreId());
            $autoSend = Mage::getStoreConfig('ugiftcert/email/auto_send', $order->getStoreId());

            if ($changeStatus) {
                $certs = Mage::getModel('ugiftcert/cert')->getCollection()
                    ->addOrderFilter($order->getId());
                foreach ($certs as $cert) {
                    $cert->setStatus('A')->save();
                }
            }

            if (Unirgy_Giftcert_Model_Source_Autosend::PAYMENT == $autoSend) {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getProductType()!='ugiftcert') {
                        continue;
                    }
                    Mage::helper('ugiftcert')->sendOrderItemEmail($item);
                    $item->setQtyShipped($item->getQtyInvoiced());
                }
            }
        }
        // order has been invoiced before it was saved - remember and execute on save
        else {
            $this->_gcInvoiced = true;
        }
    }

    /**
    * On payment/order cancel refund GCs
    *
    * @param mixed $observer
    */
    public function sales_order_payment_cancel($observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();

        $gcHistory = Mage::getModel('ugiftcert/history')->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('action_code', 'order');

        if (!$gcHistory->count()) {
            return;
        }

        foreach ($gcHistory as $gch) {
            $cert = Mage::getModel('ugiftcert/cert')->load($gch->getCertId());
            if (!$cert) continue;

            $data = $gch->getData();
            $data['history_id'] = null;
            $data['action_code'] = 'refund';
            $data['ts'] = now();
            $data['status'] = 'A';
            $cert->addHistory($data);

            $amount = Mage::helper('directory')->currencyConvert($gch->getAmount(), $gch->getCurrencyCode(), $cert->getCurrencyCode());
            $cert->setStatus('A')->setBalance($cert->getBalance()+$amount)->save();
        }
    }

    /**
    * Check for extension update news
    *
    * @param Varien_Event_Observer $observer
    */
    public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('ugiftcert/admin/notifications')) {
            try {
                //disable for time being
                #Mage::getModel('ugiftcert/feed')->checkUpdate();
            } catch (Exception $e) {
                // silently ignore
            }
        }
    }

    /**
    * Hide E_STRICT for 1.2.x
    *
    * @deprecated
    * @see controller_front_init_before
    * @param mixed $observer
    */
    public function catalog_product_load_before($observer)
    {
        $errorReporting = error_reporting();
        if ($errorReporting & E_STRICT) {
            error_reporting($errorReporting & !E_STRICT);
        }
    }

    /**
    * Use different version of product type class for 1.2.x
    *
    * @param mixed $observer
    */
    public function controller_front_init_before($observer)
    {
        if (version_compare(Mage::getVersion(), '1.3.0', '<')) {
            Mage::getConfig()->setNode('global/catalog/product/type/ugiftcert/model', 'ugiftcert/product_type12');
        }
    }

    /**
    * Support for admin created orders
    *
    * @param mixed $observer
    */
    public function controller_action_predispatch_adminhtml_sales_order_create_loadBlock($observer)
    {
        $data = Mage::app()->getRequest()->getPost('order');
        if (empty($data['coupon']['code'])) {
            return;
        }
        $code = trim($data['coupon']['code']);

        $cert = Mage::getModel('ugiftcert/cert')->load($code, 'cert_number');
        if (!($cert->getId() && $cert->getStatus()=='A' && $cert->getBalance()>0)) {
            return;
        }

        $session = Mage::getSingleton('adminhtml/session_quote');
        $quote = $session->getQuote();
        $cert->addToQuote($quote);
        $quote->collectTotals()->save();
        $session->addSuccess(Mage::helper('ugiftcert')->__("Gift voucher '%s' was applied to your order.", $cert->getCertNumber()));

        unset($data['coupon']['code']);
        Mage::app()->getRequest()->setPost('order', $data);
    }
}
