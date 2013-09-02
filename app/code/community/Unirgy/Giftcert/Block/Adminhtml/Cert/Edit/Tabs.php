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
class Unirgy_Giftcert_Block_Adminhtml_Cert_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('giftcert_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ugiftcert')->__('Manage Gift Certificates'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('ugiftcert')->__('Gift Certificate Information'),
            'title'     => Mage::helper('ugiftcert')->__('Gift Certificate Information'),
            'content'   => $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit_tab_form')->toHtml(),
        ));

        if ($id = Mage::app()->getRequest()->getParam('id')) {
            $this->addTab('history', array(
                'label'     => Mage::helper('ugiftcert')->__('Transaction History'),
                'title'     => Mage::helper('ugiftcert')->__('Transaction History'),
                'content'   => $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit_tab_history')
                    ->setCertId($id)
                    ->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }
}