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
class Unirgy_Giftcert_Block_Cart_Item extends Mage_Checkout_Block_Cart_Item_Renderer
{
    public function getProductOptions()
    {
        $options = parent::getProductOptions();
        foreach (Mage::helper('ugiftcert')->getGiftcertOptionVars() as $code=>$label) {
            if ($option = $this->getItem()->getOptionByCode($code)) {
                $options[] = array(
                    'label' => $label,
                    'value' => $this->htmlEscape($option->getValue()),
                );
            }
        }
        return $options;
    }
}
