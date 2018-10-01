<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to suporte@tray.net.br so we can send you a copy immediately.
 *
 * @category   Tray
 * @package    Tray_CheckoutApi
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Tray_CheckoutApi_Model_Bankslip extends Tray_CheckoutApi_Model_Standard
{
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION'; //retirar
    const PAYMENT_TYPE_SALE = 'SALE'; //retirar
    
    protected $_allowCurrencyCode = array('BRL');
    
    protected $_code  = 'traycheckoutapi_bankslip';
    
    protected $_formBlockType = 'checkoutapi/form_bankslip';
    
    protected $_blockType = 'checkoutapi/bankslip';
    
    protected $_infoBlockType = 'checkoutapi/info_bankslip';
    
    protected $errorMessageTrayCheckout = '';
    
    protected $errorCodeTrayCheckout = '';
    
    protected $errorTypeErrorTrayCheckout = '';
    
    protected $notification  = 'bankslip';
    
    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('checkoutapi/standard/payment', array('_secure' => true, 'type' => 'bankslip'));
        //return Mage::getUrl('checkoutapi/redirect');
    }
    
    
}