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

/** @var $installer Mage_Paypal_Model_Resource_Setup */
$installer = $this;

/**
 * Prepare database for install
 */
$installer->startSetup();

/**
 * Add paypal attributes to the:
 *  - sales/flat_quote_payment_item table
 *  - sales/flat_order table
 */
//$installer->addAttribute('quote_payment', 'traycheckout_transaction_id', array());
//$installer->addAttribute('quote_payment', 'traycheckout_split_number', array());
//$installer->addAttribute('quote_payment', 'traycheckout_split_value', array());
//$installer->addAttribute('quote_payment', 'traycheckout_token_transaction', array());
//$installer->addAttribute('quote_payment', 'traycheckout_url_payment', array());
//$installer->addAttribute('quote_payment', 'traycheckout_typeful_line', array());


$installer->run("
    ALTER TABLE  {$this->getTable('sales_flat_quote_payment')} ADD  `traycheckout_transaction_id` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_quote_payment')} ADD  `traycheckout_split_number` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_quote_payment')} ADD  `traycheckout_split_value` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_quote_payment')} ADD  `traycheckout_token_transaction` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_quote_payment')} ADD  `traycheckout_url_payment` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_quote_payment')} ADD  `traycheckout_typeful_line` VARCHAR( 255 ) NULL DEFAULT NULL;
 
    ALTER TABLE  {$this->getTable('sales_flat_order_payment')} ADD  `traycheckout_transaction_id` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_order_payment')} ADD  `traycheckout_split_number` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_order_payment')} ADD  `traycheckout_split_value` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_order_payment')} ADD  `traycheckout_token_transaction` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_order_payment')} ADD  `traycheckout_url_payment` VARCHAR( 255 ) NULL DEFAULT NULL;
    ALTER TABLE  {$this->getTable('sales_flat_order_payment')} ADD  `traycheckout_typeful_line` VARCHAR( 255 ) NULL DEFAULT NULL;
");
/**
 * Prepare database after install
 */
    
$installer->endSetup();
