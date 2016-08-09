<?php
/**
 * Isotope BillSAFE payment method for Contao Open Source CMS
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package Isotope
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */


namespace Isotope\BillSAFE;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Isotope;
use Isotope\Model\Payment\BillSAFE;
use Isotope\Model\ProductCollection\Order;


/**
 * Class Hooks
 * @package Isotope\BillSAFE
 */
class Hooks
{

    /**
     * Report invoice number to BillSAFE
     * @category ISO_HOOKS: postCheckout
     *
     * @param Order|\Model $order
     */
    public function setInvoiceNumber(Order $order)
    {
        /** @var IsotopePayment|BillSAFE $payment */
        $payment = $order->getPaymentMethod();

        if ('billsafe' !== $payment->type) {
            return;
        }

        $request = $payment->callMethod(
            'setInvoiceNumber',
            [
                'invoiceNumber' => $order->document_number,
                'transactionId' => $order->billsafe_transactionId,
            ]
        );

        parse_str($request->response, $response);

        if ($response['ack'] === 'ERROR') {
            \System::log(
                'BillSAFE NVP: '.$response['errorList_0_code']." ".$response['errorList_0_message'],
                __METHOD__,
                TL_ERROR
            );
        }
    }


    /**
     * Add the BillSAFE payment instructions to the notification tokens array
     * @category ISO_HOOKS: getOrderNotificationTokens
     *
     * @param Order $order
     * @param array $tokens
     *
     * @return array
     */
    public function addInstructionToNotificationTokens(Order $order, array $tokens)
    {
        /** @var IsotopePayment|BillSAFE $payment */
        $payment = $order->getPaymentMethod();

        if ('billsafe' !== $payment->type) {
            return $tokens;
        }

        $paymentData = deserialize($order->payment_data);

        $tokens['billsafe_instruction_exists'] = ('OK' === $paymentData['ack']) ? 1 : 0;
        $tokens = array_merge(
            $tokens,
            array_combine(
                array_map(
                    function ($k) {
                        return 'billsafe_'.$k;
                    },
                    array_keys($paymentData)
                ),
                $paymentData
            )
        );

        // Format amount
        $tokens['billsafe_instruction_amount'] = Isotope::formatPriceWithCurrency(
            $tokens['billsafe_instruction_amount'],
            false,
            $tokens['billsafe_instruction_currencyCode']
        );

        return $tokens;
    }
}
