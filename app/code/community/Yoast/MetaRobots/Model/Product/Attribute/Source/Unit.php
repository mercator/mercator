<?php
/**
 *
 * @category   Yoast
 * @package    Yoast_MetaRobots
 * @copyright  Copyright (c) 2009-2010 Yoast (http://www.yoast.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Yoast
 * @package    Yoast_MetaRobots
 * @author     Yoast <magento@yoast.com>
 */
  
  class Yoast_MetaRobots_Model_Product_Attribute_Source_Unit extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
  {          
	  public function getAllOptions()

          {
              if (!$this->_options) {
                  $this->_options = array(
					array(
                          'value' => 'INDEX, FOLLOW',
                          'label' => 'INDEX, FOLLOW',
                      ),
                     array(
                          'value' => 'INDEX, NOFOLLOW',
                          'label' => 'INDEX, NOFOLLOW',
                      ),
                     array(
                          'value' => 'NOINDEX, FOLLOW',
                          'label' => 'NOINDEX, FOLLOW',
                      ),
                     array(
                          'value' => 'NOINDEX, NOFOLLOW',
                          'label' => 'NOINDEX, NOFOLLOW',
                      ),
                     array(
                          'value' => 'INDEX, FOLLOW, NOARCHIVE',
                          'label' => 'INDEX, FOLLOW, NOARCHIVE',
                      ),
                     array(
                          'value' => 'INDEX, NOFOLLOW, NOARCHIVE',
                          'label' => 'INDEX, NOFOLLOW, NOARCHIVE',
                      ),
                     array(
                          'value' => 'NOINDEX, NOFOLLOW, NOARCHIVE',
                          'label' => 'NOINDEX, NOFOLLOW, NOARCHIVE',
                      )
                  );
              }
              return $this->_options;
          }
}