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

class Fontis_Blog_Model_Mysql4_Related extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {    
        $this->_init("blog/related", "post_id");
    }
	
	public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (strcmp($value, (int) $value) !== 0) {
            $field = "post_id";
        }
        return parent::load($object, $value, $field);
    }
}
