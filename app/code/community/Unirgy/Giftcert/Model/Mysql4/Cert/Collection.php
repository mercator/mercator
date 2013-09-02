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
class Unirgy_GiftCert_Model_Mysql4_Cert_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_hasHistory = false;

    protected function _construct()
    {
        $this->_init('ugiftcert/cert');
        parent::_construct();
    }

    public function addHistory()
    {
        if ($this->_hasHistory) {
            return $this;
        }
        $this->_hasHistory = true;

        $this->getSelect()->join(array('h'=>$this->getTable('ugiftcert/history')), 'h.cert_id=main_table.cert_id', array('ts', 'amount', 'customer_id', 'customer_email', 'order_id', 'order_increment_id', 'order_item_id', 'user_id', 'username'))
            ->where("h.action_code='create'");
        return $this;
    }

    public function addIdFilter($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $this->addHistory();
        $this->getSelect()->where('main_table.cert_id in (?)', $ids);
        return $this;
    }

    public function addOrderFilter($orderId)
    {
        $this->addHistory();
        if (is_array($orderId)) {
            $this->getSelect()->where('h.order_id in (?)', $orderId);
        } else {
            $this->getSelect()->where('h.order_id=?', $orderId);
        }
        return $this;
    }

    public function addItemFilter($itemId)
    {
        $this->addHistory();
        if (is_array($itemId)) {
            $this->getSelect()->where('h.order_item_id in (?)', $itemId);
        } else {
            $this->getSelect()->where('h.order_item_id=?', $itemId);
        }
        return $this;
    }
}
