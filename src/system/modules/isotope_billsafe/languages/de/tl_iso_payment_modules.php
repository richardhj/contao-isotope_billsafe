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
	'Verkäufer ID',
	'Von BillSAFE für Ihren Shop vergebene Verkäufer-ID.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_merchantLicense'] = array
(
	'Lizenzschlüssel',
	'Ihr von BillSAFE vergebener Verkäufer-Lizenzschlüssel. Dies ist <b>nicht</b> Ihr Passwort.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_applicationSignature'] = array
(
	'Applikations-Signatur',
	'Von BillSAFE vergebene Kennung zur Identifikation Ihrer Applikation.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_product'] = array
(
	'Zahlart',
	'Kennung der ausgewählten Zahlart.'
);

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_method'] = array
(
	'Methode',
	'Art der Datenübermittlung.'
);
