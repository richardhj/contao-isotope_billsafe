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
 * Payment modules
 */
$GLOBALS['ISO_LANG']['PAY']['billsafe'] = array('BillSAFE', 'This module supports "Name-Value Pair" (NVP).');


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['pay_with_billsafe']	= array('Pay with BillSAFE', 'You will be redirected to Bill safe payment may wish to order. If you are not immediately redirected, please click on "Pay Now".', 'Pay Now');

$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['housenumber'] = 'Please check your address for the house number.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['dateOfBirth'] = 'Please check your date of birth for completeness and trueness.';

$GLOBALS['ISO_LANG']['billsafe']['product_types']['invoice'] = 'Invoice';
$GLOBALS['ISO_LANG']['billsafe']['product_types']['installment'] = 'Installment';
