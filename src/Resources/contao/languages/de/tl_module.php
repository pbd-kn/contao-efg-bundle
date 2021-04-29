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

$GLOBALS['TL_LANG']['tl_module']['efgSearch_legend'] = 'Sucheinstellungen';
$GLOBALS['TL_LANG']['tl_module']['comments_legend'] = 'Kommentare';
$GLOBALS['TL_LANG']['tl_module']['list_formdata'] = ['Formular-Daten-Tabelle', 'Bitte wählen Sie die Formular-Daten-Tabelle, deren Datensätze Sie auflisten möchten.'];
$GLOBALS['TL_LANG']['tl_module']['efg_list_fields'] = ['Felder', 'Bitte wählen Sie die Felder, die Sie auflisten möchten.'];
$GLOBALS['TL_LANG']['tl_module']['efg_list_searchtype'] = ['Typ des Such-Formulars', 'Bitte wählen Sie, welchen Typ des Such-Formulars Sie verwenden möchten.'];
$GLOBALS['TL_LANG']['tl_module']['efg_list_search'] = ['Durchsuchbare Felder', 'Bitte wählen Sie die Felder, die im Frontend durchsuchbar sein sollen.'];
$GLOBALS['TL_LANG']['tl_module']['efg_list_info'] = ['Felder der Detailseite', 'Bitte wählen Sie die Felder, die Sie auf der Detailseite anzeigen möchten. Wählen Sie kein Feld, um die Detailansicht eines Datensatzes zu deaktivieren.'];
$GLOBALS['TL_LANG']['tl_module']['efg_iconfolder'] = ['Verzeichnis der Icons', 'Tragen Sie hier das Verzeichnis Ihrer Icons ein. Falls das Feld nicht ausgefüllt wird, werden die Icons im Verzeichnis "bundles/contaoefgco4/icons/" verwendet.'];
$GLOBALS['TL_LANG']['tl_module']['efg_fe_keep_id'] = ['Datensatz-ID beibehalten bei Frontendbearbeitung', 'Bei der Frontendbearbeitung wird normalerweise ein neuer Datensatz -somit neue ID- angelegt, anschließend der alte gelöscht. Wählen Sie diese Option, falls Sie auf eine unveränderte Datensatz-ID angewiesen sind.'];
$GLOBALS['TL_LANG']['tl_module']['efg_fe_no_formatted_mail'] = ['Kein Versand per E-Mail (formatierter Text / HTML) bei Frontend-Bearbeitung', 'Wählen Sie diese Option, wenn der im Formulargenerator eingestellte Versand per E-Mail (formatierter Text / HTML) bei Frontend-Bearbeitung nicht erfolgen soll.'];
$GLOBALS['TL_LANG']['tl_module']['efg_fe_no_confirmation_mail'] = ['Kein Versand der Bestätigung per E-Mail bei Frontend-Bearbeitung', 'Wählen Sie diese Option, wenn der im Formulargenerator eingestellte Versand einer Bestätigungs-Mail bei Frontend-Bearbeitung nicht erfolgen soll.'];
$GLOBALS['TL_LANG']['tl_module']['efg_list_access'] = ['Anzeige Einschränkung', 'Wählen Sie, welche Daten angezeigt werden dürfen.'];
$GLOBALS['TL_LANG']['tl_module']['efg_DetailsKey'] = ['URL-Fragment der Detailseite', 'Anstelle der Vorgabe "details" in der URL der Auflistungs-Detailseite können Sie hier einen abweichenden Begriff angeben.<br />Dadurch kann z.B. eine URL www.domain.tld/page/<b>info</b>/alias.html statt der Standard-URL www.domain.tld/page/<b>details</b>/alias.html erzeugt werden.'];
$GLOBALS['TL_LANG']['tl_module']['efg_fe_edit_access'] = ['Bearbeitung im Frontend', 'Wählen Sie, ob Daten im Frontend bearbeitet werden dürfen.'];
$GLOBALS['TL_LANG']['tl_module']['efg_fe_delete_access'] = ['Löschen im Frontend', 'Wählen Sie, ob Daten im Frontend gelöscht werden dürfen.'];
$GLOBALS['TL_LANG']['tl_module']['efg_fe_export_access'] = ['CSV-Export im Frontend', 'Wählen Sie, ob Daten im Frontend als CSV-Datei exportiert werden dürfen.'];
$GLOBALS['TL_LANG']['tl_module']['efg_com_allow_comments'] = ['Kommentare aktivieren', 'Besuchern das Kommentieren von Einträgen erlauben.'];
$GLOBALS['TL_LANG']['tl_module']['com_moderate'] = ['Kommentare moderieren', 'Kommentare erst nach Bestätigung auf der Webseite veröffentlichen.'];
$GLOBALS['TL_LANG']['tl_module']['com_bbcode'] = ['BBCode erlauben', 'Besuchern das Formatieren ihrer Kommentare mittels BBCode erlauben.'];
$GLOBALS['TL_LANG']['tl_module']['com_requireLogin'] = ['Login zum Kommentieren benötigt', 'Nur angemeldeten Benutzern das Erstellen von Kommentaren erlauben.'];
$GLOBALS['TL_LANG']['tl_module']['com_disableCaptcha'] = ['Sicherheitsfrage deaktivieren', 'Wählen Sie diese Option nur, wenn das Erstellen von Kommentaren auf authentifizierte Benutzer beschränkt ist.'];
$GLOBALS['TL_LANG']['tl_module']['efg_com_per_page'] = ['Kommentare pro Seite', 'Anzahl an Kommentaren pro Seite. Geben Sie 0 ein, um den automatischen Seitenumbruch zu deaktivieren.'];
$GLOBALS['TL_LANG']['tl_module']['com_order'] = ['Sortierung', 'Standardmäßig werden Kommentare aufsteigend sortiert, beginnend mit dem ältesten.'];
$GLOBALS['TL_LANG']['tl_module']['com_template'] = ['Kommentartemplate', 'Hier können Sie das Kommentartemplate auswählen.'];
$GLOBALS['TL_LANG']['tl_module']['efg_com_notify'] = ['Benachrichtigung an', 'Bitte legen Sie fest, wer beim Hinzufügen neuer Kommentare benachrichtigt wird.'];
$GLOBALS['TL_LANG']['tl_module']['notify_admin'] = 'Systemadministrator';
$GLOBALS['TL_LANG']['tl_module']['notify_author'] = 'Besitzer des Eintrags';
$GLOBALS['TL_LANG']['tl_module']['notify_both'] = 'Besitzer und Systemadministrator';
$GLOBALS['TL_LANG']['efg_list_searchtype']['none'] = ['Keine Suche', 'Kein Suchformular'];
$GLOBALS['TL_LANG']['efg_list_searchtype']['dropdown'] = ['Dropdown und Eingabefeld', 'Das Suchformular enthält ein DropDown zur Auswahl des zu durchsuchenden Feldes und ein Eingabefeld für den Suchbegriff.'];
$GLOBALS['TL_LANG']['efg_list_searchtype']['singlefield'] = ['Einzelnes Eingabefeld', 'Das Suchformular enthält ein einzelndes Eingabefeld für den Suchbegriff. Bei der Suche werden alle als durchsuchbare Felder definierten Felder berücksichtigt.'];
$GLOBALS['TL_LANG']['efg_list_searchtype']['multiplefields'] = ['Mehrere Eingabefelder', 'Das Suchformular enthält für jedes durchsuchbare Feld ein separates Eingabefeld für den Suchbegriff.'];
$GLOBALS['TL_LANG']['efg_list_access']['public'] = ['Öffentlich', 'Jeder Seitenbesucher darf alle Daten sehen.'];
$GLOBALS['TL_LANG']['efg_list_access']['member'] = ['Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten sehen.'];
$GLOBALS['TL_LANG']['efg_list_access']['groupmembers'] = ['Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder sehen.'];
$GLOBALS['TL_LANG']['efg_fe_edit_access']['none'] = ['Keine Bearbeitung', 'Daten können nicht im Frontend bearbeitet werden.'];
$GLOBALS['TL_LANG']['efg_fe_edit_access']['public'] = ['Öffentlich', 'Jeder Seitenbesucher darf alle Daten bearbeiten.'];
$GLOBALS['TL_LANG']['efg_fe_edit_access']['member'] = ['Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten bearbeiten.'];
$GLOBALS['TL_LANG']['efg_fe_edit_access']['groupmembers'] = ['Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder bearbeiten.'];
$GLOBALS['TL_LANG']['efg_fe_delete_access']['none'] = ['Kein Löschen', 'Daten können nicht im Frontend gelöscht werden.'];
$GLOBALS['TL_LANG']['efg_fe_delete_access']['public'] = ['Öffentlich', 'Jeder Seitenbesucher darf alle Daten löschen.'];
$GLOBALS['TL_LANG']['efg_fe_delete_access']['member'] = ['Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten löschen.'];
$GLOBALS['TL_LANG']['efg_fe_delete_access']['groupmembers'] = ['Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder löschen.'];
$GLOBALS['TL_LANG']['efg_fe_export_access']['none'] = ['Kein Export', 'Daten können nicht im Frontend exportiert werden.'];
$GLOBALS['TL_LANG']['efg_fe_export_access']['public'] = ['Öffentlich', 'Jeder Seitenbesucher darf alle Daten exportieren.'];
$GLOBALS['TL_LANG']['efg_fe_export_access']['member'] = ['Besitzer', 'Mitglieder dürfen nur ihre eigenen Daten exportieren.'];
$GLOBALS['TL_LANG']['efg_fe_export_access']['groupmembers'] = ['Gruppen-Mitglieder', 'Mitglieder dürfen ihre eigenen und die Daten ihrer Gruppen-Mitglieder exportieren.'];
