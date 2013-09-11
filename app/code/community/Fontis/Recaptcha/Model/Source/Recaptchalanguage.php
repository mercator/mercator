<?php
/**
 * Fontis Recaptcha Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Fontis
 * @package    Fontis_Recaptcha
 * @author     Denis Margetic
 * @author     Chris Norton
 * @copyright  Copyright (c) 2011 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Recaptcha_Model_Source_Recaptchalanguage
{
    public function toOptionArray()
    {
        return array(array('value' => 'en', 'label' => 'English'),
                     array('value' => 'fr', 'label' => 'French'),
                     array('value' => 'de', 'label' => 'German'),
                     array('value' => 'nl', 'label' => 'Dutch'),
                     array('value' => 'pt', 'label' => 'Portuguese'),
                     array('value' => 'ru', 'label' => 'Russian'),
                     array('value' => 'es', 'label' => 'Spanish'),
                     array('value' => 'tr', 'label' => 'Turkish'),
                    );
    }
}
