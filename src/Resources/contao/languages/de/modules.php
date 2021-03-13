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

// This file is created when saving a form in form generator
// last created on 2021-03-11 16:22:01

/*
 * Back end modules
 */
$GLOBALS['TL_LANG']['MOD']['formdata'] = 'Formular-Daten';
$GLOBALS['TL_LANG']['MOD']['efg_co4'] = 'Formular-Daten';
$GLOBALS['TL_LANG']['MOD']['feedback'] = ['Feedback', 'Gespeicherte Daten aus Formularen.'];
// Eintraege der Forms
$GLOBALS['TL_LANG']['MOD']['fd_mytestformular'] = ['MyTESTFORMULAR', 'Gespeicherte Daten aus Formular "MyTESTFORMULAR".'];
$GLOBALS['TL_LANG']['MOD']['fd_zweites-formular'] = ['zweites Formular', 'Gespeicherte Daten aus Formular "zweites Formular".'];
$GLOBALS['TL_LANG']['MOD']['fd_drittes'] = ['Drittes', 'Gespeicherte Daten aus Formular "Drittes".'];

/*
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['formdatalisting'] = ['Auflistung Formular-Daten', 'Verwenden Sie dieses Modul dazu, die Daten einer beliebigen Formular-Daten-Tabelle im Frontend aufzulisten.'];
