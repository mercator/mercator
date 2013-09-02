<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Model_Status
{
    public function getAllOptions()
    {
        $hlp = Mage::helper('ugiftcert');
        return array(
            array('label'=>$hlp->__('Pending'), 'value'=>'P'),
            array('label'=>$hlp->__('Active'), 'value'=>'A'),
            array('label'=>$hlp->__('Inactive'), 'value'=>'I'),

        );
    }

    public function toOptionArray()
    {
        $hlp = Mage::helper('ugiftcert');
        return array('P'=>$hlp->__('Pending'), 'A'=>$hlp->__('Active'), 'I'=>$hlp->__('Inactive'));
    }
}
