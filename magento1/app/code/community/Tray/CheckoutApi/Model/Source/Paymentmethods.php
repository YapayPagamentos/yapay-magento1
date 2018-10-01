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

class Tray_CheckoutApi_Model_Source_Paymentmethods
{
    public function toOptionArray()
    {
        return array(
            array('value' => '3', 'label'=>Mage::helper('adminhtml')->__('Visa')),
            array('value' => '4', 'label'=>Mage::helper('adminhtml')->__('Mastercard')),
            array('value' => '2', 'label'=>Mage::helper('adminhtml')->__('Diners')),
            array('value' => '5', 'label'=>Mage::helper('adminhtml')->__('American Express')),
            array('value' => '18', 'label'=>Mage::helper('adminhtml')->__('Aura')),
            array('value' => '16', 'label'=>Mage::helper('adminhtml')->__('Elo')),
            array('value' => '15', 'label'=>Mage::helper('adminhtml')->__('Discover')),
            array('value' => '19', 'label'=>Mage::helper('adminhtml')->__('JCB')),
            array('value' => '20', 'label'=>Mage::helper('adminhtml')->__('Hipercard')),
            array('value' => '25', 'label'=>Mage::helper('adminhtml')->__('Hiper')),
        );
    }
}