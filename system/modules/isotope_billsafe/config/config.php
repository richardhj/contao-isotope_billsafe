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
 * BillSAFE Version
 */
@define('APP_VERSION', 'v1.0 (2013-08-24)');
@define('SDK_SIGNATURE', 'BillSAFE SDK (PHP) 2012-02-09');


/**
 * Payment methods
 */
\Isotope\Model\Payment::registerModelType('billsafe', 'Isotope\Model\Payment\BillSAFE');
//\Isotope\Model\Payment::registerModelType('billsafe_onsite', 'Isotope\Model\Payment\BillSAFE\OnSiteCheckout');


/**
 * Checkout steps
 */
$GLOBALS['ISO_CHECKOUTSTEP']['payment'][] = 'Isotope\CheckoutStep\PaymentBillSAFE';


/**
 * Hooks
 */
//$GLOBALS['ISO_HOOKS']['generateCollection'][] = array('PaymentBillSAFEHelper', 'generateCollection');
//$GLOBALS['ISO_HOOKS']['getOrderEmailData'][] = array('PaymentBillSAFEHelper', 'getOrderEmailData');
$GLOBALS['ISO_HOOKS']['postCheckout'][] = ['Isotope\BillSAFE\Hooks', 'setInvoiceNumber'];
$GLOBALS['ISO_HOOKS']['getOrderNotificationTokens'][] = ['Isotope\BillSAFE\Hooks', 'addInstructionToNotificationTokens'];


/**
 * Cron
 */
$GLOBALS['TL_CRON']['weekly'][] = ['PaymentBillSAFEHelper', 'updateDatePaid'];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'] = array_merge(
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'],
    [
        'billsafe_instruction_exists',
        'billsafe_instruction_recipient',
        'billsafe_instruction_bankName',
        'billsafe_instruction_bic',
        'billsafe_instruction_iban',
        'billsafe_instruction_reference',
        'billsafe_instruction_amount',
        'billsafe_instruction_currencyCode',
        'billsafe_instruction_shopUrl',
        'billsafe_instruction_note',
        'billsafe_instruction_legalNote',
        'billsafe_instruction_paymentPeriod'
    ]
);
