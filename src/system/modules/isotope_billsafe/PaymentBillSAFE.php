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

		if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
		{
			IsotopeFrontend::clearTimeout();
			return true;
		}

		$this->apiUrl = $this->debug ? $this->apiUrlSandbox : $this->apiUrlLive;
		$this->gatewayUrl = $this->debug ? $this->gatewayUrlSandbox : $this->gatewayUrlLive;

		$arrParam['token'] = $this->Input->get('token', true);

		$objRequest = $this->callMethod('getTransactionResult', $arrParam);
		parse_str($objRequest->response, $arrRequest);

		if ($arrRequest['ack'] == 'ERROR')
		{
			$this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
			$this->log('BillSAFE NVP: ' . $arrRequest['errorList_0_code'] . " " . $arrRequest['errorList_0_message'], __METHOD__, TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}
		elseif ($arrRequest['ack'] == 'OK')
		{
			if ($arrRequest['status'] == 'ACCEPTED')
			{
				IsotopeFrontend::clearTimeout();

				// Update order status
				$objOrder->date_paid = time();
				$objOrder->updateOrderStatus($this->new_order_status);

				// Save payment instruction
				$arrParam = array();
				$arrParam['orderNumber'] = $objOrder->id;
				$arrParam['outputType'] = 'STRUCTURED';

				$objRequest = $this->callMethod('getPaymentInstruction', $arrParam);
				parse_str($objRequest->response, $arrPaymentInstruction);

				$objOrder->payment_data = $arrPaymentInstruction;

				// Inform BillSAFE about invoice number
				$arrParam = array();
				$arrParam['orderNumber'] = $objOrder->id;
				$arrParam['invoiceNumber'] = $this->Isotope->Config->orderPrefix . $objOrder->order_id;

				$this->callMethod('setInvoiceNumber', $arrParam);

				// Save order
				$objOrder->save();

				return true;
			}
			else
			{
				$this->log('Payment was declined.', __METHOD__, TL_ERROR);
				$this->log('BillSAFE NVP: ' . $arrRequest['declineReason_code'] . " " . $arrRequest['declineReason_message'], __METHOD__, TL_ERROR);
				$this->redirect($this->addToUrl('step=failed', true) . '&reason=' . urlencode($arrRequest['declineReason_buyerMessage']));
			}
		}
		elseif (IsotopeFrontend::setTimeout())
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
	}


	/**
	 * Return the BillSAFE form.
	 * @return string
	 */
	public function checkoutForm()
	{
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->redirect($this->addToUrl('step=failed', true));
		}

		$this->apiUrl = $this->debug ? $this->apiUrlSandbox : $this->apiUrlLive;
		$this->gatewayUrl = $this->debug ? $this->gatewayUrlSandbox : $this->gatewayUrlLive;

		$objAddress = $this->Isotope->Cart->billingAddress;

		$order_taxAmount = (int)$objOrder->taxTotal > 0 ? $this->Isotope->Cart->grandTotal : 0;

		$arrParam = array
		(
			'order_number'       => $objOrder->id,
			'order_amount'       => number_format($this->Isotope->Cart->grandTotal, 2, '.', ''),
			'order_taxAmount'    => number_format($order_taxAmount, 2, '.', ''),
			'order_currencyCode' => $this->Isotope->Config->currency,
			'customer'           => array
			(
				'firstname'   => $objAddress->firstname,
				'lastname'    => $objAddress->lastname,
				'street'      => $objAddress->street_1,
				'houseNumber' => $objAddress->street_2,
				'postcode'    => $objAddress->postal,
				'city'        => $objAddress->city,
				'country'     => $objAddress->country,
				'email'       => $objAddress->email,
				'phone'       => $objAddress->phone
			),
			'product'            => $this->billsafe_product,
			'url_return'         => $this->Environment->base . $this->addToUrl('step=complete', true),
			'url_cancel'         => $this->Environment->base . $this->addToUrl('step=failed', true),
			'url_image'          => $this->Environment->base . '/' . $this->Isotope->Config->invoiceLogo,
			'sessionId'          => md5(session_id()),
			'articleList'        => $this->createItemsArray(),
		);

		$objRequest = $this->callMethod('prepareOrder', $arrParam);
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
			$this->redirect($this->gatewayUrl . "?token=" . $objOrder->billsafe_token);
		}

		$this->log('BillSAFE NVP: ack=' . $arrRequest['ack'] . " token=" . $arrRequest['token'], __METHOD__, TL_ERROR);
		$this->redirect($this->addToUrl('step=failed', true));
		exit;
	}


	/**
	 * Return information or advanced features in the backend
	 * Use this function to present advanced features or basic payment information for an order in the backend.
	 * @param integer
	 * @return string
	 */
	public function backendInterface($orderId)
	{
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
	 * Inform BillSAFE about shipment
	 * @param object
	 * @param integer
	 * @param object
	 * @param bool
	 */
	public function reportShipment($objOrder, $intOldStatus, $objNewStatus, $blnActions)
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
			}
		}
	}


	/**
	 * Create items array for call
	 * @return array
	 */
	protected function createItemsArray()
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
				'tax'         => $tax,
			);
		}

		foreach ($this->Isotope->Cart->getSurcharges() as $arrSurcharge)
		{
			if ($arrSurcharge['add'] === false)
			{
				continue;
			}

			$arrItems[] = array
			(
				'number'     => 'shipment',
				'name'       => $arrSurcharge['label'],
				'type'       => 'shipment',
				'quantity'   => 1,
				'grossPrice' => number_format($arrSurcharge['total_price'], 2, '.', ''),
				'tax'        => 0.00,
			);
		}

		return $arrItems;
	}


	/**
	 * Invokes an API method on the BillSAFE server
	 * @param string
	 * @param mixed
	 * @return object
	 * @todo transfer apiUrl
	 */
	public function callMethod($strMethodName, $parameter)
	{
		if (!is_object($parameter) && !is_array($parameter))
		{
			$this->log('Parameter must be an object or an array', __METHOD__, TL_ERROR);
		}

		if (!$this->apiUrl)
		{
			$this->apiUrl = $this->apiUrlLive;
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
		$objRequest->send($this->apiUrl, $this->_convertContentToString($requestString, true), $this->billsafe_method);

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
