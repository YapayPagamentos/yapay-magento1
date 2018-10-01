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

class Tray_CheckoutApi_Block_Info_Standard extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('tray/checkoutapi/info/standard.phtml');
    }
        
    public function getLinkPayment($order) 
    {
        if ($this->getRequest()->getRouteName() != 'checkout') {
            $_order = $order;
            $incrementid = $_order->getData('increment_id');
            $quoteid = $_order->getData('quote_id');
		
            $hash = Mage::getModel('core/encryption')->encrypt($incrementid . ":" . $quoteid);
            $method = $_order->getPayment()->getMethod();
            
            //Mage::log($_order->getCustomerId(), null, 'traycheckout.log');
            //Mage::log(Mage::getModel('checkoutapi/payment')->getPayment(), null, 'traycheckout.log');
            //Mage::log('Chamada: ', null, 'traycheckout.log');
            
            if ($method == "traycheckoutapi" && ($_order->getStatus() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT )){
                return '<span>Para efetuar o pagamento, <a href="' . Mage::getBaseUrl() . 'checkoutapi/standard/paymentbackend/order/' . $hash . '">clique aqui</a>.</span>';
            }
        }
    }
    
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array(
            Mage::helper('payment')->__('Payment Method ID') => $info->getTraycheckoutTransactionId(),
            Mage::helper('payment')->__('Split Number') => $info->getTraycheckoutSplitNumber(),
            Mage::helper('payment')->__('Split Value') => $info->getTraycheckoutSplitValue()
        ));
        return $transport;
    }

}