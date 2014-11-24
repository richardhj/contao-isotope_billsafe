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
 * Handle BillSAFE payments
 */
class PaymentBillSAFE extends IsotopePayment
{
	private $apiUrlSandbox = 'https://sandbox-nvp.billsafe.de/V211';
	private $apiUrlLive = 'https://nvp.billsafe.de/V211';

	private $gatewayUrlSandbox = 'https://sandbox-payment.billsafe.de/V200';
	private $gatewayUrlLive = 'https://payment.billsafe.de/V200';


	/**
	 * processPayment function.
	 */
	public function processPayment()
	{
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->log('Order ID "' . $this->Input->get('orderID') . '" not found', __METHOD__, TL_ERROR);

			return false;
		}

		if ($this->billsafe_onsiteCheckout)
		{
			// Block second try
			if ($_SESSION['CHECKOUT_DATA']['payment']['status'] == 'declined')
			{
				$this->redirect($this->addToUrl('step=payment', true));
			}

			$objRequest = $this->callMethod('processOrder', $this->returnOrderRequestParam($objOrder, $this->Isotope->Cart->billingAddress));
		}
		else
		{
			$arrParam['token'] = $this->Input->get('token', true);

			$objRequest = $this->callMethod('getTransactionResult', $arrParam);
		}

		parse_str($objRequest->response, $arrRequest);

		if ($arrRequest['ack'] == 'ERROR')
		{
			// Handle invalid parameters
			if ($arrRequest['errorList_0_code'] == 216)
			{
				if (strpos($arrRequest['errorList_0_message'], 'housenumber') !== false)
				{
					$this->redirect($this->addToUrl('step=failed', true) . '?reason=' . urlencode($GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['housenumber']));
				}
				elseif (strpos($arrRequest['errorList_0_message'], 'dateOfBirth') !== false)
				{
					$this->redirect($this->addToUrl('step=failed', true) . '?reason=' . urlencode($GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['dateOfBirth']));
				}
			}

			$this->log('BillSAFE payment could not be processed. NVP: ' . $arrRequest['errorList_0_code'] . ' ' . $arrRequest['errorList_0_message'], __METHOD__, TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}
		elseif ($arrRequest['ack'] == 'OK')
		{
			if ($arrRequest['status'] == 'ACCEPTED')
			{
				if (!$this->billsafe_onsiteCheckout)
				{
					IsotopeFrontend::clearTimeout();
				}

				// Update order status
				$objOrder->updateOrderStatus($this->new_order_status);

				// Save payment instruction
				$arrParam = array();
				$arrParam['orderNumber'] = $objOrder->id;
				$arrParam['outputType'] = 'STRUCTURED';

				$objRequest = $this->callMethod('getPaymentInstruction', $arrParam);
				parse_str($objRequest->response, $arrPaymentInstruction);

				$objOrder->payment_data = $arrPaymentInstruction;

				// Save order
				$objOrder->save();

				return true;
			}
			else
			{
				if ($this->billsafe_onsiteCheckout)
				{
					$_SESSION['CHECKOUT_DATA']['payment']['status'] = 'declined';
					$_SESSION['CHECKOUT_DATA']['responseMsg'] = $arrRequest['declineReason_buyerMessage'];
				}

				$this->log('BillSAFE payment was declined. NVP: ' . $arrRequest['declineReason_code'] . ' ' . $arrRequest['declineReason_message'], __METHOD__, TL_ERROR);
				$this->redirect($this->addToUrl('step=failed', true) . '?reason=' . urlencode($arrRequest['declineReason_buyerMessage']));
			}
		}
		elseif (!$this->billsafe_onsiteCheckout && IsotopeFrontend::setTimeout())
		{
			global $objPage;
			$objPage->noSearch = 1;
			$objPage->cache = 0;

			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'processing';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
			return $objTemplate->parse();
		}

		$this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
		$this->redirect($this->addToUrl('step=failed', true));

		return false;
	}


	/**
	 * Return the BillSAFE form.
	 * @return string
	 */
	public function checkoutForm()
	{
		if ($this->billsafe_onsiteCheckout)
		{
			return false;
		}

		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->redirect($this->addToUrl('step=failed', true));
		}

		$objRequest = $this->callMethod('prepareOrder', $this->returnOrderRequestParam($objOrder, $this->Isotope->Cart->billingAddress));
		parse_str($objRequest->response, $arrRequest);

		if ($arrRequest['ack'] == 'ERROR')
		{
			$this->log('BillSAFE NVP: ' . $arrRequest['errorList_0_code'] . " " . $arrRequest['errorList_0_message'], __METHOD__, TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
			exit;
		}
		elseif ($arrRequest['ack'] == 'OK' && !$objOrder->billsafe_token)
		{
			$objOrder->billsafe_token = $arrRequest['token'];

			$this->Database->prepare("UPDATE tl_iso_orders SET billsafe_token=? WHERE id={$objOrder->id}")->executeUncached($objOrder->billsafe_token);
		}

		if ($objOrder->billsafe_token)
		{
			$gatewayUrl = ($this->debug) ? $this->gatewayUrlSandbox : $this->gatewayUrlLive;
			$this->redirect($gatewayUrl . "?token=" . $objOrder->billsafe_token);
		}

		$this->log('BillSAFE NVP: ack=' . $arrRequest['ack'] . " token=" . $arrRequest['token'], __METHOD__, TL_ERROR);
		$this->redirect($this->addToUrl('step=failed', true));
		exit;
	}


	/**
	 * Return a payment form
	 * @param object
	 * @return string
	 */
	public function paymentForm($objModule)
	{
		if ($this->billsafe_onsiteCheckout)
		{
			if ($_SESSION['CHECKOUT_DATA']['payment']['status'] == 'declined')
			{
				$objModule->doNotSubmit = true;

				return '<p class="error message">'. $_SESSION['CHECKOUT_DATA']['responseMsg'] . '</p>';
			}

			$strBuffer = '';
			$checkboxId = '';
			$objUser = FrontendUser::getInstance();
			$arrPayment = $this->Input->post('payment');
			$this->loadLanguageFile('tl_member');

			// Build form fields
			$arrFields = array
			(
				'billsafe_tc'	=> array
				(
					'label'				=> &$GLOBALS['TL_LANG']['ISO']['billsafe_tc'],
					'inputType'			=> 'checkbox',
					'options'           => array('accept'),
					'reference'         => &$GLOBALS['ISO_LANG']['billsafe']['tc'],
					'eval'				=> array('mandatory'=>true, 'required'=>true),
				),
			);

			if (!$objUser->gender)
			{
				$arrFields['gender'] = array
				(
					'label'                   => &$GLOBALS['TL_LANG']['tl_member']['gender'],
					'exclude'                 => true,
					'inputType'               => 'select',
					'options'                 => array('male', 'female'),
					'reference'               => &$GLOBALS['TL_LANG']['MSC'],
					'eval'                    => array('mandatory'=>true, 'required'=>true, 'includeBlankOption'=>true)
				);
			}

			if (!$objUser->dateOfBirth)
			{
				$arrFields['dateOfBirth'] = array
				(
					'label'                   => &$GLOBALS['TL_LANG']['tl_member']['dateOfBirth'],
					'exclude'                 => true,
					'inputType'               => 'text',
					'eval'                    => array('mandatory'=>true, 'required'=>true, 'rgxp'=>'date')
				);
			}

			foreach ($arrFields as $field => $arrData)
			{
				$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

				// Continue if the class is not defined
				if (!$this->classFileExists($strClass))
				{
					continue;
				}

				$objWidget = new $strClass($this->prepareForWidget($arrData, 'payment['.$this->id.']['.$field.']', $_SESSION['CHECKOUT_DATA']['payment'][$this->id][$field]));

				if ($field == 'billsafe_tc')
				{
					$checkboxId = $objWidget->id;
				}

				// Validate input
				if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && $arrPayment['module'] == $this->id)
				{
					$objWidget->validate();

					if ($objWidget->hasErrors())
					{
						$objModule->doNotSubmit = true;
					}
				}

				$strBuffer .= $objWidget->parse();
			}

			if ($this->Input->post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && !$objModule->doNotSubmit && $arrPayment['module'] == $this->id && !$_SESSION['CHECKOUT_DATA']['payment']['request_lockout'])
			{
				// Gather order data and set IsotopeOrder object
				$objOrder = new IsotopeOrder();

				if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
				{
					$objOrder->uniqid = uniqid($this->Isotope->Config->orderPrefix, true);
					$objOrder->cart_id = $this->Isotope->Cart->id;
					$objOrder->findBy('id', $objOrder->save());
				}

				$_SESSION['CHECKOUT_DATA']['payment']['request_lockout'] = true;

				if (FE_USER_LOGGED_IN)
				{
					if ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['gender'])
					{
						$objUser->gender = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['gender'];
					}

					if ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['dateOfBirth'])
					{
						$objUser->dateOfBirth = strtotime($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['dateOfBirth']);
					}

					$objUser->save();
				}

				// Agreement point 3.2
				$objOrder->billsafe_tc = array
				(
					'timestamp' => time(),
					'field_value' => $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['billsafe_tc']
				);

				$objOrder->save();

				unset($_SESSION['CHECKOUT_DATA']['responseMsg']);

				$objModule->doNotSubmit = false;
			}

			$strJsSnippet = '<script src="https://fn.billsafe.de/fb/js/lazyload-min.js"></script>
<script>
LazyLoad.js("https://fn.billsafe.de/fb/js/fb-min.js", function() {
runFb({
f: \''. md5(session_id()) . '\',
s: \'' . $this->billsafe_publicKey . '\',
e: \'opt_' . $checkboxId . '_0\'
})});
</script>
<noscript>
<img src="https://fn.billsafe.de/fb/f.png?f=' . md5(session_id()) . '&s=' . $this->billsafe_publicKey . '" />
</noscript>';

			return ($_SESSION['CHECKOUT_DATA']['responseMsg'] == '' ? '' : '<p class="error message">'. $_SESSION['CHECKOUT_DATA']['responseMsg'] . '</p>')
			. $strBuffer
			. $strJsSnippet;
		}

		return '';
	}


	/**
	 * Return information or advanced features in the backend
	 * Use this function to present advanced features or basic payment information for an order in the backend.
	 * @param integer
	 * @return string
	 */
	public function backendInterface($orderId)
	{
		$this->updatePaymentInstruction($orderId);

		$i = 0 ;
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', $orderId))
		{
			return parent::backendInterface($orderId);
		}

		$strBuffer = '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=payment', '', $this->Environment->request)) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['ISO_LANG']['PAY'][$this->type][0] . ')' . '</h2>

<div id="tl_soverview">
<div id="tl_messages">

</div>
</div>

<table class="tl_show">
<tbody>';

	foreach ($objOrder->payment_data as $k=>$v)
	{
		if (is_array($v))
		{
			continue;
		}

		$strBuffer .= '
<tr>
<td' . ($i%2 ? ' class="tl_bg"' : '') . '><span class="tl_label">' . $k . ': </span></td>
<td' . ($i%2 ? ' class="tl_bg"' : '') . '>' . $v . '</td>
</tr>';

		++$i;
	}

		$strBuffer .= '
</tbody></table>
</div>';

		return $strBuffer;
	}


	/**
	 * Load the up-to-date payment instruction
	 * @param int
	 * @param bool
	 * @return void|IsotopeOrder
	 */
	public function updatePaymentInstruction($orderId, $objOrder=false)
	{
		if ($objOrder === false)
		{
			$objOrder = new IsotopeOrder();

			if (!$objOrder->findBy('id', $orderId))
			{
				return;
			}
		}

		$arrParam['orderNumber'] = $objOrder->id;
		$arrParam['outputType'] = 'STRUCTURED';

		$objRequest = $this->callMethod('getPaymentInstruction', $arrParam);
		parse_str($objRequest->response, $arrPaymentInstruction);

		if ($arrPaymentInstruction['ack'] == 'OK')
		{
			$objOrder->payment_data = $arrPaymentInstruction;
			$objOrder->save();
		}
		else
		{
			$this->log('BillSAFE NVP: ' . $arrPaymentInstruction['errorList_0_code'] . " " . $arrPaymentInstruction['errorList_0_message'], __METHOD__, TL_ERROR);
		}

		if ($objOrder !== false)
		{
			return $objOrder;
		}
	}


	/**
	 * Inform BillSAFE about order status update (shipped/cancelled)
	 * @param object
	 * @param integer
	 * @param object
	 * @param bool
	 */
	public function updateOrderStatusBillSAFE($objOrder, $intOldStatus, $objNewStatus, $blnActions)
	{
		$objOldStatus = $this->Database->prepare("SELECT shipped FROM tl_iso_orderstatus WHERE id=?")->execute((int)$intOldStatus);

		// Shipment Report
		if ($objNewStatus->shipped && !$objOldStatus->shipped)
		{
			$shippingDate = (((time() - $objOrder->date_shipped) / 86400) > 5) ? time() : $objOrder->date_shipped;

			$arrParam = array
			(
				'orderNumber'   => $objOrder->id,
				'shippingDate'  => date('Y-m-d', $shippingDate)
			);

			$objRequest = $this->callMethod('reportShipment', $arrParam);
			parse_str($objRequest->response, $arrRequest);

			if ($arrRequest['ack'] == 'ERROR')
			{
				$this->log('BillSAFE NVP: ' . $arrRequest['errorList_0_code'] . " " . $arrRequest['errorList_0_message'], __METHOD__, TL_ERROR);
			}
			else
			{
				$this->Database->prepare("UPDATE tl_iso_orders SET date_shipped=? WHERE id=?")->execute($shippingDate, $objOrder->id);
				$this->log('New order status update reported to BillSAFE: shipment', __METHOD__, TL_ACCESS);
			}
		}
		// Report cancellation
		elseif ($objNewStatus->cancelled && !$objOldStatus->cancelled)
		{
			$arrParam = array
			(
				'orderNumber'           => $objOrder->id,
				'order_amount'          => 0.00,
				'order_taxAmount'       => 0.00,
				'order_currencyCode'    => $this->Isotope->Config->currency,
				'articleList'           => array()
			);

			$objRequest = $this->callMethod('updateArticleList', $arrParam);
			parse_str($objRequest->response, $arrRequest);

			if ($arrRequest['ack'] == 'ERROR')
			{
				$this->log('BillSAFE NVP: ' . $arrRequest['errorList_0_code'] . " " . $arrRequest['errorList_0_message'], __METHOD__, TL_ERROR);
			}
			else
			{
				$this->log('New order status update reported to BillSAFE: cancellation', __METHOD__, TL_ACCESS);
			}
		}
		// Revert shipment report
		elseif (!$objNewStatus->shipped && $objOldStatus->shipped)
		{
			$objRequest = $this->callMethod('revertReportShipment', array('orderNumber' => $objOrder->id));
			parse_str($objRequest->response, $arrRequest);

			if ($arrRequest['ack'] == 'ERROR')
			{
				$this->log('BillSAFE NVP: ' . $arrRequest['errorList_0_code'] . " " . $arrRequest['errorList_0_message'], __METHOD__, TL_ERROR);
			}
			else
			{
				$this->Database->prepare("UPDATE tl_iso_orders SET date_shipped=? WHERE id=?")->execute('', $objOrder->id);
				$this->log('New order status update reported to BillSAFE: shipment reverted', __METHOD__, TL_ACCESS);
			}
		}
	}


	/**
	 * Return the params for a BillSAFE order request
	 * @param object
	 * @param object
	 * @return array
	 */
	protected function returnOrderRequestParam($objOrder, $objAddress)
	{
		$order_taxAmount = (int)$objOrder->taxTotal > 0 ? $this->Isotope->Cart->grandTotal : 0;

		// Prevent empty email for members (address book)
		$objUser = FrontendUser::getInstance();
		$email = ($objAddress->email) ?: $objUser->email;

		$arrParam = array
		(
			'order_number'       => $objOrder->id,
			'order_amount'       => number_format($this->Isotope->Cart->grandTotal, 2, '.', ''),
			'order_taxAmount'    => number_format($order_taxAmount, 2, '.', ''),
			'order_currencyCode' => $this->Isotope->Config->currency,
			'customer'           => array
			(
				'id'          => $objUser->id,
				//'company'   => $objAddress->company, // B2B transactions are not allowed by default
				'firstname'   => $objAddress->firstname,
				'lastname'    => $objAddress->lastname,
				'street'      => $objAddress->street_1,
				'houseNumber' => $objAddress->street_2,
				'postcode'    => $objAddress->postal,
				'city'        => $objAddress->city,
				'country'     => $objAddress->country,
				'email'       => $email,
				'phone'       => $objAddress->phone,
			),
			'product'            => $this->billsafe_product,
			'sessionId'          => md5(session_id()),
			'articleList'        => $this->createArticleListArray(),
		);

		// Required not-default fields
		if ($objUser->gender)
		{
			$arrParam['customer']['gender'] = $objUser->gender{0};
		}
		elseif ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['gender'])
		{
			$arrParam['customer']['gender'] = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['gender']{0};
		}

		if ($objUser->dateOfBirth)
		{
			$arrParam['customer']['dateOfBirth'] = date('Y-m-d', $objUser->dateOfBirth);
		}
		elseif ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['dateOfBirth'])
		{
			$arrParam['customer']['dateOfBirth'] = date('Y-m-d', strtotime($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['dateOfBirth']));
		}

		if (!$this->billsafe_onsiteCheckout)
		{
			$arrParam['url_return'] = $this->Environment->base . $this->addToUrl('step=complete', true);
			$arrParam['url_cancel'] = $this->Environment->base . $this->addToUrl('step=failed', true);
			$arrParam['url_image']  = $this->Environment->base . '/' . $this->Isotope->Config->invoiceLogo;
		}

		return $arrParam;
	}


	/**
	 * Create items array for call
	 * @return array
	 */
	protected function createArticleListArray()
	{
		$arrItems = array();

		foreach ($this->Isotope->Cart->getProducts() as $objProduct)
		{
			$tax = 0.00;
			$objIncludes = $this->Database->prepare("SELECT r.* FROM tl_iso_tax_rate r LEFT JOIN tl_iso_tax_class c ON c.includes=r.id WHERE c.id=?")->execute($objProduct->tax_class);

			if ($objIncludes->numRows)
			{
				$arrTaxRate = deserialize($objIncludes->rate);
				$tax = (int)$arrTaxRate['value'] > 0 ? number_format($arrTaxRate['value'], 2, '.', '') : 0.00;
			}

			$arrItems[] = array
			(
				'number'      => strip_tags($objProduct->sku),
				'name'        => strip_tags($objProduct->name),
				'type'        => 'goods',
				'description' => strip_tags($objProduct->description),
				'quantity'    => $objProduct->quantity_requested,
				'grossPrice'  => number_format($objProduct->price, 2, '.', ''),
				'tax'         => $tax
			);
		}

		foreach ($this->Isotope->Cart->getSurcharges() as $arrSurcharge)
		{
			if ($arrSurcharge['add'] === false)
			{
				continue;
			}

			$type = 'shipment';
			$number = 'shipment';

			// Find coupons
			if ($arrSurcharge['total_price'] < 0)
			{
				$type = 'voucher';
				$number = standardize($arrSurcharge['label'], true);
			}

			$arrItems[] = array
			(
				'number'     => $number,
				'name'       => $arrSurcharge['label'],
				'type'       => $type,
				'quantity'   => 1,
				'grossPrice' => number_format($arrSurcharge['total_price'], 2, '.', ''),
				'tax'        => 0.00
			);
		}

		return $arrItems;
	}


	/**
	 * Invokes an API method on the BillSAFE server
	 * @param string
	 * @param mixed
	 * @return object
	 */
	public function callMethod($strMethodName, $parameter)
	{
		if (!is_object($parameter) && !is_array($parameter))
		{
			$this->log('Parameter must be an object or an array', __METHOD__, TL_ERROR);
		}

		$requestString = $this->_destructurize($parameter)
			. 'method=' . urlencode($strMethodName)
			. '&format=NVP'
			. '&merchant_id=' . urlencode($this->billsafe_merchantId)
			. '&merchant_license=' . urlencode($this->billsafe_merchantLicense)
			. '&application_signature=' . urlencode($this->billsafe_applicationSignature)
			. '&application_version=' . urlencode(APP_VERSION)
			. '&sdkSignature=' . urlencode(SDK_SIGNATURE);

		$objRequest = new Request();
		$objRequest->send(($this->debug) ? $this->apiUrlSandbox : $this->apiUrlLive, $this->_convertContentToString($requestString, true), $this->billsafe_method);

		return $objRequest;
	}


	/**
	 * Convert $input to string
	 * @param mixed
	 * @param string $prefix
	 * @return string
	 */
	private function _destructurize($input, $prefix = '')
	{
		if (is_bool($input))
		{
			return urlencode($prefix) . '=' . ($input ? 'TRUE' : 'FALSE') . "&";
		}
		else
		{
			if (is_string($input))
			{
				return urlencode($prefix) . '=' . urlencode($input) . "&";
			}
			else
			{
				if (is_scalar($input))
				{
					return urlencode($prefix) . '=' . urlencode($input) . "&";
				}
			}
		}

		if (is_object($input))
		{
			$input = get_object_vars($input);
		}

		if (is_array($input))
		{
			$returnString = '';

			foreach ($input as $key => $value)
			{
				$returnString .= $this->_destructurize($value, empty($prefix) ? $key : $prefix . '_' . $key);
			}

			return $returnString;
		}

		return '';
	}


	/**
	 * Convert $content to string
	 * When $blnIsRaw is set to false, content will be url encoded.
	 * @param mixed
	 * @param boolean
	 * @return string
	 */
	private function _convertContentToString($content, $blnIsRaw)
	{
		if (is_array($content))
		{
			$tmp = array();

			foreach ($content as $key => $value)
			{
				$tmp[] = $blnIsRaw ? $key . '=' . $value : urlencode($key) . '=' . urlencode($value);
			}

			$content = implode('&', $tmp);
		}
		else
		{
			$content = $blnIsRaw ? (string)$content : urlencode((string)$content);
		}

		return (string)$content;
	}
}
