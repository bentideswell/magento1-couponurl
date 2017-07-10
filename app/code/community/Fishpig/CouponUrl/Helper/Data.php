<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SeoTagUrls
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_CouponUrl_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * 
	 *
	 * @var string
	*/
	const REDIRECT_PARAM = '_redir';
	
	/**
	 * Retrieve the extensions front name
	 *
	 * @return string
	 */
	public function getFrontName()
	{
		return ($front = trim(Mage::getStoreConfig('couponurl/settings/route'), '/, ')) !== ''
			? $front
			: 'coupon';
	}
	
	/**
	 * Apply the discount when adding a product to the cart
	 * This is required because we can't add a coupon before a product is in the basket
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function applyCouponAfterAddingProductObserver(Varien_Event_Observer $observer)
	{
		if ($couponCode = Mage::getSingleton('checkout/session')->getCouponCode()) {
			Mage::getSingleton('checkout/cart')->getQuote()
				->setCouponCode($couponCode)
				->save();
		}
	}
	
	/**
	 * Apply the coupon observer
	 *
	 * @param Varien_Event_Observer $obsever
	 * @return $this
	 */
	public function applyCouponObserver(Varien_Event_Observer $observer)
	{
		if ($this->canRun()) {		
			$this->applyCoupon();
		}
		
		return $this;
	}
	
	/**
	 * Apply the coupon
	 *
	 * @return false|Mage_Promo_Model_Coupon
	 */
	public function applyCoupon()
	{
		if (($coupon = $this->_initCoupon()) !== false) {
			// Set the coupon code in the session
			Mage::getSingleton('checkout/session')
				->setCouponCode($coupon->getCode());

			// Apply the coupon code
			Mage::getSingleton('checkout/cart')->getQuote()
				->setCouponCode($coupon->getCode())
				->save();
			
			return $coupon;
		}
		
		return false;
	}
	
	/**
	 * Initialise the Splash Gourp model
	 *
	 * @return false|Mage_Salesrule_Model_Coupon
	 */
	protected function _initCoupon()
	{
		if (($coupon = Mage::registry('couponurl_coupon')) !== null) {
			return $coupon;
		}

		$coupon = Mage::getModel('salesrule/coupon')->loadByCode(
			Mage::app()->getRequest()->getParam($this->getParamName(), false)
		);

		if (!$coupon->getId()) {
			return false;
		}
		
		Mage::register('couponurl_coupon', $coupon);

		return $coupon;
	}
	
	/**
	 * Retrieve the parameter name
	 *
	 * @return string
	 */
	public function getParamName()
	{
		return ($param = trim(Mage::getStoreConfig('couponurl/settings/param'))) !== ''
			? $param
			: 'coupon_url';
	}
	
	/**
	 * Determine whether the extension is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::getStoreConfigFlag('couponurl/settings/enabled');
	}
	
	/**
	 * Get redirect URL on success
	 *
	 * @return string
	 */
	public function getSuccessRedirect()
	{
		$redirectUrl = Mage::app()->getRequest()->getParam(Fishpig_CouponUrl_Helper_Data::REDIRECT_PARAM);
		
		if ($redirectUrl) {
			return base64_decode($redirectUrl);
		}
		
		return ($redirect = trim(Mage::getStoreConfig('couponurl/success/redirect_to'))) !== ''
			? $redirect
			: str_replace('/index.php/', '/', Mage::getUrl());
	}
	
	/**
	 * Get redirect URL on success
	 *
	 * @return string
	 */
	public function getSuccessMessage()
	{
		return Mage::getStoreConfig('couponurl/success/message');
	}
	
	/**
	 * Get the URL for the coupon
	 *
	 * @param Mage_SalesRule_Model_Rule $coupon
	 * @return false|string
	 */
	public function getUrlByCoupon(Mage_SalesRule_Model_Rule $coupon)
	{
		return $coupon->getId() && $coupon->getCouponCode()
			? $this->getUrlByCouponCode($coupon->getCouponCode())
			: false;
	}
	
	/**
      * Get the URL for the given coupon code
      *
      * @param string $couponCode
      * @return string
      */
	public function getUrlByCouponCode($couponCode, $redirect = null)
	{
		if ($redirect) {
			$redirect = base64_encode($redirect);
		}
		
		$url = Mage::getUrl('', array(
			'_direct' => $this->getFrontName() . '/' . $couponCode . '/', 
			'_query' => array(Fishpig_CouponUrl_Helper_Data::REDIRECT_PARAM => $redirect)
		));

		if (Mage::getStoreConfigFlag('web/seo/use_rewrites')) {
			return str_replace('/' . trim($_SERVER['SCRIPT_NAME'], '/') . '/', '/', $url);
		}
		
		return $url;
	}
	
	/**
	 * Determine whether the request is an AJAX request
	 *
	 * @return bool
	**/
	public function isAjax()
	{
		return (int)Mage::app()->getRequest()->getParam('isAjax') === 1;
	}
	
	/**
	 * Determine whether the request is an AJAX request
	 *
	 * @return bool
	**/
	public function canRun()
	{
		return !in_array(
			Mage::app()->getRequest()->getModuleName(),
			array('firecheckout')
		);
	}
}
