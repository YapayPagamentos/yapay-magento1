<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Tray_CheckoutApi_OnepageController extends Mage_Checkout_OnepageController  {

    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        //$session->clear();
        $this->loadLayout();
        #Google Analytics
        $block = $this->getLayout()->getBlock('google_analytics');
        if ($block) {
            /** @var Mage_Checkout_Model_Session $session */
            $session = Mage::getSingleton('checkout/session');
            if ($session->getLastRealOrder()) {
                $block->setOrderIds([$session->getLastRealOrder()->getId()]);
            }
        }

        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }
}
?>