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

class Tray_CheckoutApi_Model_Auth extends Varien_Object
{
    public $urlAccessToken = "v1/authorizations/access_token";
    public $urlRefreshToken = "v1/authorizations/refresh";
    
    public $access_token = "";
    public $refresh_token = "";
    
    public function doAuthorization($consumer_key = "", $consumer_secret = "", $code = "",$environment = "1")
    {
        
        $params["consumer_key"] = $consumer_key;
        $params["consumer_secret"] = $consumer_secret;
        $params["code"] = $code;
        
        $tcRequest = Mage::getModel('checkoutapi/request');
        
        Mage::log($params, null, 'traycheckout.log');

        $tcResponse = $tcRequest->requestData($this->urlAccessToken,$params,$environment);
        
        Mage::log($tcResponse, null, 'traycheckout.log');
        
        if($tcResponse->message_response->message == "success"){
            $this->access_token = $tcResponse->data_response->authorization->access_token;
            $this->refresh_token = $tcResponse->data_response->authorization->refresh_token;
        }
    }
       
    
}