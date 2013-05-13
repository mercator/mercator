<?php
/**
 * Fontis Blog Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Parts of this software are derived from code originally developed by
 * Robert Chambers <magento@robertchambers.co.uk>
 * and released as "Lazzymonk's Blog" 0.5.8 in 2009.
 *
 * @category   Fontis
 * @package    Fontis_Blog
 * @copyright  Copyright (c) 2013 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Blog_Block_Manage_Comment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('commentGrid');
        $this->setDefaultSort('status');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('blog/comment')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $blogHelper = Mage::helper("blog");

        /*$this->addColumn('comment_id', array(
            'header'    => Mage::helper('blog')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'post_id',
        ));*/

        $this->addColumn('comment', array(
            'header'    => $blogHelper->__('Comment'),
            'align'     =>'left',
            'index'     => 'comment',
        ));


        $this->addColumn('user', array(
            'header'    => $blogHelper->__('Poster'),
            'width'     => '150px',
            'index'     => 'user',
        ));

        $this->addColumn('email', array(
            'header'    => $blogHelper->__('Email Address'),
            'width'     => '150px',
            'index'     => 'email',
        ));

        $this->addColumn('created_time', array(
            'header'    => $blogHelper->__('Created'),
            'align'     => 'center',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'created_time',
        ));

        $this->addColumn('status', array(
            'header'    => $blogHelper->__('Status'),
            'align'     => 'canter',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Unapproved',
                2 => 'Approved',
            ),
        ));

        $this->addColumn('action', array(
            'header'    =>  $blogHelper->__('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => $blogHelper->__('Approve'),
                    'url'       => array('base'=> '*/*/approve'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => $blogHelper->__('Unapprove'),
                    'url'       => array('base'=> '*/*/unapprove'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => $blogHelper->__('Delete'),
                    'url'       => array('base'=> '*/*/delete'),
                    'field'     => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
        ));

        // These are disabled because the controller actions haven't been written for them.
        //$this->addExportType("*/*/exportCsv", $blogHelper->__("CSV"));
        //$this->addExportType("*/*/exportXml", $blogHelper->__("XML"));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $blogHelper = Mage::helper("blog");
        $this->setMassactionIdField('post_id');
        $this->getMassactionBlock()->setFormFieldName('blog');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => $blogHelper->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => $blogHelper->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('approve', array(
             'label'    => $blogHelper->__('Approve'),
             'url'      => $this->getUrl('*/*/massApprove'),
             'confirm'  => $blogHelper->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('unapprove', array(
             'label'    => $blogHelper->__('Unapprove'),
             'url'      => $this->getUrl('*/*/massUnapprove'),
             'confirm'  => $blogHelper->__('Are you sure?')
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
