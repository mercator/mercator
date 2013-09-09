<?php
/**
 * @category    SchumacherFM_CmsRestriction
 * @package     Model
 * @author      Cyrill at Schumacher dot fm (@SchumacherFM)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Magento-CmsRestriction/issues
 */
class SchumacherFM_CmsRestriction_Model_Option_Groups
{
    /**
     * Get types as a source model result
     *
     * @return array
     */
    public function toOptionArray()
    {
        $groups     = array();
        $collection = Mage::helper('customer')->getGroups();

        foreach ($collection as $group) {
            $groups[] = array(
                'value' => $group->getCustomerGroupId(),
                'label' => $group->getCustomerGroupCode()
            );
        }

        return $groups;
    }
}
