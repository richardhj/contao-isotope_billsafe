<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * BillSAFE for Isotope eCommerce 
 * Isotope eCommerce the eCommerce module for Contao Open Source CMS
 *
 * PHP Version 5.3
 * 
 * @copyright  Kirsten Roschanski 2013
 * @author     Kirsten Roschanski
 * @author     Richard Henkenjohann
 * @package    BillSAFE
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @filesource
 */


/**
 * BillSAFE Version
 */
@define('APP_VERSION', 'v1.0 (2013-08-24)');
@define('SDK_SIGNATURE', 'BillSAFE SDK (PHP) 2012-02-09');


/**
 * Payment modules
 */
$GLOBALS['ISO_PAY']['billsafe'] = 'PaymentBillSAFE';


/**
 * Hooks
 */
$GLOBALS['ISO_HOOKS']['postOrderStatusUpdate'][] = array('PaymentBillSAFEHelper', 'processOrderStatusUpdate');
$GLOBALS['ISO_HOOKS']['generateCollection'][] = array('PaymentBillSAFEHelper', 'generateCollection');
$GLOBALS['ISO_HOOKS']['getOrderEmailData'][] = array('PaymentBillSAFEHelper', 'getOrderEmailData');
$GLOBALS['ISO_HOOKS']['postCheckout'][] = array('PaymentBillSAFEHelper', 'setInvoiceNumber');
