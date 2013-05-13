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

class Fontis_Blog_Block_Manage_Blog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('blogGrid');
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('blog/blog')->getCollection();
        $store = $this->_getStore();
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $blogHelper = Mage::helper("blog");

        $this->addColumn("post_id", array(
            "header"    => $blogHelper->__("ID"),
            "align"     => "right",
            "width"     => "50px",
            "index"     => "post_id",
        ));

        $this->addColumn("title", array(
            "header"    => $blogHelper->__("Title"),
            "align"     => "left",
            "index"     => "title",
        ));

        $this->addColumn("identifier", array(
            "header"    => $blogHelper->__("Identifier"),
            "align"     => "left",
            "index"     => "identifier",
        ));

        $this->addColumn("user", array(
            "header"    => $blogHelper->__("Poster"),
            "width"     => "150px",
            "index"     => "user",
        ));


        $this->addColumn("created_time", array(
            "header"    => $blogHelper->__("Created"),
            "align"     => "left",
            "width"     => "120px",
            "type"      => "date",
            "default"   => "--",
            "index"     => "created_time",
        ));

        $this->addColumn("update_time", array(
            "header"    => $blogHelper->__("Updated"),
            "align"     => "left",
            "width"     => "120px",
            "type"      => "date",
            "default"   => "--",
            "index"     => "update_time",
        ));

        $this->addColumn('status', array(
            "header"    => $blogHelper->__('Status'),
            "align"     => "left",
            "width"     => "80px",
            "index"     => "status",
            "type"      => "options",
            "options"   => Mage::getSingleton("blog/status")->getOptionArray(),
        ));

        $this->addColumn("action",
            array(
                "header"    =>  $blogHelper->__("Action"),
                "width"     => "100px",
                "type"      => "action",
                "getter"    => "getId",
                "actions"   => array(
                    array(
                        "caption"   => $blogHelper->__("Edit"),
                        "url"       => array("base" => "*/*/edit"),
                        "field"     => "id"
                    )
                ),
                "filter"    => false,
                "sortable"  => false,
                "index"     => "stores",
                "is_system" => true,
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $blogHelper = Mage::helper("blog");
        $this->setMassactionIdField("post_id");
        $this->getMassactionBlock()->setFormFieldName("blog");

        $this->getMassactionBlock()->addItem("delete", array(
             "label"    => $blogHelper->__("Delete"),
             "url"      => $this->getUrl("*/*/massDelete"),
             "confirm"  => $blogHelper->__("Are you sure?")
        ));

        $statuses = Mage::getSingleton("blog/status")->getOptionArray();

        array_unshift($statuses, array("label" => "", "value" => ""));
        $this->getMassactionBlock()->addItem("status", array(
            "label" => $blogHelper->__("Change status"),
            "url"   => $this->getUrl("*/*/massStatus", array("_current" => true)),
            "additional" => array(
                "visibility" => array(
                     "name"     => "status",
                     "type"     => "select",
                     "class"    => "required-entry",
                     "label"    => $blogHelper->__("Status"),
                     "values"   => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }
}
