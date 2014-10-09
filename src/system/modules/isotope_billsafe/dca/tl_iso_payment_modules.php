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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['billsafe'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},billsafe_merchantId,billsafe_merchantLicense,billsafe_applicationSignature,billsafe_product,billsafe_method;{price_legend:hide},price,tax_class;{template_legend},button;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['billsafe_merchantId'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_merchantId'],
	'inputType'         => 'text',
	'eval'              => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['billsafe_merchantLicense'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_merchantLicense'],
	'inputType'         => 'text',
	'eval'              => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['billsafe_applicationSignature'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_applicationSignature'],
	'inputType'         => 'text',
	'eval'              => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['billsafe_product'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_product'],
	'exclude'           => true,
	'default'           => 'invoice',
	'inputType'         => 'select',
	'options'           => array('invoice', 'installment'),
	'reference'	        => $GLOBALS['ISO_LANG']['billsafe']['product_types'],
	'eval'              => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['billsafe_method'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['billsafe_method'],
	'exclude'           => true,
	'inputType'         => 'select',
	'default'           => 'POST',
	'options'           => array('POST'),
	'eval'              => array('mandatory'=>true, 'tl_class'=>'w50'),
);
