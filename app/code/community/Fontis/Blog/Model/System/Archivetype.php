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

class Fontis_Blog_Model_System_Archivetype
{
    const YEARLY    = 1;
    const MONTHLY   = 2;
    const DAILY     = 3;

    public static $_options = array(
        self::YEARLY    => "Yearly",
        self::MONTHLY   => "Monthly",
        self::DAILY     => "Daily"
    );

    public static $_formats = array(
        self::YEARLY    => "Y",
        self::MONTHLY   => "F Y",
        self::DAILY     => "F jS, Y"
    );

    public function toOptionArray()
    {
        $helper = Mage::helper("blog");
        return array(
            array("value" => self::YEARLY,  "label" => $helper->__(self::getTypeLabel(self::YEARLY))),
            array("value" => self::MONTHLY, "label" => $helper->__(self::getTypeLabel(self::MONTHLY))),
            array("value" => self::DAILY,   "label" => $helper->__(self::getTypeLabel(self::DAILY)))
        );
    }

    public static function getTypeLabel($type)
    {
        if (isset(self::$_options[$type])) {
            return self::$_options[$type];
        } else {
            return null;
        }
    }

    public static function getTypeFormat($type)
    {
        if (isset(self::$_formats[$type])) {
            return self::$_formats[$type];
        } else {
            return null;
        }
    }
}
