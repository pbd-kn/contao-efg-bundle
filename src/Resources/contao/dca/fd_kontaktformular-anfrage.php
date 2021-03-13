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
// last created on 2020-12-14 16:03:05 by saving form "Kontaktformular Anfrage"

/*
 * Table tl_formdata defined by form "Kontaktformular Anfrage"
 */
$GLOBALS['TL_DCA']['tl_formdata'] = [
    // Config
    'config' => [
        'dataContainer' => 'Formdata',
        'ctable' => ['tl_formdata_details'],
        'closed' => false,
        'notEditable' => false,
        'enableVersioning' => false,
        'doNotCopyRecords' => true,
        'doNotDeleteRecords' => true,
        'switchToEdit' => true,
    ],
    // List
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['date DESC'],
            'flag' => 8,
            'panelLayout' => 'filter;search,sort,limit',
        ],
        'label' => [
            'fields' => ['date', 'form', 'alias', 'be_notes', 'anrede', 'vorname', 'Artikelbezeichnung', 'name', 'email', 'message'],
            'format' => '<div class="fd_wrap">
	<div class="fd_head">%s<span>[%s]</span><span>%s</span></div>
		<div class="fd_notes">%s</div>
	<div class="fd_row field_anrede"><div class="fd_label">anrede: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_vorname"><div class="fd_label">ihr vorname: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_Artikelbezeichnung"><div class="fd_label">Artikelbezeichnung: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_name"><div class="fd_label">ihr name: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_email"><div class="fd_label">email: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_message"><div class="fd_label">ihre anfrage: </div><div class="fd_value">%s </div></div>
		</div>',
            /*
            'label_callback'          => array('tl_fd_kontaktformular_anfrage','getRowLabel')
            */
        ],
        'global_operations' => [
            'import' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['import'],
                'href' => 'key=import',
                'class' => 'header_csv_import',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'export' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['export'],
                'href' => 'act=export',
                'class' => 'header_csv_export',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'exportxls' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['exportxls'],
                'href' => 'act=exportxls',
                'class' => 'header_xls_export',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['edit'],
                'href' => 'act=edit',
                'button_callback' => ['FormdataBackend', 'callbackEditButton'],
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    // Palettes
    'palettes' => [
        'default' => 'form,alias,date,ip,published,sorting;{confirmation_legend},confirmationSent,confirmationDate;{fdNotes_legend:hide},be_notes;{fdOwner_legend:hide},fd_member,fd_user,fd_member_group,fd_user_group;{fdDetails_legend},anrede,vorname,Artikelbezeichnung,name,email,message',
    ],

    // Base fields in table tl_formdata
    'fields' => [
        'form' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['form'],
            'inputType' => 'select',
            'exclude' => true,
            'search' => false,
            'filter' => false,
            'sorting' => false,
            'options_callback' => ['tl_formdata', 'getFormsSelect'],
            'eval' => ['chosen' => true, 'tl_class' => 'w50'],
        ],
        'date' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['date'],
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'flag' => 8,
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ],
        'ip' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['ip'],
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'sorting' => false,
            'filter' => false,
            'eval' => ['tl_class' => 'w50'],
        ],
        'fd_member' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['fd_member'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'options_callback' => ['tl_formdata', 'getMembersSelect'],
        ],
        'fd_user' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['fd_user'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'options_callback' => ['tl_formdata', 'getUsersSelect'],
        ],
        'fd_member_group' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['fd_member_group'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'options_callback' => ['tl_formdata', 'getMemberGroupsSelect'],
        ],
        'fd_user_group' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['fd_user_group'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'options_callback' => ['tl_formdata', 'getUserGroupsSelect'],
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12 cbx clr'],
            // 'default'                 => '1'
        ],
        'sorting' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['sorting'],
            'exclude' => true,
            'filter' => false,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'maxlength' => 10, 'tl_class' => 'w50'],
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alnum', 'unique' => true, 'spaceToUnderscore' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'save_callback' => [
                ['tl_formdata', 'generateAlias'],
            ],
        ],
        'confirmationSent' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['confirmationSent'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true, 'isBoolean' => true],
        ],
        'confirmationDate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['confirmationDate'],
            'exclude' => true,
            'filter' => true,
            'flag' => 8,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ],
        'be_notes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['be_notes'],
            'inputType' => 'textarea',
            'exclude' => true,
            'search' => true,
            'sorting' => false,
            'filter' => false,
            'eval' => ['rte' => 'tinyMCE', 'cols' => 80, 'rows' => 5, 'style' => 'height: 80px'],
        ],
        'import_source' => [
            'label' => &$GLOBALS['TL_LANG']['tl_formdata']['import_source'],
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'csv', 'class' => 'mandatory'],
        ],
    ],
    'tl_formdata' => [
        'baseFields' => ['id', 'sorting', 'tstamp', 'form', 'ip', 'date', 'fd_member', 'fd_user', 'fd_member_group', 'fd_user_group', 'published', 'alias', 'be_notes', 'confirmationSent', 'confirmationDate'],
        'detailFields' => ['anrede', 'vorname', 'Artikelbezeichnung', 'name', 'email', 'message'],
        'formFilterKey' => 'form',
        'formFilterValue' => 'Kontaktformular Anfrage',
    ],
];

// Detail fields in table tl_formdata_details
// 'anrede'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['label'] = ['anrede', '[anrede] anrede'];
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['options']['herr'] = 'herr';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['options']['frau'] = 'frau';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['default'][] = 'herr';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['ff_id'] = 25;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['f_id'] = 2;
// 'vorname'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['label'] = ['ihr vorname', '[vorname] ihr vorname'];
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['eval']['rgxp'] = 'extnd';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['ff_id'] = 26;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['f_id'] = 2;
// 'Artikelbezeichnung'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['label'] = ['Artikelbezeichnung', '[Artikelbezeichnung] '];
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['formfieldType'] = 'hidden';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['sorting'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['default'] = '{{get::name}}';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['ff_id'] = 13;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['f_id'] = 2;
// 'name'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['label'] = ['ihr name', '[name] ihr name'];
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['eval']['rgxp'] = 'extnd';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['ff_id'] = 6;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['f_id'] = 2;
// 'email'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['label'] = ['email', '[email] email'];
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['eval']['rgxp'] = 'email';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['ff_id'] = 7;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['f_id'] = 2;
// 'message'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['label'] = ['ihre anfrage', '[message] ihre anfrage'];
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['inputType'] = 'textarea';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['formfieldType'] = 'textarea';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['sorting'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['eval']['rgxp'] = 'extnd';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['eval']['cols'] = 80;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['eval']['rows'] = 8;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['ff_id'] = 8;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['f_id'] = 2;

/*
 * Class tl_fd_kontaktformular_anfrage
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Thomas Kuhn 2007-2014
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    Efg
 */
/*
 * erweitert fuer contao 4
 * pbd verhinderung der doppeldefinition der class bei delete eines Eintrags aus der Tabelle
 */
if (!class_exists('tl_fd_kontaktformular_anfrage', false)) {
    class tl_fd_kontaktformular_anfrage extends \Backend
    {
        /**
         * Database result.
         *
         * @var array
         */
        protected $arrData;

        public function __construct()
        {
            parent::__construct();
        }

        /*
        * Create list label for formdata item
        * This can be used to customize the backend list view for formdata
        */
        public function getRowLabel($arrRow)
        {
            $strRowLabel = '';

            $strKey = 'unpublished';

            $strRowLabel .= '<div class="fd_wrap">';
            $strRowLabel .= '<div class="fd_head">'.date($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['date']).'<span>['.$arrRow['form'].']</span><span>'.$arrRow['alias'].'</span></div>';
            $strRowLabel .= '<div class="fd_notes">'.$arrRow['be_notes'].'</div>';
            $strRowLabel .= '<div class="mark_links">';
            if (\strlen($arrRow['anrede'])) {
                $strRowLabel .= '<div class="fd_row field_anrede">';
                $strRowLabel .= '<div class="fd_label">'.$GLOBALS['TL_DCA']['tl_formdata']['fields']['anrede']['label'][0].': </div>';
                $strRowLabel .= '<div class="fd_value">'.$arrRow['anrede'].' </div>';
                $strRowLabel .= '</div>';
            }
            if (\strlen($arrRow['vorname'])) {
                $strRowLabel .= '<div class="fd_row field_vorname">';
                $strRowLabel .= '<div class="fd_label">'.$GLOBALS['TL_DCA']['tl_formdata']['fields']['vorname']['label'][0].': </div>';
                $strRowLabel .= '<div class="fd_value">'.$arrRow['vorname'].' </div>';
                $strRowLabel .= '</div>';
            }
            if (\strlen($arrRow['Artikelbezeichnung'])) {
                $strRowLabel .= '<div class="fd_row field_Artikelbezeichnung">';
                $strRowLabel .= '<div class="fd_label">'.$GLOBALS['TL_DCA']['tl_formdata']['fields']['Artikelbezeichnung']['label'][0].': </div>';
                $strRowLabel .= '<div class="fd_value">'.$arrRow['Artikelbezeichnung'].' </div>';
                $strRowLabel .= '</div>';
            }
            if (\strlen($arrRow['name'])) {
                $strRowLabel .= '<div class="fd_row field_name">';
                $strRowLabel .= '<div class="fd_label">'.$GLOBALS['TL_DCA']['tl_formdata']['fields']['name']['label'][0].': </div>';
                $strRowLabel .= '<div class="fd_value">'.$arrRow['name'].' </div>';
                $strRowLabel .= '</div>';
            }
            if (\strlen($arrRow['email'])) {
                $strRowLabel .= '<div class="fd_row field_email">';
                $strRowLabel .= '<div class="fd_label">'.$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['label'][0].': </div>';
                $strRowLabel .= '<div class="fd_value">'.$arrRow['email'].' </div>';
                $strRowLabel .= '</div>';
            }
            if (\strlen($arrRow['message'])) {
                $strRowLabel .= '<div class="fd_row field_message">';
                $strRowLabel .= '<div class="fd_label">'.$GLOBALS['TL_DCA']['tl_formdata']['fields']['message']['label'][0].': </div>';
                $strRowLabel .= '<div class="fd_value">'.$arrRow['message'].' </div>';
                $strRowLabel .= '</div>';
            }
            $strRowLabel .= '</div></div>';

            return $strRowLabel;
        }
    }
}
