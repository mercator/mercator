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
class Unirgy_Giftcert_Block_Adminhtml_Cert_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('giftcertGrid');
        $this->setDefaultSort('cert_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('cert_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ugiftcert/cert')->getCollection()->addHistory();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('ugiftcert');

        $this->addColumn('cert_id', array(
            'header'    => $hlp->__('Certificate ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'cert_id',
            'type'      => 'number',
        ));

        $this->addColumn('cert_number', array(
            'header'    => $hlp->__('Certificate Code'),
            'align'     => 'left',
            'index'     => 'cert_number',
        ));
        
        $this->addColumn('pos_number', array(
            'header'    => $hlp->__('POS Number'),
            'align'     => 'left',
            'index'     => 'pos_number',
        ));

        $this->addColumn('amount', array(
            'header'    => $hlp->__('Initial amount'),
            'align'     => 'right',
            'index'     => 'amount',
            'type'      => 'currency',
            'currency'  => 'currency_code',
        ));

        $this->addColumn('balance', array(
            'header'    => $hlp->__('Balance'),
            'align'     => 'right',
            'index'     => 'balance',
            'type'      => 'currency',
            'currency'  => 'currency_code',
        ));

        $this->addColumn('status', array(
            'header'    => $hlp->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'filter_index' => 'main_table.status',
            'options'   => array(
                'P' => $hlp->__('Pending'),
                'A' => $hlp->__('Active'),
                'I' => $hlp->__('Inactive'),
            ),
        ));

        $this->addColumn('customer_email', array(
            'header'    => $hlp->__('Customer Created'),
            'align'     => 'left',
            'index'     => 'customer_email',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => $hlp->__('Order ID'),
            'align'     => 'left',
            'index'     => 'order_increment_id',
        ));

        $this->addColumn('ts', array(
            'header'    => $hlp->__('Created At'),
            'align'     => 'left',
            'index'     => 'ts',
            'type'      => 'datetime',
            'width'     => '160px',
        ));

        $this->addColumn('expire_at', array(
            'header'    => $hlp->__('Expires On'),
            'align'     => 'left',
            'index'     => 'expire_at',
            'type'      => 'date',
            'width'     => '120px',
        ));

        $this->addColumn('store_id', array(
            'header'    => $this->__('Store View'),
            'width'     => '200px',
            'index'     => 'store_id',
            'type'      => 'store',
            'store_all'  => false,
            'store_view' => true,
        ));

        $this->addColumn('username', array(
            'header'    => $hlp->__('Admin Created'),
            'align'     => 'left',
            'index'     => 'username',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $block = $this->getMassactionBlock();

        $block->setFormFieldName('cert');

        $block->addItem('delete', array(
             'label'=> Mage::helper('ugiftcert')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('ugiftcert')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ugiftcert/status')->toOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $block->addItem('status', array(
             'label'=> Mage::helper('ugiftcert')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'status' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('ugiftcert')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        $block->addItem('email', array(
             'label'=> Mage::helper('ugiftcert')->__('Send emails'),
             'url'  => $this->getUrl('*/*/massEmail'),
             'confirm' => Mage::helper('ugiftcert')->__('Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
