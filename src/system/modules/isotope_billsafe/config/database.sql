-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_iso_payment_modules`
--

CREATE TABLE `tl_iso_payment_modules` (
  `billsafe_merchantId` varchar(255) NOT NULL default '',
  `billsafe_merchantLicense` varchar(255) NOT NULL default '',
  `billsafe_applicationSignature` varchar(255) NOT NULL default '',
  `billsafe_publicKey` varchar(255) NOT NULL default '',
  `billsafe_product` varchar(255) NOT NULL default '',
  `billsafe_onsiteCheckout` char(1) NOT NULL default '',
  `billsafe_method` varchar(255) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Table `tl_iso_orders`
--

CREATE TABLE `tl_iso_orders` (
  `billsafe_token` varchar(255) NOT NULL default '',
  `billsafe_tc` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Table `tl_iso_orderstatus`
--

CREATE TABLE `tl_iso_orderstatus` (
  `shipped` char(1) NOT NULL default '',
  `cancelled` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
