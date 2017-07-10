<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SeoTagUrls
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_CouponUrl_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
	/**
	 * Parameter
	 *
	 * @param string
	*/
	const PARAM_IGNORE_URL_PARAM_CHECK = '_ignore_check_for_url_params';
	
	/**
	 * Initialize Controller Router
	 *
	 * @param Varien_Event_Observer $observer
	*/
	public function initControllerRouters(Varien_Event_Observer $observer)
	{
		$observer->getEvent()->getFront()->addRouter('couponurl', $this);
	}

    /**
     * Validate and Match the route against the module
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
    	$helper = Mage::helper('couponurl');
    	$urlKey = trim($request->getPathInfo(), '/');

    	$frontName = $helper->getFrontName();
    	
    	if (substr($urlKey, 0, strlen($frontName)) !== $frontName) {
			return false;
    	}

		$code = urldecode(substr($urlKey, strlen($frontName)+1));
		$coupon = Mage::getModel('salesrule/coupon')->loadByCode($code);
		
		if (!$coupon->getId()) {
			return false;
		}

		Mage::register('couponurl_coupon', $coupon);

		$request->setModuleName('couponurl')
			->setControllerName('coupon')
			->setActionName('apply')
			->setParam($helper->getParamName(), $coupon->getCode())
			->setParam(self::PARAM_IGNORE_URL_PARAM_CHECK, 1);

		$request->setAlias(
			Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
			$urlKey
		);

		return true;
	}

	/**
	 * Check the URL for the coupon parameter
	 *
	 * @return $this
	 */
	public function checkForUrlParamsObserver()
	{
		if (Mage::app()->getRequest()->getParam(Fishpig_CouponUrl_Helper_Data::REDIRECT_PARAM)) {
			return $this;
		}
		
		if (Mage::app()->getRequest()->getParam(self::PARAM_IGNORE_URL_PARAM_CHECK)) {
			return $this;
		}
				
		$couponCode = Mage::app()->getRequest()->getParam(
			Mage::helper('couponurl')->getParamName()
		);
		
		if (!$couponCode) {
			return $this;
		}
		
		if (is_array($couponCode)) {
			foreach($couponCode as $key => $value) {
				if (!$value) {
					unset($couponCode[$key]);
				}
			}
		}
		
		if (!$couponCode) {
			return $this;
		}
		
		header('Location: ' . Mage::helper('couponurl')->getUrlByCouponCode($couponCode, $this->_getCurrentUrl()));
		exit;
	}
	
	/**
	 * Get the current URL
	 *
	 * @return string
	 */
	protected function _getCurrentUrl()
	{
		return Mage::getUrl('*/*/*', array(
			'_query' => array(Mage::helper('couponurl')->getParamName() => null), 
			'_current' => true,
			'_use_rewrite' => true));
	}
}
