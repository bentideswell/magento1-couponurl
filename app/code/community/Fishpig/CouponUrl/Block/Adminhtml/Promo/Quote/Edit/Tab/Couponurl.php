<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SeoTagUrls
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_CouponUrl_Block_Adminhtml_Promo_Quote_Edit_Tab_Couponurl extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	/**
	 * Prepare content for tab
	 *
	 * @return string
	*/
	public function getTabLabel()
	{
		return Mage::helper('salesrule')->__('Coupon URL');
	}
	
	/**
	 * Prepare title for tab
	 *
	 * @return string
	*/
	public function getTabTitle()
	{
		return $this->getTabLabel();
	}
	
	/**
	 * Returns status flag about this tab can be showen or not
	 *
	 * @return true
	*/
	public function canShowTab()
	{
		return Mage::helper('couponurl')->isEnabled()
			&& !is_null(Mage::registry('current_promo_quote_rule'));
	}

	/**
	 * Returns status flag about this tab hidden or not
	 *
	 * @return true
	*/
	public function isHidden()
	{
		return false;
	}

	protected function _prepareForm()
	{
		/*
		$form = new Varien_Data_Form();

		$form->setHtmlIdPrefix('couponurl_');

		$fieldset = $form->addFieldset('couponurl', array(
			'legend'=> $this->__('Settings')
		));
		
		$fieldset->addField('redirect_to', 'text', array(
			'name' 		=> 'redirect_to',
			'label' 	=> $this->__('Redirect To'),
			'title' 	=> $this->__('Redirect To'),
			'note' => $this->__("Default: '%s'", Mage::helper('couponurl')->getSuccessRedirect())
		));
		
		$fieldset->addField('message', 'text', array(
			'name' 		=> 'message',
			'label' 	=> $this->__('Message'),
			'title' 	=> $this->__('Message'),
			'note' => $this->__("Default: '%s'", Mage::helper('couponurl')->getSuccessMessage())
		));
		
		$this->setForm($form);
		*/
		
		return parent::_prepareForm();
	}
	
	/**
	 * Add the custom html
	 *
	 * @param string $html
	 * @return string
	 */
	protected function _afterToHtml($html)
	{
		$coupon = Mage::registry('current_promo_quote_rule');
		
		if (!$coupon) {
			return $html;
		}

		$couponUrl = Mage::helper('couponurl')->getUrlByCoupon($coupon);

		return sprintf('<div class="fieldset">
			<div class="hor-scroll">
				<table cellspacing="0" class="form-list">
					<tbody>
						<tr>
							<td>Activate this coupon via <a href="%s" target="_blank">%s</a></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>', $couponUrl, $couponUrl) . $html;		
	}
}
