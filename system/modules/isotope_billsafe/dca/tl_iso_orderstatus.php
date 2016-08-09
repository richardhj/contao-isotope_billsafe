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
$table = Isotope\Model\OrderStatus::getTable();


/**
 * Palettes
 */
$GLOBALS['TL_DCA'][$table]['palettes']['default'] = str_replace
(
    ',saferpay_status',
    ',billsafe_status,saferpay_status',
    $GLOBALS['TL_DCA'][$table]['palettes']['default']
);


/**
 * Fields
 */
$GLOBALS['TL_DCA'][$table]['fields']['billsafe_status'] = [
    'label'     => &$GLOBALS['TL_LANG'][$table]['billsafe_status'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['shipment', 'canellation'],
    'reference' => &$GLOBALS['TL_LANG'][$table]['billsafe_status'],
    'eval'      => [
        'includeBlankOption' => true,
        'tl_class'           => 'w50',
    ],
    'sql'       => "varchar(16) NOT NULL default ''",
];
