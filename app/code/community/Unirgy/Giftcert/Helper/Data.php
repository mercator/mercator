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
class Unirgy_Giftcert_Helper_Data extends Mage_Core_Helper_Data
{
    /**
    * Convert pattern to random string
    *
    * Example: [A*5]-[AN*8]-[N*4]
    *
    * @param string $pattern
    * @return string
    */
    public function processRandomPattern($pattern)
    {
        return preg_replace_callback('#\[([AN]{1,2})\*([0-9]+)\]#', array($this, 'convertPattern'), $pattern);
    }

    /**
    * Random pattern regex callback method
    *
    * @param array $m
    * @return string
    */
    public function convertPattern($m)
    {
        $chars = (strpos($m[1], 'A')!==false ? 'ABCDEFGHJKLMNPQRSTUVWXYZ' : '').
            (strpos($m[1], 'N')!==false ? '23456789' : '');
        // no confusing chars, like O/0, 1/I
        return $this->getRandomString($m[2], $chars);
    }

    /**
    * Check whether parameter is a random pattern
    *
    * @param string $str
    * @return int
    */
    public function isPattern($str)
    {
        return preg_match('#\[([AN]{1,2})\*([0-9]+)\]#', $str);
    }

    /**
    * Possible info_buyRequest variables
    *
    * @return array
    */
    public function getGiftcertOptionVars()
    {
        return array(
            'recipient_name' => $this->__("Recipient's name"),
            'recipient_email' => $this->__("Recipient's email address"),
            'recipient_address' => $this->__("Recipient's postal address"),
            'recipient_message' => $this->__("Custom message"),
            'toself_printed' => $this->__("Send printed copy to myself"),
        );
    }

    /**
    * Generate options for item line in order view
    *
    * @param array $result
    * @param Mage_Sales_Model_Order_Item $item
    */
    public function addOrderItemCertOptions(&$result, $item)
    {
        if ($item->getProductType()!=='ugiftcert') {
            return;
        }

        if ($options = $item->getProductOptionByCode('info_buyRequest')) {
            foreach ($this->getGiftcertOptionVars() as $code=>$label) {
                if (!empty($options[$code])) {
                    $result[] = array(
                        'label' => $label,
                        'value' => $options[$code],
                        'option_value' => $options[$code],
                    );
                }
            }
        }

        if (empty($options['recipient_email']) && empty($options['recipient_address'])) {
            $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection()->addItemFilter($item->getId());
            if ($giftcerts->count()) {
                $gcs = array();
                foreach ($giftcerts as $gc) {
                    $gcs[] = $gc->getCertNumber();
                }
                $gcsStr = join("\n", $gcs);
                $result[] = array(
                    'label' => $this->__('Certificate number(s)'),
                    'value' => $gcsStr,
                    'option_value' => $gcsStr,
                );
            }
        }
    }

    protected $_currencies = array();
    public function getCurrency($currencyCode)
    {
        if (!isset($this->_currencies[$currencyCode])) {
            $this->_currencies[$currencyCode] = Mage::getModel('directory/currency')->load($currencyCode);
        }
        return $this->_currencies[$currencyCode];
    }

    protected $_productCache = array();
    public function sendEmail($data)
    {
        if (is_array($data)) {
            $data = new Varien_Object($data);
        }
        $store = $data->getStore();

        $gc = $data->getGc();
        $currency = $this->getCurrency($gc->getCurrencyCode());
        $self = !$gc->getRecipientName();

        $product = null;
        if ($data->getItem()) {
            if ($data->getItem()->getProduct()) {
                $product = $data->getItem()->getProduct();
            } else {
                $productId = $data->getItem()->getProductId();
                if (empty($this->_productCache[$productId])) {
                    $this->_productCache[$productId] = Mage::getModel('catalog/product')->load($productId);
                }
                $product = $this->_productCache[$productId];
            }
        }

        $template = Mage::getStoreConfig($self ? 'ugiftcert/email/template_self' : 'ugiftcert/email/template', $store);
        $identity = Mage::getStoreConfig('ugiftcert/email/identity', $store);
        $this->setDesignStore($store);
        Mage::getModel('core/email_template')
            ->sendTransactional($template, $identity, $data->getEmail(), $data->getName(), array(
                'order' => $data->getOrder(),
                'item' => $data->getItem(),
                'product' => $product,
                'gc' => $gc,
                'amount' => $currency->format($gc->getAmount()),
                'sender_name' => $data->getSenderName(),
                'sender_firstname' => $data->getSenderFirstname(),
                'recipient_name' => $data->getName(),
                'custom_message' => $gc->getRecipientMessage(),
                'expire_on' => $gc->getExpireAt() ? $this->formatDate($gc->getExpireAt(), 'long') : '',
                'certificate_numbers' => join('<br/>', $data->getGcNumbers()),
                'website_name' => $store->getWebsite()->getName(),
                'group_name' => $store->getGroup()->getName(),
                'store_name' => $store->getName(),
            ));
        $this->setDesignStore();
    }

    public function sendManualEmail($gc)
    {
        $store = Mage::app()->getStore($gc->getStoreId());

        $emailEnabled = Mage::getStoreConfig('ugiftcert/email/enabled', $store);
        if (!$gc->getRecipientEmail() || !$emailEnabled) {
            return $this;
        }

        $usePin = Mage::getStoreConfig('ugiftcert/default/use_pin', $store);
        $pinFormat = Mage::getStoreConfig('ugiftcert/email/pin_format', $store);

        $this->sendEmail(array(
            'store' => $store,
            'email' => $gc->getRecipientEmail(),
            'name' => $gc->getRecipientName(),
            'sender_name' => $gc->getSenderName() ? $gc->getSenderName() : $store->getWebsite()->getName(),
            'sender_firstname' => $gc->getSenderName() ? $gc->getSenderName() : $store->getWebsite()->getName(),
            'gc' => $gc,
            'gc_numbers' => array($gc->getCertNumber().($usePin ? sprintf($pinFormat, $gc->getPin()) : '')),
        ));

        return $this;
    }

    /**
    * Send GC confirmation email for order item
    *
    * @param Mage_Sales_Model_Order_Item $item
    * @param null|Unirgy_Giftcert_Model_Mysql4_Cert_Collection $giftcerts
    */
    public function sendOrderItemEmail($item, $giftcerts = null)
    {
        $order = $item->getOrder();
        $storeId = $order->getStoreId();
        $store = Mage::app()->getStore($storeId);

        if (!$giftcerts) {
            $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection()->addItemFilter($item->getId());
        }
        if (!count($giftcerts)) {
            return $this;
        }
        $gcs = array();
        $self = null;

        $emailEnabled = Mage::getStoreConfig('ugiftcert/email/enabled', $store);
        $usePin = Mage::getStoreConfig('ugiftcert/default/use_pin', $store);
        $pinFormat = Mage::getStoreConfig('ugiftcert/email/pin_format', $store);

        foreach ($giftcerts as $gc) {
            if (is_null($self)) {
                $self = !$gc->getRecipientName();
                if (!$self && !$gc->getRecipientEmail()) {
                    return $this;
                }
                if (!$self && !$emailEnabled) {
                    return $this;
                }
                $email = $self ? $order->getCustomerEmail() : $gc->getRecipientEmail();
                $name = $self ? $order->getBillingAddress()->getFirstname() : $gc->getRecipientName();
            }

            $gcs[] = $gc->getCertNumber().($usePin ? sprintf($pinFormat, $gc->getPin()) : '');

            $history = array(
                'action_code'       => 'email',
                'ts'                => now(),
                'amount'            => $gc->getAmount(),
                'currency_code'     => $gc->getCurrencyCode(),
                'status'            => $gc->getStatus(),
                'customer_email'    => $email,
            );
            if (Mage::app()->getStore()->isAdmin()) {
                $user = Mage::getSingleton('admin/session')->getUser();
                $history['user_id'] = $user->getId();
                $history['username'] = $user->getUsername();
            }
            $gc->addHistory($history);
        }

        $this->sendEmail(array(
            'store' => $store,
            'order' => $order,
            'item' => $item,
            'email' => $email,
            'name' => $name,
            'sender_name' => $order->getCustomerName() ? $order->getCustomerName() : $order->getBillingAddress()->getName(),
            'sender_firstname' => $order->getCustomerFirstname() ? $order->getCustomerFirstname() : $order->getBillingAddress()->getFirstname(),
            'gc' => $gc,
            'gc_numbers' => $gcs,
        ));

        return $this;
    }

    /**
    * Send GC confirmation emails for set of certificates
    *
    * Used in admin GC grid mass action
    *
    * @param array $certIds
    * @return array
    */
    public function sendGiftcertEmails(array $certIds)
    {
        $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection()->addIdFilter($certIds);

        $grouped = array();
        $emailsSent = 0;
        $certsSent = 0;
        $oldCerts = 0;
        foreach ($giftcerts as $cert) {
#echo "<pre>"; print_r($cert->debug()); exit;
            $certsSent++;
            $itemId = $cert->getOrderItemId();
            if (empty($itemId)) {
                $this->sendManualEmail($cert);
                $emailsSent++;
                continue;
            }
            if (empty($grouped[$itemId])) {
                $item = Mage::getModel('sales/order_item')->load($itemId);
                //$item->setOrder(Mage::getModel('sales/order')->load($item->getOrderId()));
                $grouped[$itemId]['item'] = $item;
            }
            $grouped[$itemId]['certs'][$cert->getId()] = $cert;
        }

        foreach ($grouped as $g) {
            $this->sendOrderItemEmail($g['item'], $g['certs']);
            $emailsSent++;
        }

        return array('emails'=>$emailsSent, 'certs'=>$certsSent, 'old'=>$oldCerts);
    }

    /**
    * Parse GC amount configuration
    *
    * Used on product list to determine minimal price, and on product view page.
    *
    * Format:
    * 50-1500 : range
    * 50;100;200 : dropdown
    * 100 : fixed amount
    * - : any value
    *
    * Multi currency setup - use multiple lines, star for default:
    *
    * USD:50-1500
    * CAD,EUR:50;100;200
    * *:25;50
    *
    * Whitespaces are ignored
    *
    * @param mixed $product
    * @param mixed $attr
    * @return mixed
    */
    public function getAmountConfig($product, $attr='ugiftcert_amount_config')
    {
        $valuesStr = $product->getDataUsingMethod($attr);

        if (!$valuesStr) {
            $valuesStr = Mage::getStoreConfig('ugiftcert/default/amount_config');
        }

        $valuesStr = trim(str_replace(array(' ', "\r", "\t"), '', $valuesStr));

        $currencyCode = Mage::app()->getStore()->getCurrentCurrency()->getCurrencyCode();
        $lines = explode("\n", $valuesStr);
        if (sizeof($lines)>1) {
            $choices = array();
            foreach ($lines as $line) {
                $values = explode(':', $line);
                if (empty($values[1])) {
                    continue;
                }
                $choices[$values[0]] = $values[1];
            }
            $found = false;
            foreach ($choices as $curs=>$values) {
                if (strpos($curs, $currencyCode)!==false) {
                    $found = true;
                    $valuesStr = $values;
                }
            }
            if (!$found) {
                $valuesStr = isset($choices['*']) ? $choices['*'] : '-';
            }
        }

        if ($valuesStr==='' || $valuesStr==='-') {
            return array('type'=>'any');
        }

        $values = explode('-', $valuesStr);
        if (sizeof($values)==2) {
            return array('type'=>'range', 'from'=>$values[0], 'to'=>$values[1]);
        }

        $values = explode(';', $valuesStr);
        if (sizeof($values)>1) {
            return array('type'=>'dropdown', 'options'=>$values);
        }

        $value = intval($valuesStr);
        return array('type'=>'fixed', 'amount'=>$value);
    }

    protected $_currencyRate;
    public function reverseCurrency($amount, $origAmount)
    {
        if (empty($this->_currencyRate)) {
            $this->_currencyRate = Mage::app()->getStore()->getCurrentCurrencyRate();
        }
    }

    protected $_store;
    protected $_oldStore;
    protected $_oldArea;
    protected $_oldDesign;
    /**
    * Safely set frontend configuration for sending emails
    *
    * @param mixed $store
    */
    public function setDesignStore($store=null)
    {
        if (!is_null($store)) {
            if ($this->_store) {
                return $this;
            }
            $this->_oldStore = Mage::app()->getStore();
            $this->_oldArea = Mage::getDesign()->getArea();
            $this->_store = Mage::app()->getStore($store);

            $store = $this->_store;
            $area = 'frontend';
            $package = Mage::getStoreConfig('design/package/name', $store);
            $design = array('package'=>$package, 'store'=>$store->getId());
            $inline = false;
        } else {
            if (!$this->_store) {
                return $this;
            }
            $this->_store = null;
            $store = $this->_oldStore;
            $area = $this->_oldArea;
            $design = $this->_oldDesign;
            $inline = true;
        }

        Mage::app()->setCurrentStore($store);
        $oldDesign = Mage::getDesign()->setArea($area)->setAllGetOld($design);
        Mage::app()->getTranslator()->init($area, true);
        Mage::getSingleton('core/translate')->setTranslateInline($inline);

        if ($this->_store) {
            $this->_oldDesign = $oldDesign;
        } else {
            $this->_oldStore = null;
            $this->_oldArea = null;
            $this->_oldDesign = null;
        }

        return $this;
    }

    public function addAdminhtmlVersion($module='Unirgy_Giftcert')
    {
        $layout = Mage::app()->getLayout();
        $version = (string)Mage::getConfig()->getNode("modules/{$module}/version");

        $layout->getBlock('before_body_end')->append($layout->createBlock('core/text')->setText('
            <script type="text/javascript">$$(".legality")[0].insert({after:"'.$module.' ver. '.$version.'<br/>"});</script>
        '));

        return $this;
    }
}
