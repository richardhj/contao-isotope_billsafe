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
    'Verkäufer ID',
    'Von BillSAFE für Ihren Shop vergebene Verkäufer-ID.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_merchantLicense'] = [
    'Lizenzschlüssel',
    'Ihr von BillSAFE vergebener Verkäufer-Lizenzschlüssel. Dies ist <b>nicht</b> Ihr Passwort.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_applicationSignature'] = [
    'Applikations-Signatur',
    'Von BillSAFE vergebene Kennung zur Identifikation Ihrer Applikation.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_publicKey'] = [
    'Public-Key',
    'Von BillSAFE vergebener Public-Key für die OnSite-Checkout-Implementation.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_product'] = [
    'Zahlart',
    'Kennung der ausgewählten Zahlart.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_onsiteCheckout'] = [
    'OnSite-Checkout verwenden',
    'Beim OnSite-Checkout wird der Kunde nicht über das BillSAFE-Gateway geleitet. Bitte beachten Sie, dass dies eine mit BillSAFE entsprechende Zusatzvereinbarung (Factoringvertrag) bedarf.',
];

$GLOBALS['TL_LANG'][$table]['billsafe_method'] = [
    'Methode',
    'Art der Datenübermittlung.',
];
