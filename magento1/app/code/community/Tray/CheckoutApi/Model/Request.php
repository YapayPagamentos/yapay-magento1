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

class Tray_CheckoutApi_Model_Request extends Varien_Object
{
    private function getUrlEnvironment($environment){
        return ($environment == '1') ? "https://api.sandbox.traycheckout.com.br/" : "https://api.traycheckout.com.br/";
    }
    
    public function requestData($pathPost, $dataRequest, $environment = "1")
    {
        
        $urlPost = self::getUrlEnvironment($environment).$pathPost;
        
        Mage::log('URL de Request: '.$urlPost, null, 'traycheckout.log');
        $ch = curl_init ( $urlPost );
        
        if(is_array($dataRequest)){
            Mage::log('Data: '. http_build_query($dataRequest), null, 'traycheckout.log');
        }else{
            Mage::log('Data: '.  $dataRequest, null, 'traycheckout.log');
        }
        
        curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $dataRequest);
        curl_setopt ( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2 );

        
        if (!($responseData = curl_exec($ch))) {
            Mage::log('Error: Erro na execucao! ', null, 'traycheckout.log');
            if(curl_errno($ch)){
                Mage::log('Error '.curl_errno($ch).': '. curl_error($ch), null, 'traycheckout.log');
            }else{
                Mage::log('Error : '. curl_error($ch), null, 'traycheckout.log');
            }
            curl_close ( $ch );
            exit();    
        }
        
        $httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        
        curl_close($ch); 
        
        $responseData = simplexml_load_string($responseData);
                
        return $responseData;
    }
    
}