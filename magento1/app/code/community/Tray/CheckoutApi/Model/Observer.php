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

class Tray_CheckoutApi_Model_Observer extends Varien_Object
{
    public function sendEmailFrontend() 
    {
        $session = Mage::getSingleton('checkout/session');
        $lastSuccessOrderId = $session->getData('last_real_order_id');
       
        $order = Mage::getModel('sales/order')->loadByAttribute('increment_id',$lastSuccessOrderId);

//        $sendNewOrderEmail = Mage::getStoreConfig('sales_email/order/enabled');
//        if ($sendNewOrderEmail && !$order->getData('email_sent')) {
//            $order->sendNewOrderEmail();
//            $order->setEmailSent(true);
//            $order->save();
//        }
    }
    
    /*public function cancelOrderTrayCheckout(Varien_Event_Observer $observer) {
        $order = $observer->getOrder();
        
        $paymentMethod = str_replace(array("payment_","_configButtom"),"",$order->getPayment()->getData('method'));
        if ($paymentMethodName == "traycheckoutapi" || $paymentMethodName == "traycheckoutapi_bankslip" || $paymentMethodName == "traycheckoutapi_onlinetransfer"){ 
            $paymentMethod = ($paymentMethod == "traycheckoutapi_bankslip") ? "bankslip" : (($paymentMethod == "traycheckoutapi_onlinetransfer") ? "onlinetransfer" : "standard");
            $configTc = Mage::getSingleton('checkoutapi/'.$paymentMethod);
            
            $tcAuth = Mage::getModel('checkoutapi/auth');
            $tcAuth->doAuthorization( $configTc->getConfigData("customerKey"), $configTc->getConfigData("customerSecret"), $configTc->getConfigData("code"), $configTc->getConfigData("sandbox"));
            
            $tcRequest = Mage::getModel('checkoutapi/request');
            
            $params["access_token"] = $tcAuth->access_token;
            $params["transaction_id"] = $order->getPayment()->getData("traycheckout_transaction_id");
            
            $tcResponse = $tcRequest->requestData("v1/transactions/cancel",$params,$configTc->getConfigData("sandbox"));
            
            if($tcResponse->message_response->message == "success"){
                $order->addStatusToHistory($order->getStatus(), "Pedido Cancelado no TrayCheckout!", false);
            }else{
                if ($params["access_token"] != ""){
                    $order->addStatusToHistory($order->getStatus(), "Não foi possível cancelar o pedido: ".$tcResponse->error_response->errors->error[0]->code." - ".$tcResponse->error_response->errors->error[0]->message , false);
                    Mage::throwException("Erro ao Cancelar o Pedido no TrayCheckout: " .$tcResponse->error_response->errors->error[0]->code." - ".$tcResponse->error_response->errors->error[0]->message);    
                }
            }
        }  
    }

    public function shipmentTrayCheckout(Varien_Event_Observer $observer){
        
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        $paymentMethodName = $order->getPayment()->getData('method');

        Mage::log($order->getPayment()->getData('method'), null, 'traycheckout.log');
        
        if ($paymentMethodName == "traycheckoutapi" || $paymentMethodName == "traycheckoutapi_bankslip" || $paymentMethodName == "traycheckoutapi_onlinetransfer"){
            $paymentMethod = ($paymentMethodName == "traycheckoutapi_bankslip") ? "bankslip" : (($paymentMethodName == "traycheckoutapi_onlinetransfer") ? "onlinetransfer" : "standard");
            $configTc = Mage::getSingleton('checkoutapi/'.$paymentMethod);
            

            $tracking = array();

            foreach ($shipment->getAllTracks() as $track) {
                $tracking['title'] = $track->getTitle();
                $tracking['number'] = $track->getNumber();
            }

            if (count($tracking) > 0) {
                //$tcAuth->doAuthorization( $configTc->getConfigData("customerKey"), $configTc->getConfigData("customerSecret"), $configTc->getConfigData("code"), $configTc->getConfigData("sandbox"));

                $tcRequest = Mage::getModel('checkoutapi/request');
            
                $params["token_account"] = $configTc->getConfigData('token');
                //$params["access_token"] = $tcAuth->access_token;
                $params["order_number"] = $configTc->getConfigData('prefixo') . $order->getIncrementId();
                $params["code"] = $tracking['number'];
                $params["date_posting"] = date("d/m/Y",strtotime($shipment->getCreatedAt()));
                
                $tcResponse = $tcRequest->requestData("v1/transactions/trace",$params,$configTc->getConfigData("sandbox"));
                
                if($tcResponse->message_response->message == "success"){
                    $order->addStatusToHistory($order->getStatus(), "Enviado Código de Rastreio " . $tracking['number'] . "ao TrayCheckout!", false);
                }else{
                    if ($params["access_token"] != ""){
                        //$order->addStatusToHistory($order->getStatus(), "Não foi possível cancelar o pedido: ".$tcResponse->error_response->errors->error[0]->code." - ".$tcResponse->error_response->errors->error[0]->message , false);
                        Mage::throwException("Erro ao Enviar o Código de Rastreio ao TrayCheckout: " .$tcResponse->error_response->errors->error[0]->code." - ".$tcResponse->error_response->errors->error[0]->message);    
                    }
                }
            } else {
                Mage::log('Sem Código de Rastreio!', null, 'traycheckout.log');
            }

            
            //$params["transaction_id"] = $order->getPayment()->getData("traycheckout_token_transaction");
        }
        
    }*/

}