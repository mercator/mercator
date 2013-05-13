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

class Fontis_Blog_Block_Manage_Cat extends Fontis_Blog_Block_Manage_Abstract
{
    public function __construct()
    {
        $this->_controller = "manage_cat";
        $this->_blockGroup = "blog";
        $this->_headerText = Mage::helper("blog")->__("Blog Category Manager");
        parent::__construct();
        $this->setTemplate("blog/cats.phtml");
    }

    protected function _prepareLayout()
    {
        $this->setChild("add_new_button",
            $this->getLayout()->createBlock("adminhtml/widget_button")
                ->setData(array(
                    "label"     => Mage::helper("blog")->__("Add Category"),
                    "onclick"   => "setLocation('" . $this->getUrl("*/*/new") . "')",
                    "class"     => "add"
                ))
        );

        /**
         * Display store switcher if system has more one store
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild("store_switcher",
                $this->getLayout()->createBlock("adminhtml/store_switcher")
                    ->setUseConfirm(false)
                    ->setSwitchUrl($this->getUrl("*/*/*", array("store" => null)))
            );
        }
        $this->setChild("grid", $this->getLayout()->createBlock("blog/manage_cat_grid", "blog.grid"));
        return parent::_prepareLayout();
    }
}
