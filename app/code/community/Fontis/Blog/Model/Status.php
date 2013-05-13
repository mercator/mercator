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

class Fontis_Blog_Model_Status extends Varien_Object
{
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 2;
    const STATUS_HIDDEN     = 3;

    public function addEnabledFilterToCollection($collection)
    {
        $collection->addEnableFilter(array("in" => $this->getEnabledStatusIds()));
        return $this;
    }

    public function addCatFilterToCollection($collection, $cat)
    {
        $collection->addCatFilter($cat);
        return $this;
    }

    public function getEnabledStatusIds()
    {
        return array(self::STATUS_ENABLED);
    }

    public function getDisabledStatusIds()
    {
        return array(self::STATUS_DISABLED);
    }

    public function getHiddenStatusIds()
    {
        return array(self::STATUS_HIDDEN);
    }

    static public function getOptionArray()
    {
        $blogHelper = Mage::helper("blog");
        return array(
            self::STATUS_ENABLED    => $blogHelper->__("Enabled"),
            self::STATUS_DISABLED   => $blogHelper->__("Disabled"),
            self::STATUS_HIDDEN     => $blogHelper->__("Hidden")
        );
    }
}
