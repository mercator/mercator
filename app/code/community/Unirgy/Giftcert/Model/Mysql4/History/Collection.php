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
class Unirgy_GiftCert_Model_Mysql4_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ugiftcert/history');
        parent::_construct();
    }
    
    public function addCertFilter($id)
    {
        if (is_array($id)) {
            $this->getSelect()->where('cert_id in (?)', $id);
        } else {
            $this->getSelect()->where('cert_id=?', $id);
        }
        return $this;
    }
    
    public function addActionFilter($action)
    {
        if (is_array($action)) {
            $this->getSelect()->where('action_code in (?)', $action);
        } else {
            $this->getSelect()->where('action_code=?', $action);
        }
        return $this;
    }
}
