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

$GLOBALS['ISO_LANG']['billsafe']['product_types']['invoice'] = 'Rechnung';
$GLOBALS['ISO_LANG']['billsafe']['product_types']['installment'] = 'Ratenkauf';
