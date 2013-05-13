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

class Fontis_Blog_Block_Manage_Blog_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("blog_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("blog")->__("Post Information"));
    }

    protected function _beforeToHtml()
    {
        $blogHelper = Mage::helper("blog");
        $formSection = $blogHelper->__("Post Information");
        $optionsSection = $blogHelper->__("Advanced Options");
        //$relatedSection = $blogHelper->__("Related Products");
        $layout = $this->getLayout();

        $this->addTab("form_section", array(
            "label"     => $formSection,
            "title"     => $formSection,
            "content"   => $layout->createBlock("blog/manage_blog_edit_tab_form")->toHtml(),
        ));

        $this->addTab("options_section", array(
            "label"     => $optionsSection,
            "title"     => $optionsSection,
            "content"   => $layout->createBlock("blog/manage_blog_edit_tab_options")->toHtml(),
        ));

        /*
        $this->addTab("related_section", array(
            "label"     => $relatedSection,
            "title"     => $relatedSection,
            "content"   => $layout->createBlock("blog/manage_blog_edit_tab_related")->toHtml(),
        ));
        */

        return parent::_beforeToHtml();
    }
}
