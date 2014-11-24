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
 * Class PaymentBillSAFEHelper
 *
 * @copyright  Copyright (C) 2014 Richard Henkenjohann
 * @author     Richard Henkenjohann
 * @package    BillSAFE
 */

class PaymentBillSAFEHelper extends IsotopeOrder
{
	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('Isotope');
	}


	/**
	 * Inform BillSAFE about order status update
	 * @param object
	 * @param integer
	 * @param object
	 * @param bool
	 */
	public function processOrderStatusUpdate($objOrder, $intOldStatus, $objNewStatus, $blnActions)
	{
		$objPayment = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->execute($objOrder->payment_id);

		if ($objPayment->type != 'billsafe')
		{
			return;
		}

		// Construct class first
		$objPaymentBillSAFE = new PaymentBillSAFE($objPayment->row());
		$objPaymentBillSAFE->updateOrderStatusBillSAFE($objOrder, $intOldStatus, $objNewStatus, $blnActions);
	}


	/**
	 * Report invoice number to BillSAFE
	 * @param object
	 * @param array
	 * @param array
	 */
	public function setInvoiceNumber($objOrder, $arrItemIds, $arrData)
	{
		$objPayment = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->execute($objOrder->payment_id);

		if ($objPayment->type != 'billsafe')
		{
			return;
		}

		$arrParam = array
		(
			'orderNumber'   => $objOrder->id,
			'invoiceNumber' => $objOrder->order_id
		);

		// Construct class first
		$objPaymentBillSAFE = new PaymentBillSAFE($objPayment->row());
		$objRequest = $objPaymentBillSAFE->callMethod('setInvoiceNumber', $arrParam);

		parse_str($objRequest->response, $arrRequest);

		if ($arrRequest['ack'] == 'ERROR')
		{
			$this->log('BillSAFE NVP: ' . $arrRequest['errorList_0_code'] . " " . $arrRequest['errorList_0_message'], __METHOD__, TL_ERROR);
		}
	}


	/**
	 * New parameter for TemplateObject
	 */
	public function generateCollection(&$objTemplate, $arrItems, IsotopeProductCollection $objProductCollection)
	{
		if (!preg_match("/iso_invoice/", $objTemplate->getName()))
		{
			return;
		}

		$objPayment = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->execute($objProductCollection->payment_id);

		if ($objPayment->type != 'billsafe')
		{
			return;
		}

		// Update payment instruction
		$objPaymentBillSAFE = new PaymentBillSAFE($objPayment->row());
		$objPaymentBillSAFE->updatePaymentInstruction($objProductCollection->id);

		$arrInstruction = deserialize($this->Database->prepare("SELECT payment_data FROM tl_iso_orders WHERE id=?")->execute($objProductCollection->id)->payment_data, true);

		if (empty($arrInstruction))
		{
			$objTemplate->instruction_exists = false;
		}
		else
		{
			$objTemplate->instruction_exists = true;

			foreach ($arrInstruction as $k=>$v)
			{
				$objTemplate->$k = $v;
			}
		}
	}


	/**
	 * New Simple Tokens for Email Data
	 */
	public function getOrderEmailData(IsotopeOrder $objOrder, $arrData)
	{
		$arrData['instruction_exists'] = false;

		$objPayment = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->execute($objOrder->payment_id);

		if ($objPayment->type != 'billsafe')
		{
			return $arrData;
		}

		// Update payment instruction
		$objPaymentBillSAFE = new PaymentBillSAFE($objPayment->row());
		$objOrder = $objPaymentBillSAFE->updatePaymentInstruction($objOrder->id, $objOrder);

		$arrInstruction = $objOrder->payment_data;

		$arrData['instruction_exists'] = true;

		foreach ($arrInstruction as $k=>$v)
		{
			if ($k == 'instruction_amount')
			{
				$v = $this->Isotope->formatPriceWithCurrency($v, false);
			}

			$arrData[$k] = $v;
		}

		return $arrData;
	}
}
