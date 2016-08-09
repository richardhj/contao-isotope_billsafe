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
$table = Isotope\Model\ProductCollection::getTable();


/**
 * Fields
 */
$GLOBALS['TL_DCA'][$table]['fields']['billsafe_transactionId']['sql'] = "varchar(50) NOT NULL default ''";
$GLOBALS['TL_DCA'][$table]['fields']['billsafe_tc']['sql'] = "text NULL";
$GLOBALS['TL_DCA'][$table]['fields']['billsafe_payoutStatus']['sql'] = "int(10) NOT NULL default '0'";
$GLOBALS['TL_DCA'][$table]['fields']['iso_billsafe_pause']['sql'] = "int(10) NOT NULL default '0'";
