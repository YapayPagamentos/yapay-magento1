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

class Tray_CheckoutApi_Block_Onepage_Success extends Mage_Checkout_Block_Onepage_Success
{
    protected $order_number;
    protected $order_number_tc;
    protected $transaction_id;
    protected $url_payment;
    protected $typeful_line;
    protected $payment_response;
    protected $status_id;
    protected $status_name;
    protected $payment_method_id;
    protected $payment_method_name;
    protected $token_account;
    protected $wasPaidWithYapay;


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
    public function getPaymentResponse() {
        return $this->payment_response;
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

    public function getRealOrderId()
    {
        $order = Mage::getModel('sales/order')->load($this->getLastOrderId());
        #print_r($order->getData());
        return $order->getIncrementId();
    }

    public function wasPaidWithYapay()
    {
        return $this->wasPaidWithYapay;
    }


    protected function getPayment()
    {

        $quote_id = Mage::getSingleton('checkout/session')->getData('last_quote_id');

        $quote = Mage::getModel('sales/quote')->load($quote_id);

        switch ($quote->getPayment()->getData('method')) {
            case 'traycheckoutapi_bankslip':
                $standard = Mage::getModel('checkoutapi/bankslip');
                break;
            case 'traycheckoutapi':
                $standard = Mage::getModel('checkoutapi/standard');
                break;
            case 'traycheckoutapi_onlinetransfer':
                $standard = Mage::getModel('checkoutapi/onlinetransfer');
                break;
        }

        if($standard == null)
        {
            $this->wasPaidWithYapay = false;
            return false;
        } else {
            $this->wasPaidWithYapay = true;
        }




        $response = $standard->getTrayCheckoutRequest("/v2/transactions/pay_complete",$standard->getCheckoutFormFields());

        $xml = simplexml_load_string($response);
        $this->order_number = str_replace($standard->getConfigData('prefixo'),'',$xml->data_response->transaction->order_number);
        $this->order_number_tc = $xml->data_response->transaction->order_number;
        $this->transaction_id = $xml->data_response->transaction->transaction_id;
        $this->url_payment = $xml->data_response->transaction->payment->url_payment;
        $this->typeful_line = $xml->data_response->transaction->payment->linha_digitavel;
        $this->payment_response = $xml->data_response->transaction->payment->payment_response;
        $this->status_id = $xml->data_response->transaction->status_id;
        $this->status_name = $xml->data_response->transaction->status_name;
        $this->payment_method_name = $xml->data_response->transaction->payment->payment_method_name;
        $this->payment_method_id = $xml->data_response->transaction->payment->payment_method_id;
        $this->token_account = $standard->getConfigData('token');

        $standard->updateTransactionTrayCheckout($xml->data_response->transaction);

        echo '';
    }
}