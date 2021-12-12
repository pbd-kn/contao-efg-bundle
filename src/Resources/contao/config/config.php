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

/*
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
// last created on 2021-12-11 15:30:25

\define('debsmall', 1);
\define('debmedium', 2 + debsmall);
\define('debfull', 4 + debmedium);
\define('debmailsmall', 8);
\define('debmailmedium', 16 + debmailsmall);
\define('debmailfull', 32 + debmailmedium);

/*
* to fix height of style class w50 in backend
*/
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/contaoefgco4/css/w50_fix.css';
}

/*
 * Use class ExtendedForm
 */
//$GLOBALS['FE_MOD']['application']['form'] = 'PBDKN\Efgco4\Resources\contao\forms\ExtendedForm';
//$GLOBALS['TL_CTE']['includes']['form'] = 'PBDKN\Efgco4\Resources\contao\forms\ExtendedForm';

/*
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 */

/*
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 */

array_insert($GLOBALS['FE_MOD']['application'], \count($GLOBALS['FE_MOD']['application']), [
    'formdatalisting' => 'PBDKN\Efgco4\Resources\contao\modules\ModuleFormdataListing',
]);

// Add backend form fields
$GLOBALS['BE_FFL']['efgLookupOptionWizard'] = 'PBDKN\Efgco4\Resources\contao\widgets\EfgLookupOptionWizard';
$GLOBALS['BE_FFL']['efgLookupSelect'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormLookupSelectMenu';
$GLOBALS['BE_FFL']['efgLookupCheckbox'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormLookupCheckbox';
$GLOBALS['BE_FFL']['efgLookupRadio'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormLookupRadio';
$GLOBALS['BE_FFL']['efgFormPaginator'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormPaginator';

// Add front end form fields
$GLOBALS['TL_FFL']['efgLookupSelect'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormLookupSelectMenu';
$GLOBALS['TL_FFL']['efgLookupCheckbox'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormLookupCheckbox';
$GLOBALS['TL_FFL']['efgLookupRadio'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormLookupRadio';
$GLOBALS['TL_FFL']['efgImageSelect'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormImageSelect';
$GLOBALS['TL_FFL']['efgFormPaginator'] = 'PBDKN\Efgco4\Resources\contao\forms\EfgFormPaginator';

/*
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 */

$GLOBALS['TL_HOOKS']['processFormData'][] = ['PBDKN\Efgco4\Resources\contao\classes\FormdataProcessor', 'processSubmittedData'];
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = ['PBDKN\Efgco4\Resources\contao\classes\FormdataProcessor', 'processConfirmationContent'];
$GLOBALS['TL_HOOKS']['listComments'][] = ['PBDKN\Efgco4\Resources\classes\contao\FormdataComments', 'listComments'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['PBDKN\Efgco4\Resources\contao\classes\Formdata', 'getSearchablePages'];
$GLOBALS['TL_HOOKS']['executePostActions'][] = ['PBDKN\Efgco4\Resources\contao\classes\Formdata', 'executePostActions'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['PBDKN\Efgco4\Resources\contao\classes\EfgInsertTag', 'Efg_InsertTags'];

// Hooks zum bearbeiten der Mails
//$GLOBALS['TL_HOOKS']['processFormData'][] = array('PBDKN\Efgco4\Resources\contao\classes\efgMailHooks', 'processFormData');   // nur zum Test
//$GLOBALS['TL_HOOKS']['compileFormFields'][] = array('PBDKN\Efgco4\Resources\contao\classes\efgMailHooks', 'compileFormFields');
$GLOBALS['TL_HOOKS']['prepareFormData'][] = ['PBDKN\Efgco4\Resources\contao\classes\efgMailHooks', 'prepareFormData'];

// end config efg
