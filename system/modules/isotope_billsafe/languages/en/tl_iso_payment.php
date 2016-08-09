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
 * Fields
 */
$GLOBALS['TL_LANG'][$table]['billsafe_merchantId'] = [
    'Merchant ID',
    'By BillSAFE awarded for your shop vendor ID.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_merchantLicense'] = [
    'Merchant license',
    'Your award of BillSAFE seller license key. This is <b>not</b> your password.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_applicationSignature'] = [
    'Application signature',
    'By BillSAFE assigned identifier used to identify your application.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_publicKey'] = [
    'public key',
    'By BillSAFE assigned public key for the OnSite checkout implementation.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_product'] = [
    'Payment product',
    'Label of the selected payment method.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_onsiteCheckout'] = [
    'Use OnSite checkout',
    'The customer will not be redirected to the BillSAFE gateway. You have to inform BillSAFE about the usage of the <em>processOrder</em> function (accordant additional arrangement).',
];

$GLOBALS['TL_LANG'][$table]['billsafe_method'] = [
    'Method',
    'Method of data transmission.',
];
