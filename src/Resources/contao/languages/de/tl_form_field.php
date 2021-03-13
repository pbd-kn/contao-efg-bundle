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
 *  extended by insert_tag  {{efg_insert::formalias::aliasvalue::column(::format)}}
 *
 */

$GLOBALS['TL_LANG']['FFL']['efgLookupSelect'] = ['Select-Menü (DB)', 'ein ein- oder mehrzeiliges Drop-Down-Menü. Die Optionen werden aus einer definierbaren Datenbank-Tabelle gelesen.'];
$GLOBALS['TL_LANG']['FFL']['efgLookupCheckbox'] = ['Checkbox-Menü (DB)', 'eine Liste mehrerer Optionen, von denen beliebig viele ausgewählt werden können. Die Optionen werden aus einer definierbaren Datenbank-Tabelle gelesen.'];
$GLOBALS['TL_LANG']['FFL']['efgLookupRadio'] = ['Radio-Button-Menü (DB)', 'eine Liste mehrerer Optionen, von denen eine ausgewählt werden kann. Die Optionen werden aus einer definierbaren Datenbank-Tabelle gelesen.'];
$GLOBALS['TL_LANG']['FFL']['efgImageSelect'] = ['Bild-Auswahl-Menü', 'Ein Bild-Auswahl-Menü erzeugt im Formular eine Bilder-Galerie mit Radio-Buttons oder Checkboxen zur Auswahl von Bildern.'];
$GLOBALS['TL_LANG']['FFL']['efgFormPaginator'] = ['Absendefeld und Seitenumbruch', 'Schaltflächen und Seitenumbruch für mehrseitiges Formular.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgLookupOptions'] = 'Optionen aus Datenbank';
$GLOBALS['TL_LANG']['tl_form_field']['lookup_field'] = ['Datenbank-Feld (label)', 'Wählen Sie, aus welchem Datenbank-Feld die auswählbaren Optionen (sichtbarer Text bzw. label) gelesen werden sollen.'];
$GLOBALS['TL_LANG']['tl_form_field']['lookup_val_field'] = ['Datenbank-Feld (value)', 'Wählen Sie, aus welchem Datenbank-Feld die auswählbaren Optionen (Absendewert bzw. value) gelesen werden sollen.'];
$GLOBALS['TL_LANG']['tl_form_field']['lookup_where'] = ['Bedingung', 'Um bestimmte Datensätze auszuschließen, können Sie hier eine Bedingung eingeben (z.B. <em>published=\'1\'</em> oder <em>type!=\'admin\'</em>).'];
$GLOBALS['TL_LANG']['tl_form_field']['lookup_sort'] = ['Sortieren nach', 'Hier können Sie eine kommagetrennte Liste der Felder eingeben, nach denen die Ergebnisse sortiert werden sollen (z.B. <em>title ASC</em> oder <em>city ASC, gender DESC</em>). Wenn Sie keine Sortierung angeben, werden die Datensätze anhand des label-Feldes sortiert, oder anhand des Datums (bei Terminen, Quelltabelle tl_calendar_events).'];
$GLOBALS['TL_LANG']['tl_form_field']['efgMultiSRC'] = ['Quelldateien', 'Bitte wählen Sie alle Bilder und/oder Ordner, die in das Formularelement eingefügt werden sollen. Wenn Sie einen Ordner auswählen, werden alle darin enthaltenen Dateien automatisch eingefügt.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageUseHomeDir'] = ['Quelldateien aus Benutzerverzeichnis', 'Ist ein Benutzer angemeldet, werden anstelle der oben gewählten Quelldateien alle Bilder in seinem Benutzerverzeichnis in das Formularelement eingefügt.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageMultiple'] = ['Mehrfachauswahl', 'Erlaubt die Auswahl mehrerer Bilder im Formular.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageSortBy'] = ['Sortieren nach', 'Bitte wählen Sie eine Sortierreihenfolge.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageSize'] = ['Bildbreite und Bildhöhe', 'Geben Sie entweder die Bildbreite, die Bildhöhe oder beide Werte ein, um die Bildgröße anzupassen. Wenn Sie keine Angaben machen, wird das Bild in seiner Originalgröße angezeigt.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImagePerRow'] = ['Vorschaubilder pro Reihe', 'Bitte legen Sie fest, wie viele Vorschaubilder pro Reihe angezeigt werden sollen.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageMargin'] = ['Bildabstand', 'Bitte geben Sie den oberen, rechten, unteren und linken Bildabstand sowie die Einheit ein. Der Bildabstand ist der Abstand zwischen einem Bild und seinen benachbarten Elementen.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageFullsize'] = ['Großansicht', 'Wenn Sie diese Option wählen, öffnet sich bei Anklicken des Bildes dessen Großansicht.'];
$GLOBALS['TL_LANG']['tl_form_field']['name_asc'] = 'Dateiname (aufsteigend)';
$GLOBALS['TL_LANG']['tl_form_field']['name_desc'] = 'Dateiname (absteigend)';
$GLOBALS['TL_LANG']['tl_form_field']['date_asc'] = 'Datum (aufsteigend)';
$GLOBALS['TL_LANG']['tl_form_field']['date_desc'] = 'Datum (absteigend)';
$GLOBALS['TL_LANG']['tl_form_field']['meta'] = 'Meta Datei (meta.txt)';
$GLOBALS['TL_LANG']['tl_form_field']['random'] = 'Zufällige Reihenfolge';
$GLOBALS['TL_LANG']['tl_form_field']['efgAddBackButton'] = ['Zurück-Schaltfläche erstellen', 'Eine Schaltfläche erstellen, die zur vorherigen Formular-Seite führt.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgSwitchButtonOrder'] = ['Reihenfolge der Schaltflächen tauschen', 'Die Zurück-Schaltfläche nach bzw. rechts neben der Absende-Schaltfläche positionieren.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackStoreSessionValues'] = ['Formulareingaben bei \'Zurück\' erhalten', 'Eingaben im Frontend-Formular auch bei \'Zurück\' zwischenspeichern für die Vorbelegung bei erneutem Aufruf dieser Formular-Seite.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackSlabel'] = ['Bezeichnung der Zurück-Schaltfläche', 'Bitte geben Sie die Bezeichnung der Zurück-Schaltfläche ein.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackImageSubmit'] = $GLOBALS['TL_LANG']['tl_form_field']['imageSubmit'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackSingleSRC'] = $GLOBALS['TL_LANG']['tl_form_field']['singleSRC'];
$GLOBALS['TL_LANG']['tl_form_field']['backbutton_legend'] = 'Zurück-Schaltfläche';
