<?php

// begin config efg

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   Efg
 * @author    Thomas Kuhn <mail@th-kuhn.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Thomas Kuhn 2007-2014
 *
 * Porting EFG to Contao 4
 * Based on EFG Contao 3 from Thomas Kuhn 
 *
 * @package   contao-efg-bundle
 * @author    Peter Broghammer <mail@pb-contao@gmx.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Peter Broghammer 2021-
 *
 * Thomas Kuhn's Efg package has been completely converted to contao 4.9 
 * extended by insert_tag  {{efg_insert::formalias::aliasvalue::column(::format)}}
 */


// This file is created when saving a form in form generator
// last created on 2021-03-25 15:36:04
/*
 * you can set the swiftmail transport set in efg_internal_config.html
 * example define('SENDMAILCOMMAND', ini_get ('sendmail_path') . ' -t')
*/

define('SENDMAILCOMMAND', ini_get ('sendmail_path') . ' -t');    // set mailtransport for Swiftmailer
define('debsmall',1);
define('debmedium',2+debsmall);
define('debfull',4+debmedium);
define('debmailsmall',8);
define('debmailmedium',16+debmailsmall);
define('debmailfull',32+debmailmedium);


/**
* to fix height of style class w50 in backend
*/
if (TL_MODE == 'BE')
{
	$GLOBALS['TL_CSS'][] = 'PBDKN/Efgco4/Resources/assets/w50_fix.css';
}


/**
 * Use class ExtendedForm
 */
$GLOBALS['FE_MOD']['application']['form'] = 'PBDKN\Efgco4\Resources\contao\forms\ExtendedForm';
$GLOBALS['TL_CTE']['includes']['form'] = 'PBDKN\Efgco4\Resources\contao\forms\ExtendedForm';


/**
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 */

array_insert($GLOBALS['BE_MOD'], 1, array('formdata' => array()));

// this is used for the form independent "Feedback" module
$GLOBALS['BE_MOD']['formdata']['feedback'] = array
(
	'tables'     => array('tl_formdata', 'tl_formdata_details'),
	'icon'       => 'PBDKN/Efgco4/Resources/contao/assets/formdata_all.gif',
	'stylesheet' => 'PBDKN/Efgco4/Resources/contao/assets/style.css'
);

// following are used for the form dependent modules
$GLOBALS['BE_MOD']['formdata']['fd_neuesformular'] = array
(
	'tables'     => array('tl_formdata', 'tl_formdata_details'),
	'import'     => array('FormdataBackend', 'importCsv'),
	'icon'       => 'PBDKN/Efgco4/Resources/contao/assets/formdata_all.gif',
	'stylesheet' => 'PBDKN/Efgco4/Resources/contao/assets/style.css'
);
$GLOBALS['BE_MOD']['formdata']['fd_drittes-hilfsformular'] = array
(
	'tables'     => array('tl_formdata', 'tl_formdata_details'),
	'import'     => array('FormdataBackend', 'importCsv'),
	'icon'       => 'PBDKN/Efgco4/Resources/contao/assets/formdata_all.gif',
	'stylesheet' => 'PBDKN/Efgco4/Resources/contao/assets/style.css'
);
$GLOBALS['BE_MOD']['formdata']['fd_sechstes-hilfsformular'] = array
(
	'tables'     => array('tl_formdata', 'tl_formdata_details'),
	'import'     => array('FormdataBackend', 'importCsv'),
	'icon'       => 'PBDKN/Efgco4/Resources/contao/assets/formdata_all.gif',
	'stylesheet' => 'PBDKN/Efgco4/Resources/contao/assets/style.css'
);


/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 */

array_insert($GLOBALS['FE_MOD']['application'], count($GLOBALS['FE_MOD']['application']), array
(
	'formdatalisting' => 'PBDKN\Efgco4\Resources\contao\modules\ModuleFormdataListing'
));


/**
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 */

$GLOBALS['TL_HOOKS']['processFormData'][] = array('PBDKN\Efgco4\Resources\contao\classes\FormdataProcessor', 'processSubmittedData');
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('PBDKN\Efgco4\Resources\contao\classes\FormdataProcessor', 'processConfirmationContent');
$GLOBALS['TL_HOOKS']['listComments'][] = array('PBDKN\Efgco4\Resources\classes\contao\FormdataComments', 'listComments');
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('PBDKN\Efgco4\Resources\contao\classes\Formdata', 'getSearchablePages');
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('PBDKN\Efgco4\Resources\contao\classes\Formdata', 'executePostActions');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('PBDKN\Efgco4\Resources\contao\classes\EfgInsertTag', 'Efg_InsertTags');

// end config efg

