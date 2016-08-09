<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register PSR-0 namespace
 */
if (class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::add('Isotope', 'system/modules/isotope_billsafe/src');
}


/**
 * Register the templates
 */
TemplateLoader::addFiles(
    [
        'iso_checkout_payment_billsafe' => 'system/modules/isotope_billsafe/templates',
    ]
);
