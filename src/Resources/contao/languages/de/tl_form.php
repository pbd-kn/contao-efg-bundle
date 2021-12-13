<?php

declare(strict_types=1);

/*
 *
 *  Contao Open Source CMS
 *
 *  Copyright (c) 2005-2014 Leo Feyer
 *
 *  @package   Efg
 *  @author    Thomas Kuhn <mail@th-kuhn.de>
 *  @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *  @copyright Thomas Kuhn 2007-2014
 *
 *
 *  Porting EFG to Contao 4
 *  Based on EFG Contao 3 from Thomas Kuhn
 *
 *  @package   contao-efg-bundle
 *  @author    Peter Broghammer <mail@pb-contao@gmx.de>
 *  @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *  @copyright Peter Broghammer 2021-
 *
 *  Thomas Kuhn's Efg package has been completely converted to contao 4.9
 *
 *  extended by insert_tag  {{efg_insert::formalias::aliasvalue::column(::format)}}
 *  extended using sendto by selection for sending Mail to additional receipients
 *
 */

$GLOBALS['TL_LANG']['tl_form']['storeFormdata'] = ['Daten im Modul "Formular-Daten" speichern', 'Wenn Sie diese Option wählen, werden die Daten im Backend-Modul "Formular-Daten" gespeichert.<br>Hinweis: Nach Ergänzung oder Änderung von Formular-Feldern bitte das Formular erneut speichern.'];
$GLOBALS['TL_LANG']['tl_form']['efgAliasField'] = ['Formularfeld für Alias', 'Wählen Sie hier das Formularfeld, dessen Inhalt zur Erzeugung des Formulardaten-Alias verwendet wird.'];
$GLOBALS['TL_LANG']['tl_form']['efgStoreValues'] = ['Options-Werte speichern', 'Wenn Sie diese Option wählen, wird bei Feldern des Typs Select-Menü, Radio-Button-Menü und Checkbox-Menü der ausgewählte "Wert" anstelle der "Bezeichnung" gespeichert. Die Option hat keine Auswirkung bei Feldern des Typs Select-Menü (DB), Radio-Button-Menü (DB) und Checkbox-Menu (DB)'];
$GLOBALS['TL_LANG']['tl_form']['useFormValues'] = ['Feldwerte exportieren', 'Wenn Sie diese Option wählen, werden beim Export der Formulardaten die ausgewählten Werte von Formularfeldern anstelle der ausgewählten Bezeichnungen exportiert. Dies trifft für alle Radio-Buttons, Checkboxen und Selects zu.'];
$GLOBALS['TL_LANG']['tl_form']['useFieldNames'] = ['Feldnamen exportieren', 'Wenn Sie diese Option wählen, werden beim Export der Formulardaten die Feldnamen anstelle der Feldbezeichnungen exportiert.'];
$GLOBALS['TL_LANG']['tl_form']['sendConfirmationMail'] = ['Bestätigung per E-Mail versenden', 'Wenn Sie diese Option wählen, wird eine Bestätigung per E-Mail an den Absender des Formulars versendet.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipientField'] = ['Formularfeld mit E-Mail-Adresse des Empfängers', 'Wählen Sie hier das Formularfeld, in dem der Absender seine E-Mail-Adresse angibt oder ein Formularfeld, das die Empfänger-Adresse als Wert enthält.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipient'] = ['Empfänger', 'Kommagetrennte Liste von E-Mail-Adressen, falls die E-Mail-Adresse nicht per Formularfeld definiert wird, oder die E-Mail an weitere Empfänger gesendet werden soll.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailSender'] = ['Absender', 'Bitte geben Sie hier die Absender-E-Mail-Adresse ein.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailReplyto'] = ['Antwort an (Reply-To)', 'Kommagetrennte Liste von E-Mail-Adressen, falls Antworten auf die E-Mail nicht an den Absender gesendet werden sollen.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailSubject'] = ['Betreff', 'Bitte geben Sie eine Betreffzeile für die Bestätigungs-E-Mail ein. Wenn Sie keine Betreffzeile erfassen, steigt die Wahrscheinlichkeit, dass die E-Mail als SPAM identifiziert wird.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailText'] = ['Text der Bestätigungs-E-Mail', 'Bitte geben Sie hier den Text der Bestätigungs-E-Mail ein. Neben den allgemeinen Insert-Tags werden Tags der Form form::FORMULARFELDNAME unterstützt.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailTemplate'] = ['HTML-Vorlage für die Bestätigungs-E-Mail', 'Wenn die Bestätigungs-E-Mail als HTML-E-Mail versendet werden soll, wählen Sie hier die HTML-Vorlage aus dem Dateisystem.'];
$GLOBALS['TL_LANG']['tl_form']['addConfirmationMailAttachments'] = ['Dateien an Bestätigungs-E-Mail anhängen', 'Der Bestätigungs-E-Mail können hier Dateien zum Versand angehängt werden.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailAttachments'] = ['Dateianhänge', 'Bitte wählen Sie hier die anzuhängenden Dateien aus.'];
$GLOBALS['TL_LANG']['tl_form']['addFormattedMailAttachments'] = ['Dateien an E-Mail anhängen', 'Der E-Mail können hier Dateien zum Versand angehängt werden.'];
$GLOBALS['TL_LANG']['tl_form']['formattedMailAttachments'] = ['Dateianhänge', 'Bitte wählen Sie hier die anzuhängenden Dateien aus.'];
$GLOBALS['TL_LANG']['tl_form']['sendFormattedMail'] = ['Per E-Mail versenden (formatierter Text / HTML)', 'Der Inhalt der Nachricht kann frei angegeben werden, unter Verwendung von Insert-Tags. Die Nachricht kann auch als HTML-E-Mail versendet werden.'];
$GLOBALS['TL_LANG']['tl_form']['formattedMailText'] = ['Text der E-Mail', 'Bitte geben Sie hier den Text der E-Mail ein. Neben den allgemeinen Insert-Tags werden Tags der Form form::FORMULARFELDNAME unterstützt.'];
$GLOBALS['TL_LANG']['tl_form']['formattedMailTemplate'] = ['HTML-Vorlage für die E-Mail', 'Wenn die E-Mail als HTML-E-Mail versendet werden soll, wählen Sie hier die HTML-Vorlage aus dem Dateisystem.'];
$GLOBALS['TL_LANG']['tl_form']['efgStoreFormdata_legend'] = '(EFG) Formular-Daten speichern';
$GLOBALS['TL_LANG']['tl_form']['efgSendFormattedMail_legend'] = '(EFG) Per E-Mail versenden';
$GLOBALS['TL_LANG']['tl_form']['efgSendConfirmationMail_legend'] = '(EFG) Bestätigung per E-Mail versenden';
$GLOBALS['TL_LANG']['tl_form']['efgDebugMode'] = ['EFG Debugmode', 'Set Debugmode'];
$GLOBALS['TL_LANG']['tl_form']['useSendto'] = ['Hidden Feld sendto auswerten', 'Unter dem Hiddenfeld sendto können zusätzliche Empfänger angegeben werden'];
