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

class Tray_CheckoutApi_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION'; //retirar
    const PAYMENT_TYPE_SALE = 'SALE'; //retirar
    
    protected $_allowCurrencyCode = array('BRL');
    
    protected $_code  = 'traycheckoutapi';
    
    protected $_formBlockType = 'checkoutapi/form_standard';
    
    protected $_blockType = 'checkoutapi/standard';
    
    protected $_infoBlockType = 'checkoutapi/info_standard';
    
    protected $errorMessageTrayCheckout = '';
    
    protected $errorCodeTrayCheckout = '';
    
    protected $errorTypeErrorTrayCheckout = '';
    
    protected $notification  = 'standard';
    /**
     * Availability options
     */
  

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     */
    public function canEdit()
    {
        return false;
    }
    
    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('checkoutapi/standard/payment', array('_secure' => true, 'type' => 'standard'));
        //return Mage::getUrl('checkoutapi/redirect');
    }
    
     /**
     * Get checkoutapi session namespace
     *
     * @return Tray_CheckoutApi_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('checkoutapi/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
    
    /**
     * Get singleton with checkout standard order transaction information
     *
     * @return Tray_CheckoutApi_Model_Api
     */
    public function getApi() 
    {
        return Mage::getSingleton($this->_blockType);
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return false;
    }

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock($_formBlockType, $name)
            ->setMethod('checkoutapi')
            ->setPayment($this->getPayment())
            ->setTemplate('tray/checkoutapi/form.phtml');
        return $block;
    }
    
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $paymentMethod = "";

        $info = $this->getInfoInstance();

        switch ($data->getMethod()) {
            case 'traycheckoutapi':
                $paymentMethod = $data->getTraycheckoutPaymentCodeCreditcard();
                $info->setCcType($paymentMethod)
                 ->setCcOwner($data->getCcOwner())
                 ->setCcLast4(substr($data->getCcNumber(), -4))
                 ->setCcNumber($data->getCcNumber())
                 ->setCcCid($data->getCcCid())
                 ->setCcExpMonth($data->getCcExpMonth())
                 ->setCcExpYear($data->getCcExpYear())
                 ->setTraycheckoutSplitNumber($data->getTraycheckoutSplitNumber())
                 ->setTraycheckoutSplitValue($data->getTraycheckoutSplitValue())
                 ->setCcNumber($data->getCcNumber());
                break;
            case 'traycheckoutapi_onlinetransfer':
                $paymentMethod = $data->getTraycheckoutPaymentCodeTef();
                $info->setCcType($paymentMethod);
                break;
            case 'traycheckoutapi_bankslip':
                $paymentMethod = $data->getTraycheckoutPaymentCodeBankslip();
                $info->setCcType($paymentMethod);
                break;
        }

        
        return $this;
    }
    
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        
        $info->setCcCidEnc($info->encrypt($info->getCcCid()));
        $info->setCcNumber(null)
            ->setCcCid(null);
        return $this;
    }
    
    public function validateCcNum($ccNumber)
    {
        $cardNumber = strrev($ccNumber);
        $numSum = 0;

        for ($i=0; $i<strlen($cardNumber); $i++) {
            $currentNum = substr($cardNumber, $i, 1);

            /**
             * Double every second digit
             */
            if ($i % 2 == 1) {
                $currentNum *= 2;
            }

            /**
             * Add digits of 2-digit numbers together
             */
            if ($currentNum > 9) {
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }

            $numSum += $currentNum;
        }

        /**
         * If the total has no remainder it's OK
         */
        return ($numSum % 10 == 0);
    }

    public function validate()
    {
        parent::validate();
        
        $errorMsg = "";
        $quote = $this->getCheckout()->getQuote();
        
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        
        // Verificação se consta o nome vazio do consumidor
        Mage::log('Validate - Name Data: '. $billingAddress->getData("firstname")." ".$billingAddress->getData("lastname"), null, 'traycheckout.log');
        if (str_replace(" ","",$billingAddress->getData("firstname")." ".$billingAddress->getData("lastname")) == "") {
            $errorMsg .= "Nome do comprador em branco ou inválido!!\n";
        }
        
        // Validação do CPF do cliente
        $number_taxvat = str_replace(" ","",str_replace(".","",str_replace("-","",$quote->getCustomer()->getData("taxvat"))));
        Mage::log('Validate - CPF Data: '. $number_taxvat, null, 'traycheckout.log');
        if($number_taxvat == null){
            $number_taxvat = str_replace(" ","",str_replace(".","",str_replace("-","",$billingAddress->getData("taxvat"))));
        }
        if (preg_match("/[a-zA-Z]/",$number_taxvat)) {
            $errorMsg .= "CPF em branco ou inválido!!\n";
        }
        
        // Validação do email do cliente
        Mage::log('Validate - Email Data: '. $quote->getCustomer()->getEmail(), null, 'traycheckout.log');
        Mage::log('Validate - Email Billing Data: '. $billingAddress->getEmail(), null, 'traycheckout.log');
        if (!filter_var($quote->getCustomer()->getEmail(), FILTER_VALIDATE_EMAIL)) {
            if (!filter_var($billingAddress->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $errorMsg .= "E-mail em branco ou inválido!!\n";
            }
        }
        
        // Validação do telefone do cliente
        $number_contact = str_replace(" ","",$shippingAddress->getTelephone());
        $number_contact = str_replace("(","",$number_contact);
        $number_contact = str_replace(")","",$number_contact);
        $number_contact = str_replace("-","",$number_contact);
        Mage::log('Validate - Phone Data: '. $number_contact, null, 'traycheckout.log');
        if (preg_match('/[a-zA-Z]/',$number_contact)) {
            $errorMsg .= "Telefone em branco ou inválido!!\n";
        }
        $type_contact = '';
        if (preg_match('/^[0-9]{2}[5-9]{1}[0-9]{7,8}$/',$number_contact)) {
            $type_contact = "M";
        }
        if (preg_match('/^[0-9]{2}[1-6]{1}[0-9]{7}$/',$number_contact)) {
            $type_contact = "H";
        }
        if (preg_match('/^0800[0-9]{6,7}$|^0300[0-9]{6,7}$/',$number_contact)) {
            $type_contact = "W";
        }
        Mage::log('Validate - Type Contact Data: '. $type_contact, null, 'traycheckout.log');
        if ($type_contact == '') {
            $errorMsg .= "Telefone em branco ou inválido!!\n";
        }
        
        // Verificação se consta o endereço do cliente
        Mage::log('Validate - Street 1 Data: '. $shippingAddress->getStreet(1), null, 'traycheckout.log');
        if (str_replace(" ","",$shippingAddress->getStreet(1)) == "") {
            $errorMsg .= "Endereço em branco ou inválido!!\n";
        }
        // Validação do número do endereço.
        Mage::log('Validate - Street 2 Data: '. $shippingAddress->getStreet(2), null, 'traycheckout.log');
        if (preg_match('/[a-zA-Z]/',$shippingAddress->getStreet(2))) {
            $errorMsg .= "Número do endereço em branco ou inválido!!\n";
        }
        // Verificação se consta o bairro do endereço do cliente
        Mage::log('Validate - Street 4 Data: '. $shippingAddress->getStreet(4), null, 'traycheckout.log');
        if (str_replace(" ","",$shippingAddress->getStreet(4)) == "") {
            $errorMsg .= "Bairro em branco ou inválido!!\n";
        }
        // Verificação se consta o cidade do endereço do cliente
        Mage::log('Validate - City Data: '. $shippingAddress->getCity(), null, 'traycheckout.log');
        if (str_replace(" ","",$shippingAddress->getCity()) == "") {
            $errorMsg .= "Cidade em branco ou inválido!!\n";
        }
        // Validação do número do endereço.
        Mage::log('Validate - State Data: '. $shippingAddress->getRegionCode(), null, 'traycheckout.log');
        if (!preg_match('/[A-Z]{2}$/',$shippingAddress->getRegionCode())) {
            if (!preg_match('/[A-Z]{2}$/',$shippingAddress->getRegion())) {
                $errorMsg .= "Estado em branco ou inválido!!\n";
            }
        }
        //var_dump($shippingAddress->getRegionCode());
        
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!$currency_code){
            $session = Mage::getSingleton('adminhtml/session_quote');
            $currency_code = $session->getQuote()->getBaseCurrencyCode();            
        } 
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('checkoutapi')->__('A moeda selecionada ('.$currency_code.') não é compatível com o Yapay Intermediador'));
        }
        
        $ccType = $quote->getPayment()->getData('cc_type');
        //$ccNumber = $quote->getPayment()->getData('cc_number');
        $ccNumber = str_replace(" ","",$quote->getPayment()->getData('cc_number'));
        //var_dump($quote->getPayment()->getData());
        if(!in_array($ccType, array("2","6","7","14","22","23"))){
            if ($this->validateCcNum($ccNumber)) {
                switch ($ccType){
                    // Validação Visa
                    case "3":
                        if (!preg_match('/^4([0-9]{12}|[0-9]{15})$/', $ccNumber)) {
                            $errorMsg .= Mage::helper('payment')->__('Invalid Credit Card Number')."\n";
                        }
                        break;
                    // Validação Master
                    case "4":
                        if (!preg_match('/^5([1-5][0-9]{14})$/', $ccNumber)) {
                            $errorMsg .= Mage::helper('payment')->__('Invalid Credit Card Number')."\n";
                        }
                        break;
                    // Validação American Express
                    case "5":
                        if (!preg_match('/^3[47][0-9]{13}$/', $ccNumber)) {
                            $errorMsg .= Mage::helper('payment')->__('Invalid Credit Card Number')."\n";
                        }
                        break;
                    // Validação Discovery
                    case "15":
                        if (!preg_match('/^6011[0-9]{12}$/', $ccNumber)) {
                            $errorMsg .= Mage::helper('payment')->__('Invalid Credit Card Number')."\n";
                        }
                        break;
                    // Validação JCB
                    case "19":
                        if (!preg_match('/^(3[0-9]{15}|(2131|1800)[0-9]{11})$/', $ccNumber)) {
                            $errorMsg .= Mage::helper('payment')->__('Invalid Credit Card Number')."\n";
                        }
                        break;
                }
            }
            else {
                $errorMsg = Mage::helper('payment')->__('Invalid Credit Card Number')."\n";
            }
        }
        if($ccType == "2"){
            if (!preg_match('/^3(6|8)[0-9]{12}|^3(00|01|02|03|04|05)[0-9]{11}$/', $ccNumber)) {
                $errorMsg .= Mage::helper('payment')->__('Invalid Credit Card Number')."\n";
            }
        }
        
        if($errorMsg != ""){
            $errorMsg .= "\nVerifique as informações para finalizar a compra pelo Yapay Intermediador!";
            Mage::throwException($errorMsg);
        }
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
       return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {
        return $this;
    }

    public function canCapture()
    {
        return true;
    }

    public function decrypt($data)
    {
        if ($data) {
            return Mage::helper('core')->decrypt($data);
        }
        return $data;
    }

    public function getCheckoutFormFields() 
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        
        if (!$orderIncrementId) {
            $quoteidbackend = $this->getCheckout()->getData('checkoutapi_quote_id');
            $order = Mage::getModel('sales/order')->loadByAttribute('quote_id', $quoteidbackend);
            $orderIncrementId = $order->getData('increment_id');
        }
        else {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        }
        
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        
        // Envia email de confirmação ao cliente
        if(!$order->getEmailSent()) {
        	$order->sendNewOrderEmail();
        	$order->setEmailSent(true);
        	$order->save();
        }
            
        $isOrderVirtual = $order->getIsVirtual();
        $ac = $order->getBillingAddress();
        $a = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();
        
        list($items, $totals, $discountAmount, $shippingAmount) = Mage::helper('checkoutapi')->prepareLineItems($order, false, false);

        // $shipping_description = $order->getData('shipping_description');

        if ($order->getData('shipping_description') == NULL) {
            $shipping_description = "Produto Virtual";
        } else {
            $shipping_description = $order->getData('shipping_description');
        }
        
        $sArr = array();
        
        $number_contact = str_replace(" ","",$a->getTelephone());
        $number_contact = str_replace("(","",$number_contact);
        $number_contact = str_replace(")","",$number_contact);
        $number_contact = str_replace("-","",$number_contact);
        
        $type_contact = "H";
        
        if (preg_match('/^[0-9]{2}[5-9]{1}[0-9]{7,8}$/',$number_contact)) {
            $type_contact = "M";
        }
        if (preg_match('/^[0-9]{2}[1-6]{1}[0-9]{7}$/',$number_contact)) {
            $type_contact = "H";
        }
        if (preg_match('/^0800[0-9]{6,7}$|^0300[0-9]{6,7}$/',$number_contact)) {
            $type_contact = "W";
        }
        
	    $sArr['token_account']= $this->getConfigData('token');
	    $sArr['transaction[free]']= "MAGENTO_API_v".(string) Mage::getConfig()->getNode()->modules->Tray_CheckoutApi->version;
        $sArr['transaction[order_number]']= $this->getConfigData('prefixo').$orderIncrementId;

    	$sArr['customer[name]']= $order->getData("customer_firstname") . ' ' . str_replace("(pj)", "", $order->getData("customer_lastname"));
    	$sArr['customer[cpf]']= $order->getData("customer_taxvat");
        $sArr['customer[contacts][][number_contact]']= $number_contact;
        $sArr['customer[contacts][][type_contact]']= $type_contact; 
        $sArr['customer[email]']= $order->getData("customer_email");
        
        // Endereço de Entrega
        $sArr['customer[addresses][0][type_address]']= "D";
        $sArr['customer[addresses][0][postal_code]']= trim(str_replace("-", "", $a->getPostcode()));
        $sArr['customer[addresses][0][street]']= $a->getStreet(1);
        $sArr['customer[addresses][0][number]']= $a->getStreet(2);
        $sArr['customer[addresses][0][completion]']= $a->getStreet(3);
        $sArr['customer[addresses][0][neighborhood]']=$a->getStreet(4);
        $sArr['customer[addresses][0][city]']= $a->getCity();
        $sArr['customer[addresses][0][state]']= ($a->getRegionCode() != "") ? $a->getRegionCode() : $a->getRegion();
        
        // Endereço de Cobrança
        $sArr['customer[addresses][1][type_address]']= "B";
        $sArr['customer[addresses][1][postal_code]']= trim(str_replace("-", "", $ac->getPostcode()));
        $sArr['customer[addresses][1][street]']= $ac->getStreet(1);
        $sArr['customer[addresses][1][number]']= $ac->getStreet(2);
        $sArr['customer[addresses][1][completion]']= $ac->getStreet(3);
        $sArr['customer[addresses][1][neighborhood]']=$ac->getStreet(4);
        $sArr['customer[addresses][1][city]']= $ac->getCity();
        $sArr['customer[addresses][1][state]']= ($ac->getRegionCode() != "") ? $ac->getRegionCode() : $ac->getRegion();
        
        if ($items) {
            $i = 0;
            foreach($items as $item) {
            	if ($item->getAmount() > 0) {
                    $sArr ["transaction_product[$i][code]"] = $item->getId();
                    $sArr ["transaction_product[$i][description]"] =  $item->getName();
                    $sArr ["transaction_product[$i][quantity]"] = $item->getQty();
                    $sArr ["transaction_product[$i][price_unit]"] = sprintf('%.2f',$item->getAmount());
                    $sArr ["transaction_product[$i][sku_code]"] = $item->getId();
                    $i++;
            	}
            }
	    $sArr["transaction[price_discount]"] = is_numeric( $discountAmount ) ? sprintf('%.2f',$discountAmount) : 0;
            $sArr["transaction[price_additional]"] = is_numeric( $order->getData("base_tax_amount") ) ? sprintf('%.2f',$order->getData("base_tax_amount")) : 0;
        }
        
        $shipping = sprintf('%.2f',$shippingAmount) ;

        $sArr['transaction[shipping_type]'] = $shipping_description;
        $sArr['transaction[shipping_price]'] = $shipping;
        
        $sArr['transaction[customer_ip]'] = Mage::helper('core/http')->getRemoteAddr(false);
        
        $sArr['transaction[url_process]'] = Mage::getUrl('checkoutapi/standard/return',  array('_secure' => true));
        $sArr['transaction[url_success]'] = Mage::getUrl('checkoutapi/standard/return', array('_secure' => true));
        $sArr['transaction[url_notification]'] = Mage::getUrl('checkoutapi/standard/success', array('type' => $this->getNotification()));
        
        //Mage::log('type URL: ' .  $this->notification , null, 'traycheckout.log');
        Mage::log('type URL: ' .  Mage::getUrl('checkoutapi/standard/success', array('type' => $this->getNotification())) , null, 'traycheckout.log');
        //Exemplo de Afiliados
        //$sArr['affiliates[0][account_email]'] = 'emailaffiliate1@devtray.com.br';
        //$sArr['affiliates[0][percentage]'] = '20';
        //$sArr['affiliates[1][account_email]'] = 'emailaffiliate2@devtray.com.br';
        //$sArr['affiliates[1][percentage]'] = '15';
                
        $sArr['payment[payment_method_id]'] = $order->getPayment()->getData('cc_type');
        $sArr['payment[split]'] = (($order->getPayment()->getData('traycheckout_split_number') == NULL)|| ($order->getPayment()->getData('traycheckout_split_number') == '0') ? '1' : $order->getPayment()->getData('traycheckout_split_number'));
        $sArr['payment[card_name]'] = $order->getPayment()->getData('cc_owner');
        $sArr['payment[card_number]'] = str_replace(" ","",$this->decrypt($order->getPayment()->getData('cc_number_enc')));
        $sArr['payment[card_expdate_month]'] = $order->getPayment()->getData('cc_exp_month');
        $sArr['payment[card_expdate_year]'] = $order->getPayment()->getData('cc_exp_year');
        $sArr['payment[card_cvv]'] = $this->decrypt(Mage::getModel('sales/quote_payment')->load($order->getData("quote_id"),"quote_id")->getData("cc_cid_enc"));
        
        $sReq = '';
        $rArr = array();
        foreach ($sArr as $k=>$v) {
            $value =  str_replace("&","and",$v);
            $rArr[$k] =  $value;
            $sReq .= '&'.$k.'='.$value;
        }
        return $rArr;
    }

    public function getTrayCheckoutUrl()
    {
         if ($this->getConfigData('sandbox') == '1')
         {
         	return 'https://api.sandbox.traycheckout.com.br';
         } else {
         	return 'https://api.traycheckout.com.br';
         }
    }
    
    public function getErrorMessageTrayCheckout() {
        return $this->errorMessageTrayCheckout;
    }
    
    public function getErrorCodeTrayCheckout() {
        return $this->errorCodeTrayCheckout;
    }
    public function getTypeErrorTrayCheckout() {
        return $this->errorTypeErrorTrayCheckout;
    }
    public function getNotification() {
        return $this->notification;
    }
    
    public function hasErrorTrayCheckout($res){
        $xml = simplexml_load_string($res);
        $codeTc = "";
        $messageTc = "";
        
        if($xml->message_response->message == "error"){
            if(!empty($xml->error_response->general_errors)){
                $this->errorTypeErrorTrayCheckout = "G";
                $qtdError = sizeof($xml->error_response->general_errors->general_error);
                for($i = 0; $i < $qtdError; $i++){
                    $codeTc .= $xml->error_response->general_errors->general_error[$i]->code . " | ";
                    $messageTc .= $xml->error_response->general_errors->general_error[$i]->message . " | ";
                }

            }
            if(!empty($xml->error_response->validation_errors)){
                $this->errorTypeErrorTrayCheckout = "V";
                $qtdError = sizeof($xml->error_response->validation_errors->validation_error);
                for($i = 0; $i < $qtdError; $i++){
                    $codeTc .= $xml->error_response->validation_errors->validation_error[$i]->field . " | ";
                    $messageTc .= $xml->error_response->validation_errors->validation_error[$i]->message_complete . " | ";
                }
            }
            $codeTc = substr($codeTc, 0, - 3);
            $messageTc = substr($messageTc, 0, - 3);
            $this->errorCodeTrayCheckout = $codeTc;
            $this->errorMessageTrayCheckout = $messageTc;
            return true;
        }else{
            return  false;
        }
    }
    
    public function getTrayCheckoutRequest($url = "", $params = "")
    {
        Mage::log('URL de Request: '.$this->getTrayCheckoutUrl().$url, null, 'traycheckout.log');
        $ch = curl_init ( $this->getTrayCheckoutUrl().$url );
        
        if(is_array($params)){
            $params = http_build_query($params);
        }
        Mage::log('Data: '.  $params, null, 'traycheckout.log');
        $patterns = array();
        $patterns[0] = '/card_name%5D=[\w\W]*&payment%5Bcard_number/';
        $patterns[1] = '/card_number%5D=\d+\D/';
        $patterns[2] = '/card_expdate_month%5D=\d+\D/';
        $patterns[3] = '/card_expdate_year%5D=\d+\D/';
        $patterns[4] = '/card_cvv%5D=\d+/';
        $replacements = array();
        $replacements[0] = 'card_name%5D=&payment%5Bcard_number';
        $replacements[1] = 'card_number%5D=';
        $replacements[2] = 'card_expdate_month%5D=';
        $replacements[3] = 'card_expdate_year%5D=';
        $replacements[4] = 'card_cvv%5D=';

        //$arrayp = array('%5B0%5D','%5B1%5D','%5B2%5D','%5B3%5D','%5B4%5D','%5B5%5D','%5B6%5D','%5B7%5D','%5B8%5D','%5B9%5D');
        //$replace = ;
        
        $params = preg_replace('/%5B\d{1,3}%5D/', '%5B%5D', $params);
        //$params = str_replace($arrayp, $replace, $params);
        
        Mage::log('Data: '.  preg_replace($patterns, $replacements,$params), null, 'traycheckout.log');
        
        curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt ( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2 );

        
        if (!($res = curl_exec($ch))) {
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
        
        if ($httpCode != "200") {
                Mage::log('Error: Erro de requisicao em:'. $urlPost, null, 'traycheckout.log');
                http::httpError("Erro de requisicao em: $urlPost");
                Mage::app()->getResponse()->setRedirect('checkoutapi/standard/error', array('_secure' => true , 'descricao' => urlencode(utf8_encode("Erro ao conectar em: $url")),'codigo' => urlencode($httpCode)))->sendResponse();
        }
        
        if(curl_errno($ch)){
                Mage::log('Error: Erro de conexão: ' . curl_error($ch), null, 'traycheckout.log');
                http::httpError("Erro de conexão: " . curl_error($ch));
                Mage::app()->getResponse()->setRedirect('checkoutapi/standard/error', array('_secure' => true , 'descricao' => urlencode(utf8_encode("Erro de conexão: " . curl_error($ch))),'codigo' => urlencode($httpCode)))->sendResponse();
        }
        curl_close($ch);
        
        if($this->hasErrorTrayCheckout($res)){
            Mage::app()->getResponse()->setRedirect(Mage::getModel('core/url')->getUrl('checkoutapi/standard/error', array('_secure' => true , 'descricao' => urlencode($this->getErrorMessageTrayCheckout()),'codigo' => urlencode($this->getErrorCodeTrayCheckout()),'type' => $this->getTypeErrorTrayCheckout())))->sendResponse();
            exit();
        }
        
        Mage::log('HttpCode: '. $httpCode, null, 'traycheckout.log');
        Mage::log('Response: '. $res, null, 'traycheckout.log');
        return $res;
    }
    
    function updateTransactionTrayCheckout($transactionTc){
        
        $order = Mage::getModel('sales/order')->loadByIncrementId(str_replace($this->getConfigData('prefixo'),'',$transactionTc->order_number));
        $quote = Mage::getModel('sales/quote')->load($order->getData("quote_id"));
        
        $quote->getPayment()->setData("traycheckout_transaction_id", $transactionTc->transaction_id);
        $quote->getPayment()->setData("traycheckout_token_transaction", $transactionTc->token_transaction);
        $quote->getPayment()->setData("traycheckout_url_payment", $transactionTc->payment->url_payment);
        $quote->getPayment()->setData("traycheckout_typeful_line", $transactionTc->payment->linha_digitavel);
        
        $quote->getPayment()->save();
        
        $order->getPayment()->setData("traycheckout_transaction_id", $transactionTc->transaction_id);
        $order->getPayment()->setData("traycheckout_token_transaction", $transactionTc->token_transaction);
        $order->getPayment()->setData("traycheckout_url_payment", $transactionTc->payment->url_payment);
        $order->getPayment()->setData("traycheckout_typeful_line", $transactionTc->payment->linha_digitavel);
        $order->getPayment()->save();
        
        $cod_status = $transactionTc->status_id;
        $comment = "";
        if (isset($transactionTc->status_id)) {
            $comment .= " " . $transactionTc->status_id;
        }

        if (isset($transactionTc->status_name)) {
            $comment .= " - " . $transactionTc->status_name;
        }
        
        switch ($cod_status){
            case '4': 
            case '5':
            case '88':
                    $order->addStatusToHistory(
                        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage::helper('checkoutapi')->__('Yapay Intermediador retornou o status: %s', $comment)

                    );
                break;
            case '6':
                    
                    $items = $order->getAllItems();
                    
                    $thereIsVirtual = false;
                    
                    foreach ($items as $itemId => $item) {
                        if ($item["is_virtual"] == "1" || $item["is_downloadable"] == "1") {
                            $thereIsVirtual = true;
                        }
                    }
                    
                    // what to do - from admin
                    $toInvoice = $this->getApi()->getConfigData('acaopadraovirtual') == "1" ? true : false;
                    
                    if ($thereIsVirtual && !$toInvoice) {
                    
                        $frase = 'Yapay Intermediador - Aprovado. Pagamento (fatura) confirmado automaticamente.';

                        $order->addStatusToHistory(
                                Mage_Sales_Model_Order::STATE_PROCESSING, 
                                Mage::helper('checkoutapi')->__($frase), true
                        );
                    
                        $order->sendOrderUpdateEmail(true, $frase);
                    
                    } else {
                    
                        if (!$order->canInvoice()) {
                            $isHolded = ( $order->getStatus() == Mage_Sales_Model_Order::STATE_HOLDED );

                            $status = ($isHolded) ? Mage_Sales_Model_Order::STATE_PROCESSING :  $order->getStatus();
                            $frase  = ($isHolded) ? 'Yapay Intermediador - Aprovado. Confirmado automaticamente o pagamento do pedido.' : 'Erro ao criar pagamento (fatura).';
                    
                            //when order cannot create invoice, need to have some logic to take care
                            $order->addStatusToHistory(
                                $status, //continue setting current order status
                                Mage::helper('checkoutapi')->__( $frase )
                            );
                    
                        } else {
                    
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
                        }
                    }
                break;
            case '24':
                    $order->addStatusToHistory(
                        Mage_Sales_Model_Order::STATE_HOLDED, Mage::helper('checkoutapi')->__('Yapay Intermediador retornou o status: %s', $comment)
                    );
                break;
            case '7':
            case '89':                        	
                    $frase = 'Yapay Intermediador - Cancelado. Pedido cancelado automaticamente (transação foi cancelada ou pagamento foi negado).';

                    $order->addStatusToHistory(
                        Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('checkoutapi')->__($frase), true
                    );

                    $order->sendOrderUpdateEmail(true, $frase);

                    $order->cancel();
                break;
            case '87':
                    $order->addStatusToHistory(
                        Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Mage::helper('checkoutapi')->__('Yapay Intermediador retornou o status: %s', $comment)
                    );
                break;
        }
        $order->save();
    }
    
}