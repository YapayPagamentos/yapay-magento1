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

class Tray_CheckoutApi_Block_Payment extends Mage_Core_Block_Template
{
    protected $order_number;
    protected $order_number_tc;
    protected $transaction_id;
    protected $url_payment;
    protected $typeful_line;
    protected $status_id;
    protected $status_name;
    protected $payment_method_id;
    protected $payment_method_name;
    protected $token_account;
    
    public function getOrderNumber() {
        return $this->order_number;
    }
    public function getOrderNumberTc() {
        return $this->order_number_tc;
    }
    public function getTransactionId() {
        return $this->transaction_id;
    }
    public function getUrlPayment() {
        return $this->url_payment;
    }
    public function getTypefulLine() {
        return $this->typeful_line;
    }
    public function getStatusId() {
        return $this->status_id;
    }
    public function getStatusName() {
        return $this->status_name;
    }
    public function getPaymentMethodId() {
        return $this->payment_method_id;
    }
    public function getPaymentMethodName() {
        return $this->payment_method_name;
    }
    public function getTokenAccount() {
        return $this->token_account;
    }
    
    protected function getPayment()
    {
        $standard = Mage::getModel('checkoutapi/'.$this->getRequest()->getParam("type"));
        
        $response = $standard->getTrayCheckoutRequest("/v2/transactions/pay_complete",$standard->getCheckoutFormFields());
        
        
        $xml = simplexml_load_string($response);
        $this->order_number = str_replace($standard->getConfigData('prefixo'),'',$xml->data_response->transaction->order_number);
        $this->order_number_tc = $xml->data_response->transaction->order_number;
        $this->transaction_id = $xml->data_response->transaction->transaction_id;
        $this->url_payment = $xml->data_response->transaction->payment->url_payment;
        $this->typeful_line = $xml->data_response->transaction->payment->linha_digitavel;
        $this->status_id = $xml->data_response->transaction->status_id;
        $this->status_name = $xml->data_response->transaction->status_name;
        $this->payment_method_name = $xml->data_response->transaction->payment->payment_method_name;
        $this->payment_method_id = $xml->data_response->transaction->payment->payment_method_id;
        $this->token_account = $standard->getConfigData('token');
        
        $standard->updateTransactionTrayCheckout($xml->data_response->transaction);
        
        echo "";
    }
}