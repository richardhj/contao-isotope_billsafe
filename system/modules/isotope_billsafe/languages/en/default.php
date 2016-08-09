<?php
/**
 * Isotope BillSAFE payment method for Contao Open Source CMS
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package Isotope
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */


/**
 * Payment modules
 */
$GLOBALS['ISO_LANG']['PAY']['billsafe'] = [
    'BillSAFE',
    'This module supports "Name-Value Pair" (NVP).',
];


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['pay_with_billsafe'] = [
    'Pay with BillSAFE',
    'You will be redirected to Bill safe payment may wish to order. If you are not immediately redirected, please click on "Pay Now".',
    'Pay Now',
];

$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['housenumber'] = 'Please check your address for the house number.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['postcode'] = 'Please check your address for the zip code.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['dateOfBirth'] = 'Please check your date of birth for completeness and trueness.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['default'] = 'An error with your entered data occurred. The field "%s" could not be processed. Please correct and try again.';

$GLOBALS['ISO_LANG']['billsafe']['product_types']['invoice'] = 'Invoice';
$GLOBALS['ISO_LANG']['billsafe']['product_types']['installment'] = 'Installment';

$GLOBALS['ISO_LANG']['billsafe']['tc']['accept'] = 'I accept the <href="https://www.billsafe.de/privacy-policy/buyer">privacy policy</a> and the <a href="https://www.billsafe.de/privacy-policy/credit-check">credit check</a> by <a href="https://www.billsafe.de/imprint">PayPal</a>. This <a href="https://www.billsafe.de/resources/docs/pdf/Kaeufer_AGB.pdf">terms and conditions</a> for the invoice buy applies.';
$GLOBALS['ISO_LANG']['billsafe']['tc']['error_missing'] = 'Please check all mandatory fields and click "Next".';
