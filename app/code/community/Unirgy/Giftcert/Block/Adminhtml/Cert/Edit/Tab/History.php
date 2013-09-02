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
class Unirgy_Giftcert_Block_Adminhtml_Cert_Edit_Tab_History extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('cert_history_grid');
        $this->setDefaultSort('ts');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $id = Mage::app()->getRequest()->getParam('id');
        $collection = Mage::getModel('ugiftcert/history')
            ->getCollection()
            ->addCertFilter($id);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('ugiftcert');

        $this->addColumn('ts', array(
            'header'    => $hlp->__('Timestamp'),
            'align'     => 'left',
            'index'     => 'ts',
            'type'      => 'datetime',
            'width'     => '160px',
        ));

        $this->addColumn('action_code', array(
            'header'    => $hlp->__('Action'),
            'align'     => 'left',
            'index'     => 'action_code',
            'type'      => 'options',
            'options'   => array(
                'create' => $hlp->__('Create'),
                'update' => $hlp->__('Update'),
                'email' => $hlp->__('Email'),
                'order' => $hlp->__('Order'),
                'refund' => $hlp->__('Refund'),
            ),
        ));

        $this->addColumn('amount', array(
            'header'    => $hlp->__('Amount'),
            'align'     => 'right',
            'index'     => 'amount',
            'type'      => 'currency',
            'currency'  => 'currency_code',
        ));

        $this->addColumn('status', array(
            'header'    => $hlp->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                'P' => $hlp->__('Pending'),
                'A' => $hlp->__('Active'),
                'I' => $hlp->__('Inactive'),
            ),
        ));

        $this->addColumn('customer_email', array(
            'header'    => $hlp->__('Customer Email'),
            'align'     => 'left',
            'index'     => 'customer_email',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => $hlp->__('Order ID'),
            'align'     => 'left',
            'index'     => 'order_increment_id',
        ));

        $this->addColumn('username', array(
            'header'    => $hlp->__('Username'),
            'align'     => 'left',
            'index'     => 'username',
        ));

        return parent::_prepareColumns();
    }

    protected function getRowUrl($row)
    {
        return false;
        return $this->getUrl('*/tag/edit', array(
            'tag_id' => $row->getTagId(),
            'product_id' => $this->getProductId(),
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/historyGrid', array(
            '_current' => true,
            'id'       => $this->getCertId(),
        ));
    }
}
