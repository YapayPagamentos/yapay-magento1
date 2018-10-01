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

class Tray_CheckoutApi_StandardController extends Mage_Core_Controller_Front_Action 
{

    /**
     * Order instance
     */
    protected $_order;

    
    public function paymentAction()
    {             
       $this->loadLayout();
       $this->renderLayout();       
    }
    
    public function returnAction()
    {
       $this->loadLayout();
       $this->renderLayout();
    }
    
    /*public function returnconfigmoduleAction()
    {
        
        $code = $this->getRequest()->getParam('code', false);
        $customerKey = $this->getRequest()->getParam('CK', false);
        $customerSecret = $this->getRequest()->getParam('CS', false);
        $environment = $this->getRequest()->getParam('environment', false);
        
        $tcAuth = Mage::getModel('checkoutapi/auth');
        $tcAuth->doAuthorization( $customerKey, $customerSecret, $code, $environment);
        
        $tcRequest = Mage::getModel('checkoutapi/request');
        
        //Mage::log($tcAuth, null, 'traycheckout.log');

        $params["access_token"] = $tcAuth->access_token;
        $params["url"] = Mage::getBaseUrl();
        //var_dump($code,$customerKey,$customerSecret,$environment);
        //var_dump($params);
        $tcResponse = $tcRequest->requestData("v1/people/update",$params,$environment);
        
        if($tcResponse->message_response->message == "success"){
            
            $htmlId = "payment_traycheckoutapi";
            $htmlId .= ($this->getRequest()->getParam("type") != "standard") ? "_".$this->getRequest()->getParam("type") : "";
            
            $script = "<script>";
            $script .= "parent.document.getElementById('".$htmlId."_code').value = '$code';";
            $script .= "parent.document.getElementById('".$htmlId."_customerKey').value = '$customerKey';";
            $script .= "parent.document.getElementById('".$htmlId."_customerSecret').value = '$customerSecret';";
            $script .= "parent.document.getElementById('".$htmlId."_token').value = '".$tcResponse->data_response->token_account."';";
            $script .= "parent.configForm.submit();";
            $script .= "";
            $script .= "";
            $script .= "</script>";
            
            echo $script;
        }
    }*/
    
    public function paymentbackendAction() 
    {
        $this->loadLayout();
        $this->renderLayout();

        $hash = explode("/order/", $this->getRequest()->getOriginalRequest()->getRequestUri());
        $hashdecode = explode(":", Mage::getModel('core/encryption')->decrypt($hash[1]));

        $order = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('increment_id', $hashdecode[0])
                ->addFieldToFilter('quote_id', $hashdecode[1])
                ->getFirstItem();

        if ($order) {
            $session = Mage::getSingleton('checkout/session');
            $session->setLastQuoteId($order->getData('quote_id'));
            $session->setLastOrderId($order->getData('entity_id'));
            $session->setLastSuccessQuoteId($order->getData('quote_id'));
            $session->setLastRealOrderId($order->getData('increment_id'));
            $session->setCheckoutApiQuoteId($order->getData('quote_id'));
            $this->_redirect('checkoutapi/standard/payment/type/standard');
        } else {
            Mage::getSingleton('checkout/session')->addError('URL informada é inválida!');
            $this->_redirect('checkout/cart');
        }
    }

    public function errorAction()
    {
       $this->loadLayout();
       $this->renderLayout();
    }
    
    /**
     *  Get order
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder() {
        
        if ($this->_order == null) {
            
        }
        
        return $this->_order;
    }

    protected function _expireAjax() {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with checkout standard order transaction information
     *
     * @return Tray_CheckoutApi_Model_Api
     */
    public function getApi() 
    {
        return Mage::getSingleton('checkoutapi/'.$this->getRequest()->getParam("type"));
    }

    /**
     * When a customer chooses Tray on Checkout/Payment page
     *
     */
    public function redirectAction() 
    {
        
        $type = $this->getRequest()->getParam('type', false);
        
        $session = Mage::getSingleton('checkout/session');

        $session->setCheckoutApiQuoteId($session->getQuoteId());
        
        $this->getResponse()->setHeader("Content-Type", "text/html; charset=ISO-8859-1", true);

        $this->getResponse()->setBody($this->getLayout()->createBlock('checkoutapi/redirect')->toHtml());

        $session->unsQuoteId();
    }

    /**
     * When a customer cancel payment from traycheckout .
     */
    public function cancelAction() 
    {
        
        $session = Mage::getSingleton('checkout/session');

        $session->setQuoteId($session->getCheckoutApiQuoteId(true));

        // cancel order
        if ($session->getLastRealOrderId()) {

            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

            if ($order->getId()) {
                $order->cancel()->save();
            }
        }

        $this->_redirect('checkout/cart');
    }
    
    private function getUrlPostCheckoutApi($sandbox)
    {
         if ($sandbox == '1')
         {
        	return "https://api.sandbox.traycheckout.com.br/v2/transactions/get_by_token";
         } else {
		return "https://api.traycheckout.com.br/v2/transactions/get_by_token";
         }
    }
    
    /**
     * when checkout returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the return post.
     */
    public function successAction() 
    {
        $_type = $this->getRequest()->getParam('type', false);
        $token = $this->getApi()->getConfigData('token');

	    $urlPost = $this->getUrlPostCheckoutApi($this->getApi()->getConfigData('sandbox'));

        $dados_post = $this->getRequest()->getPost();
         
        $order_number_conf = utf8_encode(str_replace($this->getApi()->getConfigData('prefixo'),'',$dados_post['transaction']['order_number']));
        $transaction_token= $dados_post['token_transaction'];
        
        $dataRequest['token_transaction'] = $transaction_token;
        $dataRequest['token_account'] = trim($token);
        $dataRequest['type_response'] = 'J';
        
        //$transaction_token= $dados_post['transaction']['transaction_token']; 

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

        
        if (!($resposta = curl_exec($ch))) {
            Mage::log('Error: Erro na execucao! ', null, 'traycheckout.log');
            if(curl_errno($ch)){
                Mage::log('Error '.curl_errno($ch).': '. curl_error($ch), null, 'traycheckout.log');
            }else{
                Mage::log('Error : '. curl_error($ch), null, 'traycheckout.log');
            }
            
            Mage::app()->getResponse()->setRedirect('checkoutapi/standard/error', array('_secure' => true , 'descricao' => urlencode(utf8_encode("Erro de execução!")),'codigo' => urlencode("999")))->sendResponse();
            echo "Erro na execucao!";
            curl_close ( $ch );
            exit();    
        }
        
        
        $httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        
        curl_close($ch); 
        
        $arrResponse = json_decode($resposta,TRUE);
        
        $message_response = $arrResponse['message_response'];
        $error_response = $arrResponse['error_response'];
        if($message_response['message'] == "error"){
            if(!empty($error_response['general_errors'])){
                foreach ($error_response['general_errors'] as $general_error){
                    $codigo_erro .= $general_error['code'] . " | ";
                    $descricao_erro .= $general_error['message'] . " | ";
                }

            }
            if(!empty($error_response['validation_errors'])){
                var_dump($error_response['validation_errors']);
                foreach ($error_response['validation_errors'] as $validation_error){
                    $codigo_erro .= $validation_error['field'] . " | ";
                    $descricao_erro .= $validation_error['message_complete'] . " | ";
                }
            }
            $codigo_erro = substr($codigo_erro, 0, - 3);
            $descricao_erro = substr($descricao_erro, 0, - 3);
            
            if ($codigo_erro == ''){
                $codigo_erro = '0000000';
            }
            if ($descricao_erro == ''){
                $descricao_erro = 'Erro Desconhecido';
            }
            $this->_redirect('checkoutapi/standard/error', array('_secure' => true , 'descricao' => urlencode(utf8_encode($descricao_erro)),'codigo' => urlencode($codigo_erro)));
        }else{
        	
            $transaction = $arrResponse['data_response']['transaction'];
            $order_number = str_replace($this->getApi()->getConfigData('prefixo'),'',$transaction['order_number']);
            $order = Mage::getModel('sales/order');
            $prefixo123 = $this->getApi()->getConfigData('prefixo');

            $order->loadByIncrementId($order_number);
            
            echo "Prefixo: $prefixo123 | Pedido: $order_number - ID: ".$transaction['transaction_id'];
            
            if ($order->getId()) {

                if (floatval($transaction['payment']['price_original']) != floatval($order->getGrandTotal())) {
                    
                    $frase = 'Total pago à Yapay Intermediador é diferente do valor original.';

                    $order->addStatusToHistory(
                            $order->getStatus(), //continue setting current order status
                            Mage::helper('checkoutapi')->__($frase), true
                    );
                    echo $frase;
                    $order->sendOrderUpdateEmail(true, $frase);
                } else {
                    $cod_status = $transaction['status_id'];
                    
                    $comment = $cod_status . ' - ' . $transaction['status_name'];

                    switch ($cod_status){
                        case 4: 
                        case 5:
                        case 88:
                                $order->addStatusToHistory(
                                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage::helper('checkoutapi')->__('Yapay Intermediador enviou automaticamente o status: %s', $comment)
                                    
                                );
                            break;
                        case 6:
                                $items = $order->getAllItems();

                                $thereIsVirtual = false;

                                foreach ($items as $itemId => $item) {
                                    if ($item["is_virtual"] == "1" || $item["is_downloadable"] == "1") {
                                        $thereIsVirtual = true;
                                    }
                                }

                                // what to do - from admin
                                $toInvoice = $this->getApi()->getConfigData('acaopadraovirtual') == "1" ? true : false;

                                if ($thereIsVirtual && $toInvoice) {

                                    /*if ($order->canInvoice()) {
                                    	$isHolded = ( $order->getStatus() == Mage_Sales_Model_Order::STATE_HOLDED );

                                        $status = ($isHolded) ? Mage_Sales_Model_Order::STATE_PROCESSING :  $order->getStatus();
                                        $frase  = ($isHolded) ? 'Tray - Aprovado. Confirmado automaticamente o pagamento do pedido.' : 'Erro ao criar pagamento (fatura).';
										
                                        //when order cannot create invoice, need to have some logic to take care
                                        $order->addStatusToHistory(
                                            $status, //continue setting current order status
                                            Mage::helper('checkoutapi')->__( $frase )
                                        );

                                    } else {*/

                                    //need to save transaction id
                                    $order->getPayment()->setTransactionId($dados_post['transaction']['transaction_id']);

                                    //need to convert from order into invoice
                                    $invoice = $order->prepareInvoice();

                                    if ($this->getApi()->canCapture()) {
                                        $invoice->register()->capture();
                                    }

                                    Mage::getModel('core/resource_transaction')
                                            ->addObject($invoice)
                                            ->addObject($invoice->getOrder())
                                            ->save();

                                    $frase = 'Pagamento (fatura) ' . $invoice->getIncrementId() . ' foi criado. Yapay Intermediador - Aprovado. Confirmado automaticamente o pagamento do pedido.';

                                    if ($thereIsVirtual) {

                                        $order->addStatusToHistory(
                                            $order->getStatus(), Mage::helper('checkoutapi')->__($frase), true
                                        );

                                    } else {

                                        $order->addStatusToHistory(
                                            Mage_Sales_Model_Order::STATE_PROCESSING, //update order status to processing after creating an invoice
                                            Mage::helper('checkoutapi')->__($frase), true
                                        );
                                    }

                                    $invoice->sendEmail(true, $frase);
                                    
                                } else {
									
                                    $frase = 'Yapay Intermediador - Aprovado. Pagamento (fatura) confirmado automaticamente.';

                                    
                                    $order->addStatusToHistory(
                                            Mage_Sales_Model_Order::STATE_PROCESSING, 
                                            Mage::helper('checkoutapi')->__($frase), false
                                    );

                                    //$order->sendOrderUpdateEmail(true, $frase);
                                    
                                }
                            //}
                            break;
                        case 24:
                                $order->addStatusToHistory(
                                    Mage_Sales_Model_Order::STATE_HOLDED, Mage::helper('checkoutapi')->__('Yapay Intermediador enviou automaticamente o status: %s', $comment)
                                );
                            break;
                        case 7:
                        case 89:                        	
                                $frase = 'Yapay Intermediador - Cancelado. Pedido cancelado automaticamente (transação foi cancelada, pagamento foi negado, pagamento foi estornado ou ocorreu um chargeback).';

                                $order->addStatusToHistory(
                                    Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('checkoutapi')->__($frase), true
                                );

                                $order->sendOrderUpdateEmail(true, $frase);

                                $order->cancel();
                            break;
                        case 87:
                                $order->addStatusToHistory(
                                    Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Mage::helper('checkoutapi')->__('Yapay Intermediador enviou automaticamente o status: %s', $comment)
                                );
                            break;
                    }
                }
                $order->save();
            }
        }
    }
    
    public function getsplitAction()
    {
        $tcStandard = Mage::getModel('checkoutapi/standard');
        
        Mage::log('Split Request: '.  $tcStandard->getConfigData('token'), null, 'traycheckout.log');
        
        $params = array(
            "token_account" => $tcStandard->getConfigData('token'),
            "price" => $this->getRequest()->getParam('price', false)
        );
        
        $method =  $this->getRequest()->getParam('method', false);
        
        $tcResponse = simplexml_load_string($tcStandard->getTrayCheckoutRequest("/v1/transactions/simulate_splitting",$params));
        
        foreach ($tcResponse->data_response->payment_methods->payment_method as $payment_method){
            if(intval($payment_method->payment_method_id) == intval($method)){
                $splittings = $payment_method->splittings->splitting;
                //echo "<p>Método: $payment_method->payment_method_id - $payment_method->payment_method_name </p>";
            }
        }
        
        
        for($auxS = 0; $auxS < (int)$tcStandard->getConfigData('tcQtdSplit') && $auxS < sizeof($splittings); $auxS++){
            $splitting = $splittings[$auxS];
            $splitSimulate[(int)$splitting->split] = (string)$splitting->split . " x de R$" . number_format((float)$splitting->value_split, 2, ',','') . (((float)$splitting->split_rate == 0) ? " sem " : " com ") . "juros";
        }
        
        echo json_encode($splitSimulate);
        
    }

}