<?php
/**
 * Isotope BillSAFE payment method for Contao Open Source CMS
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package Isotope
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */


namespace Isotope\CheckoutStep;

use Haste\Form\Form;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;


/**
 * Class PaymentBillSAFE
 * @package Isotope\CheckoutStep
 */
class PaymentBillSAFE extends CheckoutStep implements IsotopeCheckoutStep
{

	/**
	 * Haste form
	 * @var \Haste\Form\Form
	 */
	protected $objForm;

	/**
	 * Returns true if order conditions are defined
	 * @return  bool
	 */
	public function isAvailable()
	{
		//@todo billsafe available and onsite checkout enabled
		return false;
	}

	/**
	 * Generate the checkout step
	 * @return  string
	 */
	public function generate()
	{
		$this->objForm = new Form($this->objModule->getFormId(), 'POST', function ($objHaste)
		{
			/** @noinspection PhpUndefinedMethodInspection */
			return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
		});

		//@todo add form fields

		if ($this->objForm->isSubmitted())
		{
			$this->blnError = !$this->objForm->validate();

			$_SESSION['FORM_DATA'] = is_array($_SESSION['FORM_DATA']) ? $_SESSION['FORM_DATA'] : array();

			foreach (array_keys($this->objForm->getFormFields()) as $strField)
			{
				$varValue = $this->objForm->fetch($strField);

				$_SESSION['FORM_DATA'][$strField] = $varValue;
			}
		}
		else
		{
			$blnError = false;

			foreach (array_keys($this->objForm->getFormFields()) as $strField)
			{
				// Clone widget because otherwise we add errors to the original widget instance
				$objClone = clone $this->objForm->getWidget($strField);
				$objClone->validate();

				if ($objClone->hasErrors())
				{
					$blnError = true;
					break;
				}
			}

			$this->blnError = $blnError;
		}

		$objTemplate = new \Isotope\Template('iso_checkout_payment_billsafe');
		$objTemplate->form = $this->objForm->generate();

		return $objTemplate->parse();
	}


	/**
	 * Return review information for last page of checkout
	 * @return  string
	 */
	public function review()
	{
		return '';
	}


	/**
	 * Return array of tokens for notification
	 *
	 * @param IsotopeProductCollection $objCollection
	 *
	 * @return array
	 */
	public function getNotificationTokens(IsotopeProductCollection $objCollection)
	{
		$arrTokens = array();

		foreach ($this->objForm->getFormFields() as $strField => $arrConfig)
		{
			$varValue = null;

			if (isset($_SESSION['FORM_DATA'][$strField]))
			{
				$varValue = $_SESSION['FORM_DATA'][$strField];
			}

			if (null !== $varValue)
			{
				$arrTokens['form_' . $strField] = $varValue;
			}
		}

		return $arrTokens;
	}

	/**
	 * Return short name of current class (e.g. for CSS)
	 * @return string
	 */
//	public function getStepClass()
//	{
//		$strClass = get_parent_class($this);
//		$strClass = substr($strClass, strrpos($strClass, '\\') + 1);
//
//		return parent::getStepClass() . ' ' . standardize($strClass);
//	}
}
