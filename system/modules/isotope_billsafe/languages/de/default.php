<?php
/**
 * Isotope BillSAFE payment method for Contao Open Source CMS
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package Isotope
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */


/**
 * Payment modules
 */
$GLOBALS['ISO_LANG']['PAY']['billsafe'] = [
    'BillSAFE',
    'Dieses Modul unterstützt "Name-Value Pair" (NVP).',
];

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['pay_with_billsafe'] = [
    'Bezahlen mit BillSAFE',
    'Sie werden nun an BillSAFE zur Bezahlung Ihrere Bestellung weitergeleitet. Wenn Sie nicht sofort weitergeleitet werden, klicken Sie bitte auf "Jetzt bezahlen".',
    'Jetzt bezahlen',
];

$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['housenumber'] = 'Bitte überprüfen Sie Ihre Adresse auf eine fehlende Hausnummer.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['postcode'] = 'Bitte überprüfen Sie Ihre Adresse auf eine fehlerhafte Postleitzahl.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['dateOfBirth'] = 'Bitte überprüfen Sie Ihr Geburtsdatum auf Vollständigkeit und Richtigkeit.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['default'] = 'Es ist ein Fehler mit der Verarbeitung Ihrer Daten aufgetreten. Das Feld "%s" konnte nicht verarbeitet werden. Versuchen Sie es nach der Berichtigung nochmals.';

$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['215']['postcode'] = sprintf(
    $GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['215']['sprintf'],
    'Postleitzahl'
);
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['215']['sprintf'] = 'Die Eingabe von "%s" fehlt.';
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['216']['postcode'] = sprintf(
    $GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['216']['sprintf'],
    'Postleitzahl'
);
$GLOBALS['TL_LANG']['MSC']['billsafe_nvp_error']['216']['sprintf'] = 'Die Eingabe von "%s" ist fehlerhaft.';


$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['billing_address']['legend'] = 'Rechnungsadresse';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['payment_data']['legend'] = 'BillSAFE-Variablen';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['legend'] = 'Warenkorb';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['info'] = 'Änderungen (Retouren, Gutschriften) im Warenkorb könnnen Sie hier ändern. Dabei ändern sich auch die Forderungen von BillSAFE an den Kunden.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['message']['confirm'] = 'Es wurde eine aktualisierte Warenkorb-Liste bei BillSAFE hinterlegt.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['message']['error'] = 'Bei der Ausführung ist ein Fehler aufgetreten. Siehe System-Log.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList'][0] = 'Artikelliste';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_number'][0] = 'Artikelnummer';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_name'][0] = 'Bezeichnung';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_description'][0] = 'Beschr.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_description'][1] = 'Dieses Feld beinhaltet die Beschreibung des Artikels.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_type'][0] = 'Typ';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_type_options'] = array(
    'goods' => 'Artikel',
    'shipment' => 'VersKosten',
    'voucher' => 'Rabatt',
);
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_quantity'][0] = 'Anz.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_quantity'][1] = 'Der Inhalt dieses Feldes ergibt sich aus der Anzahl bestellter Exemplare abzüglich der Anzahl retournierter Exemplare abzüglich der Anzahl stornierter Exemplare.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_qrossPrice'][0] = 'Preis';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_qrossPrice'][1] = 'Geben Sie den Bruttopreis inkl. Umsatzsteuer an.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_tax'][0] = 'Steuer';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_tax'][1] = 'Dieses Feld beinhaltet den Umsatzsteuersatz in Prozent an.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_quantityShipped'][0] = 'vers.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['articleList_quantityShipped'][1] = 'Geben Sie die Anzahl der versendeten Artikel an. Das Feld muss die Anzahl der Artikelexemplare enthalten, die sich aktuell beim Käufer oder auf dem Weg dorthin befinden.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['submit'] = 'Aktualisieren';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['updateArticleList']['fields']['submit_confirm'] = 'Wollen Sie wirklich die neue Aritkelliste (ggf. mit Forderungsänderungen) an BillSAFE übermitteln?';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['legend'] = 'Rechnungswesen pausieren';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['info'] = 'Pausieren Sie mit dieser Funktion das Rechnungswesen um maximal 10 Tage, wenn der Kunde bspw. Retouren ankündigt.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['message']['info'] = 'Die Transaktion ist aktuell bis zum %s gestundet.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['message']['error'] = 'Bei der Ausführung ist ein Fehler aufgetreten. Siehe System-Log.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['fields']['pause'][0] = 'Anzahl Tage';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['pauseTransaction']['fields']['submit'] = 'Pausieren';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['legend'] = 'Direktzahlung melden';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['info'] = 'Melden Sie Zahlungen, wenn der Kunde versehentlich auf Ihr Konto überweist.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['message']['error'] = 'Bei der Ausführung ist ein Fehler aufgetreten. Siehe System-Log.';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['list_directPayment'] = 'Betrag: %s %s eingegangen am %s';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['fields']['amount'][0] = 'Betrag';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['fields']['date'][0] = 'eingegangen am';
$GLOBALS['TL_LANG']['MSC']['billsafe_backendInterface']['reportDirectPayment']['fields']['submit'] = 'Melden';

$GLOBALS['ISO_LANG']['billsafe']['product_types']['invoice'] = 'Rechnung';
$GLOBALS['ISO_LANG']['billsafe']['product_types']['installment'] = 'Ratenkauf';

$GLOBALS['ISO_LANG']['billsafe']['tc']['accept'] = 'Ich stimme den <href="https://www.billsafe.de/privacy-policy/buyer">Datenschutzgrundsätzen</a> und der <a href="https://www.billsafe.de/privacy-policy/credit-check">Bonitätsprüfung</a> von <a href="https://www.billsafe.de/imprint">PayPal</a> zu. Es gelten die <a href="https://www.billsafe.de/resources/docs/pdf/Kaeufer_AGB.pdf">Allgemeinen Nutzungsbedingungen</a> für den Rechnungskauf.';
$GLOBALS['ISO_LANG']['billsafe']['tc']['error_missing'] = 'Bitte füllen Sie alle Pflichtfelder aus und klicken auf "Weiter".';
