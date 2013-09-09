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

class Yoast_MetaRobots_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        return array(
            'catalog_product' => array(
                'entity_model'      => 'catalog/product',
                'attribute_model'   => 'catalog/resource_eav_attribute',
                'table'             => 'catalog/product',
                'additional_attribute_table' => 'catalog/eav_attribute',
                'entity_attribute_collection' => 'catalog/product_attribute_collection',
                'attributes'        => array(
					'meta_robots' => array(
                        'group'             => 'Meta Information',
                        'type'              => 'text',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Meta Robots',
                        'input'             => 'select',
                        'class'             => '',
                        'source'            => 'metarobots/product_attribute_source_unit',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    )
                ),
            ),
        );
    }

}
		