<?php
/**
 *
 * @category   Yoast
 * @package    Yoast_MetaRobots
 * @copyright  Copyright (c) 2009-2010 Yoast (http://www.yoast.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Yoast
 * @package    Yoast_MetaRobots
 * @author     Yoast <magento@yoast.com>
 */
 class Yoast_MetaRobots_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
 {
	protected function _prepareLayout()
    {
        $this->getLayout()->createBlock('catalog/breadcrumbs');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $product = $this->getProduct();
            $title = $product->getMetaTitle();
            if ($title) {
                $headBlock->setTitle($title);
            }
            $keyword = $product->getMetaKeyword();
            $currentCategory = Mage::registry('current_category');
            if ($keyword) {
                $headBlock->setKeywords($keyword);
            } elseif($currentCategory) {
                $headBlock->setKeywords($product->getName());
            }
            $description = $product->getMetaDescription();
            if ($description) {
                $headBlock->setDescription( ($description) );
            } else {
                $headBlock->setDescription($product->getDescription());
            }

			$robots = $product->getMetaRobots();
			if ($robots) {
				$headBlock->setRobots($robots);
            } else {
                $headBlock->setRobots($product->getMetaRobots());
            }

			if ($this->helper('catalog/product')->canUseCanonicalTag()) {
                $params = array('_ignore_category'=>true);
                $headBlock->addLinkRel('canonical', $product->getUrlModel()->getUrl($product, $params));
            }
        }
        return parent::_prepareLayout();
    }
 }