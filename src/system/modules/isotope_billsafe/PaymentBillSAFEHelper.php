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
	 * Inform BillSAFE about shipment
	 * @param object
	 * @param integer
	 * @param object
	 * @param bool
	 * @todo find better solution
	 */
	public function reportShipment($objOrder, $intOldStatus, $objNewStatus, $blnActions)
	{
		$objPayment = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->execute($objOrder->payment_id);

		if ($objPayment->type != 'billsafe')
		{
			return;
		}

		// Construct class first
		$objPaymentBillSAFE = new PaymentBillSAFE($objPayment->row());
		$objPaymentBillSAFE->reportShipment($objOrder, $intOldStatus, $objNewStatus, $blnActions);
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

		$arrInstruction = $objProductCollection->payment_data;

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
