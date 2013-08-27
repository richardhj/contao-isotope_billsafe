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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_orderstatus']['palettes']['default'] = str_replace
(
  'paid',
  'paid,shipped',
  $GLOBALS['TL_DCA']['tl_iso_orderstatus']['palettes']['default']
); 
 

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_orderstatus']['shipped'] = array
(
  'label'						=> &$GLOBALS['TL_LANG']['tl_iso_orderstatus']['shipped'],
  'exclude'					=> true,
  'inputType'				=> 'checkbox',
  'eval'						=> array('tl_class'=>'w50'),
);
