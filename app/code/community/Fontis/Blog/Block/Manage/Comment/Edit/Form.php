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

class Fontis_Blog_Block_Manage_Comment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $blogHelper = Mage::helper("blog");
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('comment_form', array('legend' => $blogHelper->__('Comment Information')));

        $fieldset->addField('user', 'text', array(
            'label'     => $blogHelper->__('User'),
            'name'      => 'user',
        ));

        $fieldset->addField('email', 'text', array(
            'label'     => $blogHelper->__('Email Address'),
            'name'      => 'email',
        ));

        $fieldset->addField('status', 'select', array(
            'label'     => $blogHelper->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => $blogHelper->__('Unapproved'),
                ),
                array(
                    'value'     => 2,
                    'label'     => $blogHelper->__('Approved'),
                ),
            ),
        ));

        $fieldset->addField('comment', 'editor', array(
            'name'      => 'comment',
            'label'     => $blogHelper->__('Comment'),
            'title'     => $blogHelper->__('Comment'),
            'style'     => 'width: 700px; height: 500px;',
            'wysiwyg'   => false,
            'required'  => false,
        ));

        if (Mage::getSingleton('adminhtml/session')->getBlogData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBlogData());
            Mage::getSingleton('adminhtml/session')->setBlogData(null);
        } elseif (Mage::registry('blog_data')) {
            $form->setValues(Mage::registry('blog_data')->getData());
        }
        return parent::_prepareForm();
    }
}
