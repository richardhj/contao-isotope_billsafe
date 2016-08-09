<?php
/**
 * Isotope BillSAFE payment method for Contao Open Source CMS
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package Isotope
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */


/** @noinspection PhpUndefinedMethodInspection */
$table = Isotope\Model\Payment::getTable();


/**
 * Palettes
 */
$GLOBALS['TL_DCA'][$table]['palettes']['billsafe'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},billsafe_merchantId,billsafe_merchantLicense,billsafe_applicationSignature,billsafe_publicKey,billsafe_product,billsafe_onsiteCheckout,billsafe_gatewayImage;{price_legend:hide},price,tax_class;{enabled_legend},debug,enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA'][$table]['fields']['billsafe_merchantId'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_merchantId'],
    'inputType' => 'text',
    'eval'      => [
        'mandatory' => true,
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'sql'       => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$table]['fields']['billsafe_merchantLicense'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_merchantLicense'],
    'inputType' => 'text',
    'eval'      => [
        'mandatory' => true,
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$table]['fields']['billsafe_applicationSignature'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_applicationSignature'],
    'inputType' => 'text',
    'eval'      => [
        'mandatory' => true,
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$table]['fields']['billsafe_publicKey'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_publicKey'],
    'inputType' => 'text',
    'eval'      => [
        'mandatory' => true,
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$table]['fields']['billsafe_product'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_product'],
    'exclude'   => true,
    'default'   => 'invoice',
    'inputType' => 'select',
    'options'   => ['invoice', 'installment'],
    'reference' => $GLOBALS['ISO_LANG']['billsafe']['product_types'],
    'eval'      => [
        'mandatory' => true,
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$table]['fields']['billsafe_onsiteCheckout'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_onsiteCheckout'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50 m12'],
    'sql'       => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA'][$table]['fields']['billsafe_gatewayImage'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_gatewayImage'],
    'exclude'   => true,
    'inputType' => 'fileTree',
    'eval'      => [
        'filesOnly'  => true,
        'extensions' => Config::get('validImageTypes'),
        'fieldType'  => 'radio',
    ],
    'sql'       => "binary(16) NULL",
];
