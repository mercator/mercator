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

class Fontis_Blog_Block_Manage_Comment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = "id";
        $this->_blockGroup = "blog";
        $this->_controller = "manage_comment";
        
        $this->_updateButton("save", "label", Mage::helper("blog")->__("Save Comment"));
        $this->_updateButton("delete", "label", Mage::helper("blog")->__("Delete Comment"));

        $this->_addButton("saveandcontinue", array(
            "label"     => Mage::helper("adminhtml")->__("Save And Continue Edit"),
            "onclick"   => "saveAndContinueEdit()",
            "class"     => "save",
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('blog_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'blog_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'blog_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if(Mage::registry("blog_data") && Mage::registry("blog_data")->getId()) {
            return Mage::helper("blog")->__("Edit Comment By '%s'", $this->escapeHtml(Mage::registry("blog_data")->getUser()));
        } else {
            return Mage::helper("blog")->__("Add New Comment");
        }
    }
}
