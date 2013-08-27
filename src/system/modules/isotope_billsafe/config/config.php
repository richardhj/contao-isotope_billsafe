<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * BillSAFE for Isotope eCommerce 
 * Isotope eCommerce the eCommerce module for Contao Open Source CMS
 *
 * PHP Version 5.3
 * 
 * @copyright  Kirsten Roschanski 2013
 * @package    BillSAFE
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       https://github.com/katgirl/isotope-billsafe
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
