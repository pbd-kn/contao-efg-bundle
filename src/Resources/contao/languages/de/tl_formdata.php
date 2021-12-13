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

$GLOBALS['TL_LANG']['tl_formdata']['form'] = ['Formular', 'Daten aus Formular'];
$GLOBALS['TL_LANG']['tl_formdata']['date'] = ['Datum', 'Datum des Eintrags'];
$GLOBALS['TL_LANG']['tl_formdata']['ip'] = ['IP-Adresse', 'IP-Adresse des Absenders'];
$GLOBALS['TL_LANG']['tl_formdata']['be_notes'] = ['Bemerkung', 'Bemerkungen, Bearbeitungshinweise etc.'];
$GLOBALS['TL_LANG']['tl_formdata']['import_source'] = ['Quelldatei', 'Bitte wählen Sie die zu importierende CSV-Datei aus der Dateiübersicht.'];
$GLOBALS['TL_LANG']['tl_formdata']['import_preview'] = ['Vorschau und Feldzuordnung', 'Bitte wählen Sie, in welche Formulardaten-Felder die Spalten der CSV-Datei importiert werden sollen. Die nachfolgende Tabelle zeigt maximal 50 Zeilen der CSV-Datei.'];
$GLOBALS['TL_LANG']['tl_formdata']['csv_has_header'] = ['Datei mit Spaltenbeschriftungen', 'Die Datei enthält in der ersten Zeile Feld-/Spaltenbeschriftungen.'];
$GLOBALS['TL_LANG']['tl_formdata']['option_import_ignore'] = '-nicht importieren-';
$GLOBALS['TL_LANG']['tl_formdata']['published'] = ['Veröffentlicht', 'Dieses Kennzeichen kann als Einschränkung bei Verwendung einer Auflistung verwendet werden.'];
$GLOBALS['TL_LANG']['tl_formdata']['sorting'] = ['Sortierindex', 'Der Sortierindex kann bei einer Auflistung verwendet werden.'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_member'] = ['Mitglied', 'Mitglied als Besitzer des Datensatzes'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_user'] = ['Benutzer', 'Benutzer als Besitzer des Datensatzes'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_member_group'] = ['Mitgliedergruppe', 'Mitgliedergruppe als Besitzer des Datensatzes'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_user_group'] = ['Benutzergruppe', 'Benutzergruppe als Besitzer des Datensatzes'];
$GLOBALS['TL_LANG']['tl_formdata']['alias'] = ['Alias', 'Der Alias eines Eintrags ist eine eindeutige Referenz, die anstelle der Eintrags-ID aufgerufen werden kann.'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_sender'] = ['Absender', 'E-Mail-Adresse des Absenders'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_recipient'] = ['Empfänger', 'E-Mail-Adresse des Empfängers'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_subject'] = ['Betreff', 'Betreffzeile der E-Mail'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_body_plaintext'] = ['Nachricht (Plain Text)', 'Text der E-Mail im Text-Format'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_body_html'] = ['Nachricht (HTML)', 'Text der E-Mail im HTML-Format'];
$GLOBALS['TL_LANG']['tl_formdata']['attachments'] = 'Datei-Anhang';
$GLOBALS['TL_LANG']['tl_formdata']['new'] = ['Neuer Eintrag', 'Einen neuen Eintrag erstellen'];
$GLOBALS['TL_LANG']['tl_formdata']['edit'] = ['Eintrag bearbeiten', 'Eintrag ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_formdata']['copy'] = ['Eintrag duplizieren', 'Eintrag ID %s duplizieren'];
$GLOBALS['TL_LANG']['tl_formdata']['delete'] = ['Eintrag löschen', 'Eintrag ID %s löschen'];
$GLOBALS['TL_LANG']['tl_formdata']['show'] = ['Eintrag anzeigen', 'Details des Eintrags ID %s anzeigen'];
$GLOBALS['TL_LANG']['tl_formdata']['mail'] = ['Bestätigungs-Mail versenden', 'Bestätigungs-Mail für Eintrag ID %s versenden'];
$GLOBALS['TL_LANG']['tl_formdata']['import'] = ['CSV-Import', 'Daten aus einer CSV-Datei importieren'];
$GLOBALS['TL_LANG']['tl_formdata']['export'] = ['CSV-Export', 'Daten als CSV-Datei exportieren'];
$GLOBALS['TL_LANG']['tl_formdata']['exportxls'] = ['Excel Export', 'Daten als MS-Excel-Datei exportieren'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_sent'] = 'Mail-Versand erfolgt an %s';
$GLOBALS['TL_LANG']['tl_formdata']['confirmation_sent'] = 'Für diesen Eintrag wurde bereits eine Bestätigungs-Mail gesendet am %s um %s';
$GLOBALS['TL_LANG']['tl_formdata']['confirmationSent'] = ['Bestätigungs-Mail gesendet', 'Eine Bestätigungs-Mail für diesen Eintrag wurde gesendet'];
$GLOBALS['TL_LANG']['tl_formdata']['confirmationDate'] = ['Bestätigungs-Mail gesendet am', 'Der Zeitpunkt des Versands der Bestätigungs-Mail'];
$GLOBALS['TL_LANG']['tl_formdata']['import_confirm'] = '%s neue Einträge wurden importiert.';
$GLOBALS['TL_LANG']['tl_formdata']['import_invalid'] = '%s  ungültige Einträge wurden übersprungen.';
$GLOBALS['TL_LANG']['tl_formdata']['error_select_source'] = 'Bitte wählen Sie eine Quelldatei!';
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_details'] = ['Details', 'Details anzeigen'];
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_edit'] = ['Bearbeiten', 'Datensatz bearbeiten'];
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_delete'] = ['Löschen', 'Datensatz löschen'];
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_export'] = ['CSV Export', 'Daten als CSV-Datei exportieren'];
$GLOBALS['TL_LANG']['tl_formdata']['fe_deleteConfirm'] = 'Soll der Eintrag wirklich gelöscht werden?';
$GLOBALS['TL_LANG']['tl_formdata']['confirmation_legend'] = 'Bestätigungs-Mail';
$GLOBALS['TL_LANG']['tl_formdata']['fdNotes_legend'] = 'Bemerkung';
$GLOBALS['TL_LANG']['tl_formdata']['fdOwner_legend'] = 'Besitzer';
$GLOBALS['TL_LANG']['tl_formdata']['fdDetails_legend'] = 'Details';
