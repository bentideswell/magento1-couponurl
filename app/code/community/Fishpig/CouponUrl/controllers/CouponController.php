<?php
/**
 * @category    Fishpig
 * @package     Fishpig_AttributeSplash
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_CouponUrl_CouponController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Apply the coupon
	 *
	 * @return void
	 */
	public function applyAction()
	{
		if (($coupon = Mage::helper('couponurl')->applyCoupon()) !== false) {

			Mage::getSingleton('core/session')->addSuccess(
				$this->__(Mage::helper('couponurl')->getSuccessMessage(), $coupon->getCode())
			);

			return $this->_redirectUrl(Mage::helper('couponurl')->getSuccessRedirect());
		}
		else {
			return $this->_forward('noRoute');
		}
	}
}
