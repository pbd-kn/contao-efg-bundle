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
$GLOBALS['TL_LANG']['MOD']['formdata'] = 'Form data';
$GLOBALS['TL_LANG']['MOD']['efg_co4'] = 'Form data';
$GLOBALS['TL_LANG']['MOD']['feedback'] = ['All results', 'Stored data from forms.'];
// Eintraege der Forms
$GLOBALS['TL_LANG']['MOD']['fd_mytestformular'] = ['MyTESTFORMULAR', 'Stored data from form "MyTESTFORMULAR".'];
$GLOBALS['TL_LANG']['MOD']['fd_zweites-formular'] = ['zweites Formular', 'Stored data from form "zweites Formular".'];
$GLOBALS['TL_LANG']['MOD']['fd_drittes'] = ['Drittes', 'Stored data from form "Drittes".'];

/*
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['formdatalisting'] = ['Listing form data', 'Use this module to list the records of a certain form data table in the front end.'];
