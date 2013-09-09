<?php
/**
 * @category    SchumacherFM_CmsRestriction
 * @package     Helper
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Magento-CmsRestriction/issues
 */
class SchumacherFM_CmsRestriction_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ACCESS_DENIED = 'cms/schumacherfm_cmsrestriction/url_access_denied';

    /**
     * the uber method
     * checks if a customer has the permission to view this cms page
     * calls also isCustomerAllowed() and isPageRestricted()
     *
     * @param Mage_Cms_Model_Page $page
     *
     * @return bool
     */
    public function isAllowed(Mage_Cms_Model_Page $page)
    {
        $isLoggedIn   = Mage::helper('customer')->isLoggedIn();
        $isRestricted = $this->isPageRestricted($page);

        return ($isLoggedIn && $isRestricted && $this->isCustomerAllowed($page));
    }

    public function isRenderingAllowed(Mage_Cms_Model_Page $page)
    {
        if (!$page->getIsActive()) {
            return FALSE;
        }

        $customerAllowed = $this->isCustomerAllowed($page);

        return $customerAllowed;
    }

    /**
     * @param Mage_Cms_Model_Page $page
     *
     * @return bool
     */
    public function isPageRestricted(Mage_Cms_Model_Page $page)
    {
        $allowCustomerIds    = $page->getAllowCustomerIds();
        $allowCustomerGroups = (int)$page->getAllowCustomerGroups();
        return ($allowCustomerGroups > 0 || !empty($allowCustomerIds));

    }

    /**
     * @param Mage_Cms_Model_Page $page
     *
     * @return bool
     */
    public function isCustomerAllowed(Mage_Cms_Model_Page $page)
    {

        $customer        = Mage::helper('customer')->getCustomer();
        $pageCustomerIds = array_flip(explode(',', $page->getAllowCustomerIds()));

        $isValidGroup      = ((pow(2, $customer->getGroupId()) & $page->getAllowCustomerGroups()) > 0);
        $isValidCustomerId = isset($pageCustomerIds[$customer->getEntityId()]);

        return ($isValidGroup || $isValidCustomerId);
    }

    public function getAccessDeniedUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_ACCESS_DENIED);
    }

    /**
     * @param integer $storeId
     *
     * @return string
     */
    public function getStoreName($storeId)
    {
        $storeId = (int)$storeId;
        /** @var $storeModel Mage_Core_Model_Store */
        $storeModel = Mage::getModel('core/store');
        $storeModel->load($storeId);

        $name        = '';
        $websiteName = '';
        if ($storeId === 0) {
            $name        = 'all store views';
            $websiteName = 'all websites';
        }

        if (( (int)$storeModel->getId() === $storeId) && $storeId) {
            $name        = $storeModel->getName();
            $websiteName = $storeModel->getWebsite()->getName();
        }

        return $websiteName . ' - ' . $name;
    }

    public function getPageEditLink($pageId)
    {
        if ($pageId) {
            return $this->_getUrl('*/cms_page/edit', array(
                '_current' => TRUE,
                'page_id'  => $pageId
            ));
        }

        return FALSE;
    }

    /**
     * @param array  $intArray like array(1,2,3,5,4,8,9,...)
     * @param string $useColumn
     *
     * @return bigint
     */
    public function getExpoSum($intArray, $useColumn = '')
    {
        $sum = 0;
        if (count($intArray) > 0) {
            foreach ($intArray as $intCol) {
                $int = ($useColumn === '') ? $intCol : $intCol[$useColumn];
                $sum += pow(2, intval($int));
            }
        }
        return $sum === 1 ? 0 : $sum;
    }

    /**
     * @param int $expoSum
     * @param int $maxValue
     *
     * @return array
     */
    public function getIntByExpoSum($expoSum, $maxValue = 35)
    {
        $a       = array();
        $expoSum = intval($expoSum);
        for ($i = 0; $i < $maxValue; $i++) {
            if (($expoSum & pow(2, $i)) > 0) {
                $a[$i] = $i;
            }
        }
        return count($a) === 0
            ? array(0)
            : $a;
    }
}