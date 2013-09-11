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

class Fontis_Blog_Block_Manage_Cat_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $blogHelper = Mage::helper("blog");
        $form = new Varien_Data_Form(array(
            "id" => "edit_form",
            "action" => $this->getUrl("*/*/save", array("id" => $this->getRequest()->getParam("id"))),
            "method" => "post",
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset("category_form", array("legend" => $blogHelper->__("Category Information")));

        $fieldset->addField("title", "text", array(
            "label"     => $blogHelper->__("Title"),
            "name"      => "title",
            "required"  => true
        ));

        $fieldset->addField("identifier", "text", array(
            "label"     => $blogHelper->__("Identifier"),
            "name"      => "identifier",
            "required"  => true
        ));

        $fieldset->addField("sort_order", "text", array(
            "label"     => $blogHelper->__("Sort Order"),
            "name"      => "sort_order",
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField("store_id", "multiselect", array(
                "name"      => "stores[]",
                "label"     => Mage::helper("cms")->__("Store View"),
                "title"     => Mage::helper("cms")->__("Store View"),
                "required"  => true,
                "values"    => Mage::getSingleton("adminhtml/system_store")->getStoreValuesForForm(false, true),
            ));
        }

        $fieldset->addField("meta_keywords", "editor", array(
            "name" => "meta_keywords",
            "label" => $blogHelper->__("Keywords"),
            "title" => $blogHelper->__("Meta Keywords"),
        ));

        $fieldset->addField("meta_description", "editor", array(
            "name" => "meta_description",
            "label" => $blogHelper->__("Description"),
            "title" => $blogHelper->__("Meta Description"),
        ));

        if (Mage::getSingleton("adminhtml/session")->getBlogData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getBlogData());
            Mage::getSingleton("adminhtml/session")->setBlogData(null);
        } elseif (Mage::registry("blog_data")) {
            $form->setValues(Mage::registry("blog_data")->getData());
        }
        return parent::_prepareForm();
    }
}
