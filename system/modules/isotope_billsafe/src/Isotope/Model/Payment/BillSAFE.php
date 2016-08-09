<?php
/**
 * Isotope BillSAFE payment method for Contao Open Source CMS
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package Isotope
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */


namespace Isotope\Model\Payment;

use Contao\Database;
use Haste\Form\Form;
use Isotope\Interfaces\IsotopeOrderStatusAware;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\OrderStatus;
use Isotope\Model\Payment;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionSurcharge\Tax;
use Isotope\Model\TaxClass;
use Isotope\Module\Checkout;


/**
 * Class BillSAFE
 * @property string $billsafe_merchantId
 * @property string $billsafe_merchantLicense
 * @property string $billsafe_applicationSignature
 * @property string $billsafe_publicKey
 * @property string $billsafe_product
 * @property bool   $billsafe_onsiteCheckout
 * @property mixed  $billsafe_gatewayImage
 * @package Isotope\Model\Payment
 */
class BillSAFE extends Payment implements IsotopePayment, IsotopeOrderStatusAware
{

    /**
     * @var string
     */
    private $apiUrlSandbox = 'https://sandbox-nvp.billsafe.de/V211';


    /**
     * @var string
     */
    private $apiUrlLive = 'https://nvp.billsafe.de/V211';


    /**
     * @var string
     */
    protected $apiUrl;


    /**
     * @var string
     */
    private $gatewayUrlSandbox = 'https://sandbox-payment.billsafe.de/V200';


    /**
     * @var string
     */
    private $gatewayUrlLive = 'https://payment.billsafe.de/V200';


    /**
     * @var string
     */
    protected $gatewayUrl;


    /**
     * BillSAFE constructor.
     * Set the urls by debug state
     *
     * @param \Database\Result|null $result
     */
    public function __construct(\Database\Result $result = null)
    {
        parent::__construct($result);

        $this->apiUrl = (!$this->debug) ? $this->apiUrlLive : $this->apiUrlSandbox;
        $this->gatewayUrl = (!$this->debug) ? $this->gatewayUrlLive : $this->gatewayUrlSandbox;
    }


    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        $billingAddress = Isotope::getCart()->getBillingAddress();
        $shippingAddress = Isotope::getCart()->getShippingAddress();

        if (null === $billingAddress || !in_array($billingAddress->country, ['de', 'ch', 'at'])) {
            return false;
        }

        // BillSAFE is not supported when billing and shipping address are not equal
        if (Isotope::getCart()->hasShipping() && $billingAddress->id !== $shippingAddress->id) {
            return false;
        }

        return parent::isAvailable();
    }


    /**
     * Process payment on checkout confirmation page
     *
     * @param IsotopeProductCollection|Order $order
     * @param Checkout|\Module               $module
     *
     * @return bool
     */
    public function processPayment(IsotopeProductCollection $order, \Module $module)
    {
        if ($this->billsafe_onsiteCheckout) {
            if (!$_SESSION['CHECKOUT_DATA']['payment'][$this->id]['billsafe_tc']) {
                $_SESSION['CHECKOUT_DATA']['responseMsg'] = $GLOBALS['ISO_LANG']['billsafe']['tc']['error_missing'];
                \System::log(
                    'Someone missed the tc checkbox. Value: '.print_r(
                        $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['billsafe_tc'],
                        true
                    ),
                    __METHOD__,
                    TL_ERROR
                );
                Checkout::redirectToStep('payment', $order);
            } elseif ('declined' === $_SESSION['CHECKOUT_DATA']['payment']['status']) {
                Checkout::redirectToStep('payment', $order);
            }

            $request = $this->callMethod('processOrder', $this->returnOrderRequestParam($order));
        } else {
            $params['token'] = \Input::get('token', true);

            $request = $this->callMethod('getTransactionResult', $params);
        }


        parse_str($request->response, $response);

        if ('ERROR' === $response['ack']) {
            //@todo handle 215 (missing) and 216 (invalid) parameters
//			// Handle invalid parameters
//			if ($arrRequest['errorList_0_code'] == 216)
//			{
//				$arrErrorMessageWords = trimsplit(' ', $arrRequest['errorList_0_message']);
//				$strErrorParameter = $arrErrorMessageWords[1];
//
//				switch ($strErrorParameter)
//				{
//					case 'housenumber':
//						$this->redirect($this->addToUrl('step=failed', true) . '?reason=' . urlencode($GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['housenumber']));
//						break;
//
//					case 'postcode':
//						$this->redirect($this->addToUrl('step=failed', true) . '?reason=' . urlencode($GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['postcode']));
//						break;
//
//					case 'dateOfBirth':
//						$this->redirect($this->addToUrl('step=failed', true) . '?reason=' . urlencode($GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['dateOfBirth']));
//						break;
//
//					default:
//						$this->redirect($this->addToUrl('step=failed', true) . '?reason=' . urlencode(sprintf($GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['default'], $strErrorParameter)));
//						break;
//				}
//			}

            \System::log(
                'BillSAFE payment could not be processed. NVP: '.$response['errorList_0_code'].' '.$response['errorList_0_message'],
                __METHOD__,
                TL_ERROR
            );

            Checkout::redirectToStep('failed', $order);
        } elseif ('OK' === $response['ack']) {
            if ('ACCEPTED' === $response['status']) {
                // Update order status
                $order->updateOrderStatus($this->new_order_status);

                // Mark date_paid for further processing
                $order->date_paid = 0;

                $order->billsafe_transactionId = $response['transactionId'];

                $this->updatePaymentInstruction($order);

                // Save order
                $order->save();

                return true;
            } else {
                if ($this->billsafe_onsiteCheckout) {
                    $_SESSION['CHECKOUT_DATA']['payment']['status'] = 'declined';
                    $_SESSION['CHECKOUT_DATA']['responseMsg'] = $response['declineReason_buyerMessage'];
                }

                \System::log(
                    'BillSAFE payment was declined. NVP: '.$response['declineReason_code'].' '.$response['declineReason_message'],
                    __METHOD__,
                    TL_ERROR
                );
                \Controller::redirect(
                    $module->generateUrlForStep('failed').'?reason='.urlencode($response['declineReason_buyerMessage'])
                );
            }
        }

        \System::log('Payment could not be processed.', __METHOD__, TL_ERROR);
        Checkout::redirectToStep('failed', $order);

        return false;
    }


    /**
     * Redirect the user to the BillSAFE gateway
     *
     * @param IsotopeProductCollection|\Model $order
     * @param Checkout|\Module                $module
     *
     * @return string|false
     */
    public function checkoutForm(IsotopeProductCollection $order, \Module $module)
    {
        if ($this->billsafe_onsiteCheckout) {
            //@todo
            return 'test<strong>test</strong>';
        }

        $request = $this->callMethod('prepareOrder', $this->returnOrderRequestParam($order));
        parse_str($request->response, $response);

        if ('ERROR' === $response['ack']) {
            \System::log(
                'BillSAFE NVP: '.$response['errorList_0_code']." ".$response['errorList_0_message'],
                __METHOD__,
                TL_ERROR
            );

            $reason = (in_array($response['errorList_0_code'], [215, 216]))
                ? $GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error'][$response['errorList_0_code']][trimsplit(
                    ' ',
                    $response['errorList_0_message']
                )[1]]
                : '';

            \Controller::redirect($module->generateUrlForStep('failed').'?reason='.urlencode($reason));
        } elseif ($response['ack'] == 'OK' && !$order->billsafe_token) {
            $order->billsafe_token = $response['token'];

            $order->save();
        }

        if ($order->billsafe_token) {
            \Controller::redirect($this->gatewayUrl."?token=".$order->billsafe_token);
        }

        \System::log('BillSAFE NVP: ack='.$response['ack']." token=".$response['token'], __METHOD__, TL_ERROR);
        $module->redirectToStep('failed');

        return '';
    }


    /**
     * Return information or advanced features in the backend.
     *
     * Use this function to present advanced features or basic payment information for an order in the backend.
     *
     * @param integer Order ID
     *
     * @return string
     */
    public function backendInterface($orderId)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Order|\Model $order */
        if (null === ($order = Order::findByPk($orderId))) {
            return parent::backendInterface($orderId);
        }

        $this->updatePaymentInstruction($order);

        $paymentData = deserialize($order->payment_data);

        $return = '
<div id="tl_buttons">
<a href="'.ampersand(
                str_replace('&key=payment', '', \Environment::get('request'))
            ).'" class="header_back" title="'.specialchars(
                $GLOBALS['TL_LANG']['MSC']['backBT']
            ).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$this->name.' ('.$GLOBALS['ISO_LANG']['PAY'][$this->type][0].')'.'</h2>

<div id="tl_soverview">
<div id="tl_messages">

</div>
</div>

<h3 style="margin-left:18px">'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['payment_data']['legend'].'</h3>
<table class="tl_show">
<tbody>';

        $i = 0;
        foreach ($paymentData as $k => $v) {
            if (is_array($v)) {
                continue;
            }

            $return .= '
  <tr>
    <td'.($i % 2 ? '' : ' class="tl_bg"').'><span class="tl_label">'.$k.': </span></td>
    <td'.($i % 2 ? '' : ' class="tl_bg"').'>'.$v.'</td>
  </tr>';

            ++$i;
        }


        $return .= '
</tbody>
</table>

<div class="tl_formbody_edit">
<h3>'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['legend'].'</h3>
<p>'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['info'].'</p>'.
            $this->updateArticleListForm($order).'

<h3>'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['legend'].'</h3>
<p>'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['info'].'</p>'.
            $this->pauseTransactionForm($order).'

<h3>'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['legend'].'</h3>
<p>'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['info'].'</p>'.
            $this->reportDirectPaymentForm($order).
            '
<br></div>';

        return $return;
    }


    /**
     * Return article list form for the backend interface
     *
     * @param Order|\Model $objOrder
     *
     * @return string
     */
    protected function updateArticleListForm(Order $objOrder)
    {
        $return = '';

        if ($_SESSION['ISO_UPDATEARTICLELIST_SUCCESS']) {
            unset($_SESSION['ISO_UPDATEARTICLELIST_SUCCESS']);
            $return .= '<p class="tl_confirm">'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['message']['confirm'].'</p>';
        } elseif ($_SESSION['ISO_UPDATEARTICLELIST_ERROR']) {
            unset($_SESSION['ISO_UPDATEARTICLELIST_ERROR']);

            return '<p class="tl_error">'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['message']['error'].'</p>';
        }

        // Get current article list
        $params = [
            'transactionId' => $objOrder->billsafe_transactionId,
        ];

        $request = $this->callMethod('getArticleList', $params);
        parse_str($request->response, $response);

        $mcwCurrentArticleList = [];

        // Prepare list for MCW
        foreach ($response as $k => $v) {
            $chunks = explode('_', $k);

            if ('articleList' === $chunks[0]) {
                $mcwCurrentArticleList[$chunks[1]][$chunks[2]] = $v;
            }
        }

        $form = new Form(
            'iso_billsafe_updateArticleList', 'POST', function ($haste) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $haste->getFormId() === \Input::post('FORM_SUBMIT');
        }
        );

        $form->addFormField(
            'articleList',
            [
                'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList'],
                'value'     => $mcwCurrentArticleList,
                'inputType' => 'multiColumnWizard',
                'eval'      => [
                    'columnFields' => [
                        'number'          => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_number'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width:85px'],
                        ],
                        'name'            => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_name'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width:140px'],
                        ],
                        'description'     => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_description'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width:45px', 'readonly' => true],
                        ],
                        'type'            => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_type'],
                            'inputType' => 'select',
                            'options'   => ['goods', 'shipment', 'handling', 'voucher'],
                            'reference' => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_type_options'],
                        ],
                        'quantity'        => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_quantity'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width:20px;text-align:center'],

                        ],
                        'grossPrice'      => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_qrossPrice'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width:45px'],
                        ],
                        'tax'             => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_tax'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width:32px', 'readonly' => true],
                        ],
                        'quantityShipped' => [
                            'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_quantityShipped'],
                            'inputType' => 'text',
                            'eval'      => ['style' => 'width:20px;text-align:center'],
                        ],
                    ],
                ],
            ]
        );
        $form->addFormField(
            'submit',
            [
                'label'     => $GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['submit'],
//            onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['submit_confirm']  . '\');"
                'inputType' => 'submit',
            ]
        );

        $form->addContaoHiddenFields();

        if ($form->validate()) {

            $articleList = $form->fetch('articleList');
            $order_grandTotal = 0;

            foreach ($articleList as $article) {
                $order_grandTotal += ($article['quantity'] * $article['grossPrice']);
            }

            $order_taxAmount = (int)$objOrder->taxTotal > 0 ? $order_grandTotal : 0;

            $params = [
                'transactionId'      => $objOrder->billsafe_transactionId,
                'order_amount'       => number_format($order_grandTotal, 2, '.', ''),
                'order_taxAmount'    => number_format($order_taxAmount, 2, '.', ''),
                'order_currencyCode' => $objOrder->getRelated('config_id')->currency,
                'articleList'        => $articleList,
            ];

            $request = $this->callMethod('updateArticleList', $params);
            parse_str($request->response, $response);

            if ('ERROR' === $response['ack']) {
                $_SESSION['ISO_UPDATEARTICLELIST_ERROR'] = true;
                \System::log(
                    sprintf('BillSAFE NVP: %s %s', $response['errorList_0_code'], $response['errorList_0_message']),
                    __METHOD__,
                    TL_ERROR
                );
            } else {
                $_SESSION['ISO_UPDATEARTICLELIST_SUCCESS'] = true;
                \System::log(
                    'Updated article list was reported to BillSAFE for order ID '.$objOrder->id,
                    __METHOD__,
                    TL_ACCESS
                );
            }
        }

        $return .= $form->generate();

        return $return;
    }


    /**
     * Return pauseTransaction form for the backend interface
     *
     * @param Order|\Model $order
     *
     * @return string
     */
    protected function pauseTransactionForm(Order $order)
    {
        if ($order->iso_billsafe_pause && $order->iso_billsafe_pause > time()) {
            return '<p class="tl_info">'.sprintf(
                $GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['message']['info'],
                date($GLOBALS['TL_CONFIG']['dateFormat'], $order->iso_billsafe_pause)
            ).'</p>';
        } elseif ($_SESSION['ISO_PAUSETRANSACTION_ERROR']) {
            unset($_SESSION['ISO_PAUSETRANSACTION_ERROR']);

            return '<p class="tl_error">'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['message']['error'].'</p>';
        }

        $form = new Form(
            'iso_billsafe_pauseTransaction', 'POST', function ($haste) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $haste->getFormId() === \Input::post('FORM_SUBMIT');
        }
        );

        $form->addFormField(
            'pause',
            [
                'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['fields']['pause'],
                'inputType' => 'text',
                'eval'      => ['mandatory' => true, 'required' => true, 'rgxp' => 'digit'],
            ]
        );

        $form->addFormField(
            'submit',
            [
                'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['fields']['submit'],
                'inputType' => 'submit',
            ]
        );

        $form->addContaoHiddenFields();

        if ($form->validate()) {


            $params = [
                'transactionId' => $order->billsafe_transactionId,
                'pause'         => $form->fetch('pause'),
            ];

            $request = $this->callMethod('pauseTransaction', $params);
            parse_str($request->response, $response);

            if ('ERROR' === $response['ack']) {
                \System::log(
                    sprintf('BillSAFE NVP: %s %s', $response['errorList_0_code'], $response['errorList_0_message']),
                    __METHOD__,
                    TL_ERROR
                );
                $_SESSION['ISO_PAUSETRANSACTION_ERROR'] = true;
            } else {
                $order->iso_billsafe_pause = strtotime('+'.(int)$form->fetch('pause').' days');
                $order->save();

                \System::log('BillSAFE transaction ID '.$order->id.' was paused', __METHOD__, TL_ACCESS);
            }

        }

        return $form->generate();
    }


    /**
     * Return reportDirectPayment form for the backend interface
     *
     * @param Order|\Model $order
     *
     * @return string
     */
    protected function reportDirectPaymentForm(Order $order)
    {
        $return = '';

        if ($_SESSION['ISO_REPORTDIRECTPAYMENT_ERROR']) {
            $return .= '<p class="tl_error">'.$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['message']['error'].'</p>';
            unset($_SESSION['ISO_REPORTDIRECTPAYMENT_ERROR']);
        }

        if (is_array($order->iso_billsafe_directpayments) && !empty($order->iso_billsafe_directpayments)) {
            foreach ($order->iso_billsafe_directpayments as $directpayment) {
                $return .= '<p>'.sprintf(
                        $GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['list_directPayment'],
                        $directpayment['amount'],
                        $order->getRelated('config_id')->currency,
                        date($GLOBALS['TL_CONFIG']['dateFormat'], $directpayment['date'])
                    ).'</p>';
            }
        }

        $form = new Form(
            'iso_billsafe_reportDirectPayment', 'POST', function ($haste) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $haste->getFormId() === \Input::post('FORM_SUBMIT');
        }
        );

        $form->addFormField(
            'amount',
            [
                'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['fields']['amount'],
                'inputType' => 'text',
                'eval'      => ['mandatory' => true, 'required' => true, 'rgxp' => 'digit'],
            ]
        );

        $form->addFormField(
            'date',
            [
                'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['fields']['date'],
                'inputType' => 'text',
                'value'     => date($GLOBALS['TL_CONFIG']['dateFormat']),
                'eval'      => ['mandatory' => true, 'required' => true, 'rgxp' => 'date'],
            ]
        );

        $form->addFormField(
            'submit',
            [
                'label'     => &$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['fields']['submit'],
                'inputType' => 'submit',
            ]
        );

        $form->addContaoHiddenFields();

        if ($form->validate()) {

            $params = [
                'transactionId' => $order->billsafe_transactionId,
                'amount'        => $form->fetch('amount'),
                'currencyCode'  => $order->getRelated('config_id')->currency,
                'date'          => date('Y-m-d', strtotime($form->fetch('date'))),
            ];

            $request = $this->callMethod('reportDirectPayment', $params);
            parse_str($request->response, $response);

            if ('ERROR' === $response['ack']) {
                \System::log(
                    sprintf('BillSAFE NVP: %s %s', $response['errorList_0_code'], $response['errorList_0_message']),
                    __METHOD__,
                    TL_ERROR
                );
                $_SESSION['ISO_REPORTDIRECTPAYMENT_ERROR'] = true;
            } else {
                $order->iso_billsafe_directpayments[] = [
                    'amount' => (float)$form->fetch('amount'),
                    'date'   => strtotime($form->fetch('date')),
                ];

                $order->save();

                \System::log('Direct payment was reportet to BillSAFE for order ID '.$order->id, __METHOD__, TL_ACCESS);
            }

        }

        $return .= $form->generate();

        return $return;
    }


    /**
     * Update order to BillSAFE when changing order status in backend
     *
     * @param IsotopeProductCollection|Order|\Model $order
     * @param int                                   $oldStatus
     * @param OrderStatus|\Model                    $newStatus
     */
    public function onOrderStatusUpdate(Order $order, $oldStatus, OrderStatus $newStatus)
    {
        switch ($newStatus->billsafe_status) {
            /*
             * Report shipment
             */
            case 'shipment':

                // Set date shipped if not done yet
                $order->date_shipped = $order->date_shipped ?: time();

                // Shipping date must not be older than five days
                $intShippingDate = (((time() - $order->date_shipped) / 86400) > 5) ? time() : $order->date_shipped;

                $params = [
                    'transactionId' => $order->billsafe_transactionId,
                    'shippingDate'  => date('Y-m-d', $intShippingDate),
                ];

                $request = $this->callMethod('reportShipment', $params);
                parse_str($request->response, $response);

                if ('ERROR' === $response['ack']) {
                    \System::log(
                        'BillSAFE NVP: '.$response['errorList_0_code']." ".$response['errorList_0_message'],
                        __METHOD__,
                        TL_ERROR
                    );

                    if ('BE' === TL_MODE) {
                        \Message::addError(
                            'Ein Fehler beim Melden des Versands an BillSAFE ist aufgetreten. Bitte überprüfen Sie den System-Log.'
                        );
                    }
                } else {
                    $order->date_shipped = $intShippingDate;
                    $order->save();
                    \System::log(
                        'New order status update reported to BillSAFE: shipment for ID '.$order->id,
                        __METHOD__,
                        TL_ACCESS
                    );

                    if ('BE' === TL_MODE) {
                        \Message::addConfirmation('Der Versand der Bestellung wurde an BillSAFE gemeldet.');
//                        \Message::addInfo($GLOBALS['TL_LANG']['tl_iso_product_collection']['saferpayStatusSuccess']);
                    }
                }

                break;

            /*
             * Report a cancellation by setting the article list empty
             */
            case 'cancellation':

                $config = $order->getRelated('config_id') ?: Isotope::getConfig();

                $params = array
                (
                    'transactionId'      => $order->billsafe_transactionId,
                    'order_amount'       => 0.00,
                    'order_taxAmount'    => 0.00,
                    'order_currencyCode' => $config->currency,
                    'articleList'        => array(),
                );

                $request = $this->callMethod('updateArticleList', $params);
                parse_str($request->response, $response);

                if ($response['ack'] == 'ERROR') {
                    \System::log(
                        'BillSAFE NVP: '.$response['errorList_0_code']." ".$response['errorList_0_message'],
                        __METHOD__,
                        TL_ERROR
                    );

                    if ('BE' === TL_MODE) {
                        \Message::addError('Die Stornierung konnte nicht an BillSAFE übermittelt werden.');
                    }
                } else {
                    \System::log(
                        'New order status update reported to BillSAFE: cancellation for order ID '.$order->id,
                        __METHOD__,
                        TL_ACCESS
                    );

                    if ('BE' === TL_MODE) {
                        \Message::addConfirmation('Die Forderungen von BillSAFE an den Kunden wurden storniert.');
                    }
                }

                break;

            default:
                #todo Should we process a revertReportShipment just because the old order status update had a reportShipment?
        }
    }


    /**
     * Return the params for one order for a BillSAFE request
     *
     * @param Order|\Model $order
     *
     * @return array
     */
    protected function returnOrderRequestParam(Order $order)
    {
//		$order_taxAmount = (int)$objOrder->getTaxFreeTotal() > 0 ? $this->Isotope->Cart->grandTotal : 0;
        $address = $order->getBillingAddress();
        $user = \MemberModel::findByPk($address->pid);

        $config = $order->getRelated('config_id') ?: Isotope::getConfig();

        $params = [
            'order_number'       => $order->id,
            'order_amount'       => number_format($order->getTotal(), 2, '.', ''),
            'order_taxAmount'    => number_format($order->getTotal() - $order->getTaxFreeTotal(), 2, '.', ''),
            'order_currencyCode' => $config->currency,
            'customer'           => [
                'id'          => $address->pid,
                //'company'   => $objAddress->company, // B2B transactions are not allowed by default
                'firstname'   => $address->firstname,
                'lastname'    => $address->lastname,
                'street'      => $address->street_1,
                'houseNumber' => $address->street_2,
                'postcode'    => $address->postal,
                'city'        => $address->city,
                'country'     => $address->country,
                'email'       => $address->email,
                'phone'       => $address->phone,
            ],
            'product'            => $this->billsafe_product,
            'sessionId'          => md5(session_id()),
            'articleList'        => $this->createArticleListArray($order),
        ];

        // Required not-default fields
        if ($user->gender) {
            $params['customer']['gender'] = $user->gender{0};
        } elseif ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['gender']) {
            $params['customer']['gender'] = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['gender']{0};
        }

        if ($user->dateOfBirth) {
            $params['customer']['dateOfBirth'] = date('Y-m-d', $user->dateOfBirth);
        } elseif ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['dateOfBirth']) {
            $params['customer']['dateOfBirth'] = date(
                'Y-m-d',
                strtotime($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['dateOfBirth'])
            );
        }

        $params['url_return'] = \Environment::get('base').Checkout::generateUrlForStep('complete', $order);
        $params['url_cancel'] = \Environment::get('base').Checkout::generateUrlForStep('failed');

        if (null !== ($objGatewayImage = \FilesModel::findByPk($this->billsafe_gatewayImage))) {
            $params['url_image'] = \Environment::get('base').\Image::get($objGatewayImage->path, 130, 60, 'box');
        }

        return $params;
    }


    /**
     * Create items array for call
     *
     * @param IsotopeProductCollection $order
     *
     * @return array
     */
    protected function createArticleListArray(IsotopeProductCollection $order)
    {
        $items = [];

        // Add each order's item
        foreach ($order->getItems() as $item) {
            // Set the active product for insert tags replacement
            if ($item->hasProduct()) {
                Product::setActive($item->getProduct());
            }

            $strConfig = '';
            $arrConfig = $item->getConfiguration();

            if (!empty($arrConfig)) {

                array_walk
                (
                    $arrConfig,
                    function (&$option) {
                        $option = $option['label'].': '.(string)$option;
                    }
                );

                $strConfig = ' ('.trim(implode(', ', $arrConfig)).')';
            }

            // Use the tax rate included in the tax class as BillSAFE requests the product's tax rate
            $taxRate = Database::getInstance()->prepare(
                "SELECT r.rate FROM tl_iso_tax_rate r LEFT JOIN tl_iso_tax_class c ON c.includes=r.id WHERE c.id=?"
            )->execute($item->tax_id);

            $items[] = [
                'number'     => strip_tags($item->getSku()),
                'name'       => strip_tags(
                    \StringUtil::restoreBasicEntities
                    (
                        $item->getName().$strConfig
                    )
                ),
                'type'       => 'goods',
//				'description' => strip_tags($objProduct->description),
                'quantity'   => $item->quantity,
                'grossPrice' => number_format($item->getPrice(), 2, '.', ''),
                'tax'        => number_format($taxRate->numRows ? deserialize($taxRate->rate)['value'] : 0, 2, '.', ''),
            ];
        }

        // Add the order's surcharges
        foreach ($order->getSurcharges() as $surcharge) {
            if (!$surcharge->addToTotal) {
                continue;
            }

            $items[] = [
                'number'     => ($surcharge->total_price > 0) ? 'shipment' : standardize($surcharge->label, true),
                'name'       => $surcharge->label,
                'type'       => ($surcharge->total_price > 0) ? 'shipment' : 'voucher',
                'quantity'   => 1,
                'grossPrice' => number_format($surcharge->total_price, 2, '.', ''),
                'tax'        => 0.00,
            ];
        }

        return $items;
    }


    /**
     * Load the up-to-date payment instruction
     *
     * @param IsotopeProductCollection|Order|\Model $order
     *
     * @return bool
     */
    public function updatePaymentInstruction(IsotopeProductCollection $order)
    {
        $params['transactionId'] = $order->billsafe_transactionId;
        $params['outputType'] = 'STRUCTURED';

        $request = $this->callMethod('getPaymentInstruction', $params);
        parse_str($request->response, $response);

        if ('OK' !== $response['ack']) {
            \System::log(
                'BillSAFE NVP: '.$response['errorList_0_code']." ".$response['errorList_0_message'],
                __METHOD__,
                TL_ERROR
            );

            return false;
        }

        $order->payment_data = $response;
        $order->save();

        return true;
    }


    /**
     * Invokes an API method on the BillSAFE server
     *
     * @param string $method
     * @param mixed  $parameter
     *
     * @return \Request
     */
    public function callMethod($method, $parameter)
    {
        if (!is_object($parameter) && !is_array($parameter)) {
            \System::log('Parameter must be an object or an array', __METHOD__, TL_ERROR);
        }

        $data = $this->_destructurize($parameter)
            .'method='.urlencode($method)
            .'&format=NVP'
            .'&merchant_id='.urlencode($this->billsafe_merchantId)
            .'&merchant_license='.urlencode($this->billsafe_merchantLicense)
            .'&application_signature='.urlencode($this->billsafe_applicationSignature)
            .'&application_version='.urlencode(APP_VERSION)
            .'&sdkSignature='.urlencode(SDK_SIGNATURE);

        $request = new \Request();
        $request->send($this->apiUrl, $this->_convertContentToString($data, true), 'POST');

        return $request;
    }


    /**
     * Convert $input to string
     *
     * @param        mixed
     * @param string $prefix
     *
     * @return string
     */
    private function _destructurize($input, $prefix = '')
    {
        if (is_bool($input)) {
            return urlencode($prefix).'='.($input ? 'TRUE' : 'FALSE')."&";
        } else {
            if (is_string($input)) {
                return urlencode($prefix).'='.urlencode($input)."&";
            } else {
                if (is_scalar($input)) {
                    return urlencode($prefix).'='.urlencode($input)."&";
                }
            }
        }

        if (is_object($input)) {
            $input = get_object_vars($input);
        }

        if (is_array($input)) {
            $returnString = '';

            foreach ($input as $key => $value) {
                $returnString .= $this->_destructurize($value, empty($prefix) ? $key : $prefix.'_'.$key);
            }

            return $returnString;
        }

        return '';
    }


    /**
     * Convert $content to string
     * When $blnIsRaw is set to false, content will be url encoded.
     *
     * @param mixed
     * @param boolean
     *
     * @return string
     */
    private function _convertContentToString($content, $blnIsRaw)
    {
        if (is_array($content)) {
            $tmp = [];

            foreach ($content as $key => $value) {
                $tmp[] = $blnIsRaw ? $key.'='.$value : urlencode($key).'='.urlencode($value);
            }

            $content = implode('&', $tmp);
        } else {
            $content = $blnIsRaw ? (string)$content : urlencode((string)$content);
        }

        return (string)$content;
    }


//	protected function getTaxRateForItem(ProductCollectionItem $objItem)
//	{
//		/** @noinspection PhpUndefinedMethodInspection */
//		$objTaxClass = $objItem->getRelated('tax_id');
//
//		$fltPrice = $objItem->getPrice();
//		$fltAmount = (float)0;
//
//		$arrAddresses = array(
//			'billing'  => Isotope::getCart()->getBillingAddress(),
//			'shipping' => Isotope::getCart()->getShippingAddress(),
//		);
//
//		/** @var \Isotope\Model\TaxRate $objIncludes */
//		/** @noinspection PhpUndefinedMethodInspection */
//		if (null !== ($objIncludes = $objTaxClass->getRelated('includes')) && !$objIncludes->isApplicable($fltPrice, $arrAddresses))
//		{
//			$fltPrice -= $objIncludes->calculateAmountIncludedInPrice($fltPrice);
//		}
//
//		/** @noinspection PhpUndefinedMethodInspection */
//		if (null !== ($objRates = $objTaxClass->getRelated('rates')))
//		{
//			/** @var \Isotope\Model\TaxRate $objTaxRate */
//			foreach ($objRates as $objTaxRate)
//			{
//				if ($objTaxRate->isApplicable($fltPrice, $arrAddresses))
//				{
//					$fltPrice += $objTaxRate->calculateAmountAddedToPrice($fltPrice);
//					$fltAmount = $objTaxRate->getAmount();
//
//					if ($objTaxRate->stop)
//					{
//						break;
//					}
//				}
//			}
//		}
//
//		return $fltAmount;
//
//	}
}
