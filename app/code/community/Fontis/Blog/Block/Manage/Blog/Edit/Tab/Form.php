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

class Fontis_Blog_Block_Manage_Blog_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $blogHelper = Mage::helper("blog");
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("blog_form", array("legend" => $blogHelper->__("Post Information")));

        $fieldset->addField("title", "text", array(
            "label"     => $blogHelper->__("Title"),
            "class"     => "required-entry",
            "required"  => true,
            "name"      => "title",
        ));

        $fieldset->addField("identifier", "text", array(
            "label"                 => $blogHelper->__("Identifier"),
            "class"                 => "required-entry",
            "required"              => true,
            "name"                  => "identifier",
            "class"                 => "validate-identifier",
            "after_element_html"    => '<span class="hint">&nbsp;eg: domain.com/blog/identifier</span>',
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

        $categories = array();
        $collection = Mage::getModel("blog/cat")->getCollection()->setOrder("sort_order", "asc");
        foreach ($collection as $cat) {
            $categories[] = (array(
                "label" => (string) $cat->getTitle(),
                "value" => $cat->getCatId()
            ));
        }

        $fieldset->addField("cats", "multiselect", array(
            "name"      => "cats[]",
            "label"     => $blogHelper->__("Category"),
            "title"     => $blogHelper->__("Category"),
            "required"  => true,
            "values"    => $categories,
        ));

        $fieldset->addField("status", "select", array(
            "label"     => $blogHelper->__("Status"),
            "name"      => "status",
            "values"    => array(
                array(
                    "value"   => Fontis_Blog_Model_Status::STATUS_ENABLED,
                    "label"   => $blogHelper->__("Enabled"),
                ),
                array(
                    "value"   => Fontis_Blog_Model_Status::STATUS_DISABLED,
                    "label"   => $blogHelper->__("Disabled"),
                ),
                array(
                    "value"   => Fontis_Blog_Model_Status::STATUS_HIDDEN,
                    "label"   => $blogHelper->__("Hidden"),
                ),
            ),
            "style"     => "width: 152px;",
            "after_element_html" => '<span class="hint">&nbsp;Hidden posts will not show in the blog but can still be accessed directly.</span>',
        ));

        $fieldset->addField("comments", "select", array(
            "label"     => $blogHelper->__("Enable Comments"),
            "name"      => "comments",
            "values"    => array(
                array(
                    "value" => 0,
                    "label" => $blogHelper->__("Enabled"),
                ),
                array(
                    "value" => 1,
                    "label" => $blogHelper->__("Disabled"),
                ),
            ),
            "style"     => "width: 152px;",
            "after_element_html" => '<span class="hint">&nbsp;Disabling will close the post to new comments. It will not hide existing comments.</span>',
        ));

        $wysiwyg = Mage::getSingleton("cms/wysiwyg_config");
        $isGlobalWysiwygEnabled = $wysiwyg->isEnabled();
        $wysiwygConfig = array(
            "add_variables" => false,
            "add_widgets"   => true,
            "add_images"    => true,
        );

        $summaryWysiwygState = Mage::getStoreConfig("fontis_blog/blog/wysiwyg_summary");
        $summaryWysiwygEnabled = $isGlobalWysiwygEnabled && ($summaryWysiwygState == Fontis_Blog_Model_System_Wysiwygenabled::WYSIWYG_DEFAULT ? true : $this->isWysiwygEnabled($summaryWysiwygState));
        $summaryWysiwygConfig = $summaryWysiwygEnabled ? $wysiwyg->getConfig(array_merge($this->wysiwygInitialise($summaryWysiwygState), $wysiwygConfig)) : null;
        $fieldset->addField("summary_content", "editor", array(
            "name"      => "summary_content",
            "label"     => $blogHelper->__("Summary Content"),
            "title"     => $blogHelper->__("Summary Content"),
            "style"     => "width: 600px; height: 180px;",
            "wysiwyg"   => $summaryWysiwygEnabled,
            "config"    => $summaryWysiwygConfig
        ));

        $postWysiwygState = Mage::getStoreConfig("fontis_blog/blog/wysiwyg_post");
        $postWysiwygEnabled = $isGlobalWysiwygEnabled && ($postWysiwygState == Fontis_Blog_Model_System_Wysiwygenabled::WYSIWYG_DEFAULT ? true : $this->isWysiwygEnabled($postWysiwygState));
        $postWysiwygConfig = $postWysiwygEnabled ? $wysiwyg->getConfig(array_merge($this->wysiwygInitialise($postWysiwygState), $wysiwygConfig)) : null;
        $fieldset->addField("post_content", "editor", array(
            "name"      => "post_content",
            "label"     => $blogHelper->__("Content"),
            "title"     => $blogHelper->__("Content"),
            "style"     => "width: 600px; height: 360px;",
            "wysiwyg"   => $postWysiwygEnabled,
            "config"    => $postWysiwygConfig
        ));

        $session = Mage::getSingleton("adminhtml/session");
        if ($blogData = $session->getBlogData()) {
            $form->setValues($blogData);
            $session->setBlogData(null);
        } elseif (Mage::registry("blog_data")) {
            $form->setValues(Mage::registry("blog_data")->getData());
        }

        return parent::_prepareForm();
    }

    protected function wysiwygInitialise($state)
    {
        if ($state == Fontis_Blog_Model_System_Wysiwygenabled::WYSIWYG_DEFAULT) {
            $wysiwyg = Mage::getSingleton("cms/wysiwyg_config");
            return array(
                "enabled"   => $wysiwyg->isEnabled(),
                "hidden"    => $wysiwyg->isHidden()
            );
        } else {
            return array(
                "enabled"   => $this->isWysiwygEnabled($state),
                "hidden"    => $this->isWysiwygHidden($state)
            );
        }
    }

    protected function isWysiwygEnabled($state)
    {
        return in_array($state, array(Mage_Cms_Model_Wysiwyg_Config::WYSIWYG_ENABLED, Mage_Cms_Model_Wysiwyg_Config::WYSIWYG_HIDDEN));
    }

    protected function isWysiwygHidden($state)
    {
        return $state == Mage_Cms_Model_Wysiwyg_Config::WYSIWYG_HIDDEN;
    }
}
