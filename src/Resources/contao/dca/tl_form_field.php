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

// Table tl_form_fields
$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['headerFields'][] = 'storeFormdata';
$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['headerFields'][] = 'sendConfirmationMail';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgLookupOptions'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgLookupOptions'],
    'exclude' => true,
    'inputType' => 'efgLookupOptionWizard',
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgMultiSRC'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgMultiSRC'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['fieldType' => 'checkbox', 'multiple' => true, 'files' => true, 'mandatory' => true, 'extensions' => 'gif,jpg,png'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['mandatory']['eval']['tl_class'] = 'w50 cbx';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageMultiple'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageMultiple'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 cbx'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageUseHomeDir'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageUseHomeDir'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12 cbx'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageSortBy'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageSortBy'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['name_asc', 'name_desc', 'date_asc', 'date_desc', 'random'],
    'reference' => &$GLOBALS['TL_LANG']['tl_form_field'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageSize'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageSize'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['multiple' => true, 'size' => 2, 'rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImagePerRow'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgImagePerRow'],
    'default' => 4,
    'exclude' => true,
    'inputType' => 'select',
    'options' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "smallint(5) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageMargin'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageMargin'],
    'exclude' => true,
    'inputType' => 'trbl',
    'options' => ['px', '%', 'em', 'pt', 'pc', 'in', 'cm', 'mm'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgImageFullsize'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgImageFullsize'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12 cbx'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgAddBackButton'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgAddBackButton'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgBackSlabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgBackSlabel'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgSwitchButtonOrder'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgSwitchButtonOrder'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 cbx'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgBackStoreSessionValues'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgBackStoreSessionValues'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 cbx'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgBackImageSubmit'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgBackImageSubmit'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['efgBackSingleSRC'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form_field']['efgBackSingleSRC'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'gif,jpg,png', 'mandatory' => true, 'tl_class' => 'clr'],
    'sql' => 'binary(16) NULL',
];

// Add palettes
if (\is_array($GLOBALS['TL_DCA']['tl_form_field']['palettes'])) {
    // Field type efgLookupSelect
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], \count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
        ['efgLookupSelect' => '{type_legend},type,name,label;{options_legend},efgLookupOptions;{fconfig_legend},mandatory,multiple;{expert_legend:hide},accesskey,class;{submit_legend},addSubmit']
    );
    // Field type efgLookupCheckbox
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], \count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
        ['efgLookupCheckbox' => '{type_legend},type,name,label;{options_legend},efgLookupOptions;{fconfig_legend},mandatory;{expert_legend:hide},accesskey,class;{submit_legend},addSubmit']
    );
    // Field type efgLookupRadio
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], \count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
        ['efgLookupRadio' => '{type_legend},type,name,label;{options_legend},efgLookupOptions;{fconfig_legend},mandatory;{expert_legend:hide},accesskey,class;{submit_legend},addSubmit']
    );
    // Field type efgImageSelect
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], \count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
        ['efgImageSelect' => '{type_legend},type,name,label;{options_legend},efgMultiSRC,efgImageUseHomeDir,efgImageFullsize,efgImageSize,efgImageMargin,efgImagePerRow,efgImageSortBy;{fconfig_legend},mandatory,efgImageMultiple;{expert_legend:hide},accesskey,class;{submit_legend},addSubmit']
    );
    // Field type efgFormPaginator
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['palettes'], \count($GLOBALS['TL_DCA']['tl_form_field']['palettes']),
        ['efgFormPaginator' => '{type_legend},type,slabel;{image_legend:hide},imageSubmit;{backbutton_legend},efgAddBackButton;{expert_legend:hide},class,accesskey']
    );
    $GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'efgAddBackButton';
    $GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'efgBackImageSubmit';
    $GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['efgAddBackButton'] = 'efgBackSlabel,efgSwitchButtonOrder,efgBackStoreSessionValues,efgBackImageSubmit';
    $GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['efgBackImageSubmit'] = 'efgBackSingleSRC';
}

// Add field types to type options
if (isset($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'])&&\is_array($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'])) {
    // Field type efgLookupSelect
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('select', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], true) + 1),
        'efgLookupSelect'
    );
    // Field type efgLookupCheckbox
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('checkbox', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], true) + 1),
        'efgLookupCheckbox'
    );
    // Field type efgLookupRadio
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('radio', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], true) + 1),
        'efgLookupRadio'
    );
    // Field type efgImageSelect
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('upload', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], true) + 1),
        'efgImageSelect'
    );
    // Field type efgFormPaginator
    array_insert($GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], (array_search('submit', $GLOBALS['TL_DCA']['tl_form_field']['fields']['type']['options'], true) + 1),
        'efgFormPaginator'
    );
}

/*  nach config verlegt damit fe editing mit hasteform funktioniert
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
*/
