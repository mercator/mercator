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

class Fontis_Blog_Model_System_Dateformat
{
    protected $_options;
    const FORMAT_TYPE_FULL      = "full";
    const FORMAT_TYPE_LONG      = "long";
    const FORMAT_TYPE_MEDIUM    = "medium";
    const FORMAT_TYPE_SHORT     = "short";

    public function toOptionArray()
    {
        if (!$this->_options) {
            $blogHelper = Mage::helper("blog");
            $this->_options[] = array(
                "value" => self::FORMAT_TYPE_FULL,
                "label" => $blogHelper->__("Full")
            );
            $this->_options[] = array(
                "value" => self::FORMAT_TYPE_LONG,
                "label" => $blogHelper->__("Long")
            );
            $this->_options[] = array(
                "value" => self::FORMAT_TYPE_MEDIUM,
                "label" => $blogHelper->__("Medium")
            );
            $this->_options[] = array(
                "value" => self::FORMAT_TYPE_SHORT,
                "label" => $blogHelper->__("Short")
            );
        }
        return $this->_options;
    }
}
