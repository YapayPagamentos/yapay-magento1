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

class Tray_CheckoutApi_Helper_Data extends Tray_CheckoutApi_Helper_Adress
{
    public function prepareLineItems(Mage_Core_Model_Abstract $salesEntity, $discountTotalAsItem = true, $shippingTotalAsItem = false)
    {
        $items = array();
        
        foreach ($salesEntity->getAllItems() as $item) {
            
            if (!$item->getParentItem()) {
                $items[] = new Varien_Object($this->_prepareLineItemFields($salesEntity, $item));
            }
        }
        
        $discountAmount = 0;
        
        $shippingDescription = '';
        
        if ($salesEntity instanceof Mage_Sales_Model_Order) {
            
            $discountAmount = abs(1 * $salesEntity->getBaseDiscountAmount());
            
            $shippingDescription = $salesEntity->getShippingDescription();
            
            $totals = array(
                'subtotal' => $salesEntity->getBaseSubtotal() - $discountAmount,
                'tax'      => $salesEntity->getBaseTaxAmount(),
                'shipping' => $salesEntity->getBaseShippingAmount(),
                'discount' => $discountAmount
            );
        } else {
            
            $address = $salesEntity->getIsVirtual() ? $salesEntity->getBillingAddress() : $salesEntity->getShippingAddress();
            
            $discountAmount = abs(1 * $address->getBaseDiscountAmount());
            
            $shippingDescription = $address->getShippingDescription();
            
            $totals = array (
                'subtotal' => $salesEntity->getBaseSubtotal() - $discountAmount,
                'tax'      => $address->getBaseTaxAmount(),
                'shipping' => $address->getBaseShippingAmount(),
                'discount' => $discountAmount
            );
        }

        // discount total as line item (negative)
        if ($discountTotalAsItem && $discountAmount) {
            $items[] = new Varien_Object(array(
                'name'   => Mage::helper('checkoutapi')->__('Discount'),
                'qty'    => 1,
                'amount' => -1.00 * $discountAmount,
            ));
        }
        
        // shipping total as line item
        if ($shippingTotalAsItem && (!$salesEntity->getIsVirtual()) && (float)$totals['shipping']) {
            $items[] = new Varien_Object(array(
                'id'     => Mage::helper('checkoutapi')->__('Shipping'),
                'name'   => $shippingDescription,
                'qty'    => 1,
                'amount' => (float) $totals['shipping'],
            ));
        }

        $hiddenTax = (float) $salesEntity->getBaseHiddenTaxAmount();
        
        if ($hiddenTax) {
            $items[] = new Varien_Object(array(
                'name'   => Mage::helper('checkoutapi')->__('Discount Tax'),
                'qty'    => 1,
                'amount' => (float)$hiddenTax,
            ));
        }

        return array($items, $totals, $discountAmount, $totals['shipping']);
    }

    /**
     * Get one line item key-value array
     *
     * @param Mage_Core_Model_Abstract $salesEntity
     * @param Varien_Object $item
     * @return array
     */
    protected function _prepareLineItemFields(Mage_Core_Model_Abstract $salesEntity, Varien_Object $item)
    {
        if ($salesEntity instanceof Mage_Sales_Model_Order) {
            $qty = $item->getQtyOrdered();
            $amount = $item->getBasePrice();
            // TODO: nominal item for order
        } else {
            $qty = $item->getTotalQty();
            $amount = $item->isNominal() ? 0 : $item->getBaseCalculationPrice();
        }
        
        // workaround in case if item subtotal precision is not compatible with PayPal (.2)
        $subAggregatedLabel = '';
        
        if ((float) $amount - round((float) $amount, 2)) {
            $amount = $amount * $qty;
            $subAggregatedLabel = ' x' . $qty;
            $qty = 1;
        }
        
        return array(
            'id'     => $item->getSku(),
            'name'   => $item->getName() . $subAggregatedLabel,
            'qty'    => $qty,
            'amount' => (float)$amount,
        );
    }   
}