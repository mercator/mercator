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

class Fontis_Blog_Block_Manage_Blog_Edit_Tab_Related extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("blog_related");
        $this->setDefaultSort("id");
        //$this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == "related_product") {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter("entity_id", array("in" => $productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter("entity_id", array("nin" => $productIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("catalog/product")->getCollection()
            ->addAttributeToSelect("name")
            ->addAttributeToSelect("sku")
            ->addAttributeToSelect("price")
            ->addStoreFilter($this->getRequest()->getParam("store"))
            ->joinField("position",
                "catalog/category_product",
                "position",
                "product_id=entity_id",
                "category_id=" . (int) $this->getRequest()->getParam("id", 0),
                "left"
            );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('related_product', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'related_product',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));
        $this->addColumn('id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '120px',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));
        $this->addColumn('position', array(
            'header'            => Mage::helper('catalog')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'width'             => '60px',
            'editable'          => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _getProduct()
    {
        return Mage::registry("blog_data");
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost("related_product");
        if (is_null($products)) {
            $collection = Mage::getModel("blog/related")->getCollection()
                ->addPostFilter(Mage::registry("blog_data")->getId());

            foreach ($collection as $product) {
                $products[] = $product->getProductId();
            }
        }
        return $products;
    }
}