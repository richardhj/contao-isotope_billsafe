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
$GLOBALS['ISO_LANG']['PAY']['billsafe'] = array('BillSAFE', 'Dieses Modul unterstützt "Name-Value Pair" (NVP).');

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['pay_with_billsafe']	= array('Bezahlen mit BillSAFE', 'Sie werden nun an BillSAFE zur Bezahlung Ihrere Bestellung weitergeleitet. Wenn Sie nicht sofort weitergeleitet werden, klicken Sie bitte auf "Jetzt bezahlen".', 'Jetzt bezahlen');

$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['housenumber'] = 'Bitte überprüfen Sie Ihre Adresse auf eine fehlende Hausnummer.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['dateOfBirth'] = 'Bitte überprüfen Sie Ihr Geburtsdatum auf Vollständigkeit und Richtigkeit.';

$GLOBALS['ISO_LANG']['billsafe']['product_types']['invoice'] = 'Rechnung';
$GLOBALS['ISO_LANG']['billsafe']['product_types']['installment'] = 'Ratenkauf';

$GLOBALS['ISO_LANG']['billsafe']['tc']['accept'] = 'Ich stimme den <href="https://www.billsafe.de/privacy-policy/buyer">Datenschutzgrundsätzen</a> und der <a href="https://www.billsafe.de/privacy-policy/credit-check">Bonitätsprüfung</a> von <a href="https://www.billsafe.de/imprint">PayPal</a> zu. Es gelten die <a href="https://www.billsafe.de/resources/docs/pdf/Kaeufer_AGB.pdf">Allgemeinen Nutzungsbedingungen</a> für den Rechnungskauf.';
