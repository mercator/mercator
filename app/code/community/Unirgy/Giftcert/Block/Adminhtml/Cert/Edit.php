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
class Unirgy_Giftcert_Block_Adminhtml_Cert_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'ugiftcert';
        $this->_controller = 'adminhtml_cert';

        $this->_updateButton('save', 'label', Mage::helper('ugiftcert')->__('Save Gift Certificate'));
        $this->_updateButton('delete', 'label', Mage::helper('ugiftcert')->__('Delete Gift Certificate'));

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $model = Mage::getModel('ugiftcert/cert')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('giftcert_data', $model);
        }


    }

    public function getHeaderText()
    {
        if( Mage::registry('giftcert_data') && Mage::registry('giftcert_data')->getId() ) {
            return Mage::helper('ugiftcert')->__("Edit Gift Certificate '%s'", $this->htmlEscape(Mage::registry('giftcert_data')->getCertNumber()));
        } else {
            return Mage::helper('ugiftcert')->__('New Gift Certificate');
        }
    }
}
