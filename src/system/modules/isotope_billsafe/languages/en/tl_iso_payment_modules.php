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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_merchantId'] = array
(
	'Merchant ID',
	'By BillSAFE awarded for your shop vendor ID.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_merchantLicense'] = array
(
	'Merchant license',
	'Your award of BillSAFE seller license key. This is <b>not</b> your password.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_applicationSignature'] = array
(
	'Application signature',
	'By BillSAFE assigned identifier used to identify your application.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_publicKey'] = array
(
	'public key',
	'By BillSAFE assigned public key for the OnSite checkout implementation.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_product'] = array
(
	'Payment product',
	'Label of the selected payment method.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_onsiteCheckout'] = array
(
	'Use OnSite checkout',
	'The customer will not be redirected to the BillSAFE gateway. You have to inform BillSAFE about the usage of the <em>processOrder</em> function (accordant additional arrangement).'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_method'] = array
(
	'Method',
	'Method of data transmission.'
);
