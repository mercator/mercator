<?php
/**
 * @category    SchumacherFM_CmsRestriction
 * @package     Model
 * @author      Cyrill at Schumacher dot fm (@SchumacherFM)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Magento-CmsRestriction/issues
 */
class SchumacherFM_CmsRestriction_Model_Observer
{
    const SESSION_AFTER_LOGIN_URL = 'SchumacherFMCmsRestrictionAfterLoginUrl';

    /**
     * @param Varien_Event_Observer $observer
     */
    public function updateCmsRestriction(Varien_Event_Observer $observer)
    {

        /** @var $page Mage_Cms_Model_Page */
        $page = $observer->getEvent()->getPage();

        $allowedCustomerGroups = (array)$page->getAllowCustomerGroups();
        $allowedCustomerIds    = preg_replace('~[^0-9,]+~', '', $page->getAllowCustomerIds());

        $page->setAllowCustomerGroups(Mage::helper('schumacherfm_cmsrestriction')->getExpoSum($allowedCustomerGroups));
        $page->setAllowCustomerIds($allowedCustomerIds);

    }

    /**
     * @var Mage_Core_Controller_Response_Http
     */
    protected $_response;

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @var Mage_Cms_PageController
     */
    protected $_controller;

    /**
     * the requested page
     * @var string
     */
    protected $_pageIdentifier;

    /**
     * @param Varien_Event_Observer $observer
     */
    protected function _initProperties(Varien_Event_Observer $observer)
    {
        $this->_controller     = $observer->getEvent()->getControllerAction();
        $this->_request        = $this->_controller->getRequest();
        $this->_response       = $this->_controller->getResponse();
        $this->_pageIdentifier = $this->_request->getAlias('rewrite_request_path');

    }

    /**
     * observer will only be called at
     *  controller_action_postdispatch_customer_account_loginPost
     * because the logged in customer object is not available in the pre dispatch!
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictCmsPageAfterLogin(Varien_Event_Observer $observer)
    {
        $this->_initProperties($observer);

        /* we keep these two var just to be sure it is called correctly :-| */
        $isMageCustomerAccountController = $this->_controller instanceof Mage_Customer_AccountController;
        $isCustomerLoginPost             = $this->_request->getActionName() === 'loginPost';

        if ($isMageCustomerAccountController && $isCustomerLoginPost && $this->_hasSessionRedirectUrl()) {

            $this->_pageIdentifier = $this->_getSessionRedirectUrl();

            $url = Mage::helper('schumacherfm_cmsrestriction')->isCustomerAllowed($this->_getPageModelInstance())
                ? $this->_pageIdentifier
                : Mage::helper('schumacherfm_cmsrestriction')->getAccessDeniedUrl();

            $this->_unsSessionRedirectUrl();

            $this->_handleRedirect($url);

        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function restrictCmsPage(Varien_Event_Observer $observer)
    {
        $this->_initProperties($observer);

        if ($this->_getPageModelInstance() && !Mage::app()->getStore()->isAdmin()) {

            $isLoggedIn   = Mage::helper('customer')->isLoggedIn();
            $isPageRestricted = Mage::helper('schumacherfm_cmsrestriction')->isPageRestricted($this->_getPageModelInstance());

            $isCustomerAllowed = Mage::helper('schumacherfm_cmsrestriction')->isCustomerAllowed($this->_getPageModelInstance());

            if (!$isLoggedIn && $isPageRestricted) {

                $this->_handleRedirect(Mage_Customer_Helper_Data::ROUTE_ACCOUNT_LOGIN);
                $this->_setSessionRedirectUrl($this->_pageIdentifier);

            } elseif ($isLoggedIn && $isPageRestricted && !$isCustomerAllowed) {

                /* a logged in user has no permission to view this page */
                $this->_handleRedirect(Mage::helper('schumacherfm_cmsrestriction')->getAccessDeniedUrl());

            }

        }

    }

    /**
     * @var Mage_Cms_Model_Page
     */
    protected $_pageModel;

    /**
     * @return Mage_Cms_Model_Page
     */
    protected function _getPageModelInstance()
    {

        if (empty($this->_pageModel)) {
            $this->_pageModel = Mage::getModel('cms/page')->load($this->_pageIdentifier, 'identifier');

            if (!is_object($this->_pageModel)) {
                $this->_pageModel = FALSE;
            }
        }

        return $this->_pageModel;
    }

    /**
     * @param $url
     */
    protected function _handleRedirect($url)
    {
        $redirectUrl = Mage::getUrl($url);

        $this->_response->setRedirect($redirectUrl);
        $this->_controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, TRUE);

    }

    protected function _setSessionRedirectUrl($url)
    {
        Mage::getSingleton('core/session')->{'set' . self::SESSION_AFTER_LOGIN_URL}($url);
    }

    /**
     * @return string
     */
    protected function _getSessionRedirectUrl()
    {
        return Mage::getSingleton('core/session')->{'get' . self::SESSION_AFTER_LOGIN_URL}();
    }

    /**
     * @return bool
     */
    protected function _hasSessionRedirectUrl()
    {
        return (boolean)Mage::getSingleton('core/session')->{'has' . self::SESSION_AFTER_LOGIN_URL}();
    }

    protected function _unsSessionRedirectUrl()
    {
        return (boolean)Mage::getSingleton('core/session')->{'uns' . self::SESSION_AFTER_LOGIN_URL}();
    }

}