<?php

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
// last created on 2021-07-08 10:33:16 by saving form ""



/**
 * Table tl_formdata defined by form ""
 */
$GLOBALS['TL_DCA']['tl_formdata'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Formdata',
		'ctable'                      => array('tl_formdata_details'),
		'closed'                      => true,
		'notEditable'                 => false,
		'enableVersioning'            => false,
		'doNotCopyRecords'            => true,
		'doNotDeleteRecords'          => true,
		'switchToEdit'                => true
	),
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('date DESC'),
			'flag'                    => 8,
			'panelLayout'             => 'filter;search,sort,limit',
		),
		'label' => array
		(
			'fields'                  => array('date', 'form', 'alias', 'be_notes' , 'cal_von', 'cal_bis', 'countAdults', 'countChildren', 'ageChild_1', 'ageChild_2', 'ageChild_3', 'ageChild_4', 'ageChild_5', 'ageChild_6', 'ageChild_7', 'ageChild_8', 'salutation', 'firstname', 'lastname', 'email', 'phone', 'tel', 'others', 'street', 'zip', 'city', 'country'),
			/*
			'format'                  => '<div class="fd_wrap">
	<div class="fd_head">%s<span>[%s]</span></div>
	<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h64' : '') . ' block">	<div class="fd_notes">%s</div>
	<div class="fd_row field_cal_von"><div class="fd_label">Arrival: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_cal_bis"><div class="fd_label">Departure: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_countAdults"><div class="fd_label">Adults: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_countChildren"><div class="fd_label">Children: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_1"><div class="fd_label">Age Child 1: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_2"><div class="fd_label">Age Child 2: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_3"><div class="fd_label">Age Child 3: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_4"><div class="fd_label">Age Child 4: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_5"><div class="fd_label">Age Child 5: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_6"><div class="fd_label">Age Child 6: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_7"><div class="fd_label">Age Child 7: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_ageChild_8"><div class="fd_label">Age Child 8: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_salutation"><div class="fd_label">Salutation: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_firstname"><div class="fd_label">First name: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_lastname"><div class="fd_label">Last name: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_email"><div class="fd_label">E-Mail: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_phone"><div class="fd_label">E-Mail wiederholen: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_tel"><div class="fd_label">Phone: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_others"><div class="fd_label">What else can we do for you? Tell us your requirements!: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_street"><div class="fd_label">Street: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_zip"><div class="fd_label">Postcode: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_city"><div class="fd_label">City: </div><div class="fd_value">%s </div></div>
	<div class="fd_row field_country"><div class="fd_label">Country: </div><div class="fd_value">%s </div></div>
		</div></div>',
			*/
			'label_callback'          => array('tl_fd_feedback','getRowLabel')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['edit'],
				'href'                => 'act=edit',
				'button_callback'     => array('FormdataBackend', 'callbackEditButton'),
				'icon'                => 'bundles/contaoefgco4/icons/edit.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'bundles/contaoefgco4/icons/delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_formdata']['show'],
				'href'                => 'act=show',
				'icon'                => 'bundles/contaoefgco4/icons/show.gif'
			)

		)
	),
	// Palettes
	'palettes' => array
	(
		'default'                     => 'form,alias,date,ip,published,sorting;{confirmation_legend},confirmationSent,confirmationDate;{fdNotes_legend:hide},be_notes;{fdOwner_legend:hide},fd_member,fd_user,fd_member_group,fd_user_group;{fdDetails_legend},cal_von,cal_bis,countAdults,countChildren,ageChild_1,ageChild_2,ageChild_3,ageChild_4,ageChild_5,ageChild_6,ageChild_7,ageChild_8,salutation,firstname,lastname,email,phone,tel,others,street,zip,city,country'
	),

	// Base fields in table tl_formdata
	'fields' => array
	(
		'form' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['form'],
			'inputType'               => 'select',
			'exclude'                 => false,
			'search'                  => true,
			'filter'                  => true,
			'sorting'                 => true,
			'options_callback'        => array('tl_formdata', 'getFormsSelect'),
			'eval'                    => array('chosen' => true, 'tl_class'=> 'w50')
		),
		'date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['date'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'filter'                  => true,
			'flag'                    => 8,
			'eval'                    => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
		),
		'ip' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['ip'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array('tl_class'=>'w50'),
		),
		'fd_member' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_member'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class'=>'w50'),
			'options_callback'        => array('tl_formdata', 'getMembersSelect'),
		),
		'fd_user' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_user'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class'=>'w50'),
			'options_callback'        => array('tl_formdata', 'getUsersSelect'),
		),
		'fd_member_group' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_member_group'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class'=>'w50'),
			'options_callback'        => array('tl_formdata', 'getMemberGroupsSelect'),
		),
		'fd_user_group' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['fd_user_group'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('chosen' => true, 'mandatory' => false, 'includeBlankOption' => true, 'tl_class'=>'w50'),
			'options_callback'        => array('tl_formdata', 'getUserGroupsSelect'),
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12 cbx clr'),
			// 'default'                 => '1'
		),
		'sorting' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['sorting'],
			'exclude'                 => true,
			'filter'                  => false,
			'inputType'               => 'text',
			'eval'                    => array('rgxp' => 'digit', 'maxlength' => 10, 'tl_class'=>'w50')
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['alias'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alnum', 'unique'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>64, 'tl_class'=>'w50'),
			'save_callback' => array
			(
				array('tl_formdata', 'generateAlias')
			)
		),
		'confirmationSent' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['confirmationSent'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50', 'doNotCopy'=>true, 'isBoolean'=>true)
		),
		'confirmationDate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['confirmationDate'],
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => 8,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker' => true, 'tl_class'=>'w50 wizard')
		),
		'be_notes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['be_notes'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => false,
			'filter'                  => false,
			'eval'                    => array('rte' => 'tinyMCE', 'cols' => 80,'rows' => 5, 'style' => 'height: 80px'),
		),
		'import_source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_formdata']['import_source'],
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'csv', 'class'=>'mandatory')
		)
	),
	'tl_formdata' => array
	(
		'baseFields'                 => array('id','sorting','tstamp','form','ip','date','fd_member','fd_user','fd_member_group','fd_user_group','published','alias','be_notes','confirmationSent','confirmationDate'),
		'detailFields'               => array('cal_von','cal_bis','countAdults','countChildren','ageChild_1','ageChild_2','ageChild_3','ageChild_4','ageChild_5','ageChild_6','ageChild_7','ageChild_8','salutation','firstname','lastname','email','phone','tel','others','street','zip','city','country'),
	)
);

// Detail fields in table tl_formdata_details
// 'cal_von'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['label'] = array('Arrival', '[cal_von] Arrival');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['search'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['default'] = '{{get::cal_von}}';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['eval']['rgxp'] = 'date';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['flag'] = 5;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['eval']['maxlength'] = 20;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['eval']['datepicker'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['eval']['tl_class'] = 'wizard';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['ff_id'] = 745;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['ff_class'] = 'cal_von form-control';
// 'cal_bis'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['label'] = array('Departure', '[cal_bis] Departure');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['search'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['default'] = '{{get::cal_bis}}';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['eval']['rgxp'] = 'date';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['flag'] = 5;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['eval']['maxlength'] = 20;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['eval']['datepicker'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['eval']['tl_class'] = 'wizard';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['ff_id'] = 748;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['ff_class'] = 'cal_bis form-control';
// 'countAdults'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['label'] = array('Adults', '[countAdults] Adults');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['default'][] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['ff_id'] = 755;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['ff_class'] = 'rangeinput form-control';
// 'countChildren'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['label'] = array('Children', '[countChildren] Children');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['ff_id'] = 758;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['ff_class'] = 'rangeinput form-control';
// 'ageChild_1'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['label'] = array('Age Child 1', '[ageChild_1] Age Child 1');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['ff_id'] = 761;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['ff_class'] = 'ageChildren rangeinput form-control';
// 'ageChild_2'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['label'] = array('Age Child 2', '[ageChild_2] Age Child 2');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['ff_id'] = 764;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['ff_class'] = 'ageChildren rangeinput form-control';
// 'ageChild_3'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['label'] = array('Age Child 3', '[ageChild_3] Age Child 3');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['ff_id'] = 767;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['ff_class'] = 'ageChildren rangeinput form-control';
// 'ageChild_4'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['label'] = array('Age Child 4', '[ageChild_4] Age Child 4');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['ff_id'] = 770;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['ff_class'] = 'ageChildren rangeinput form-control';
// 'ageChild_5'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['label'] = array('Age Child 5', '[ageChild_5] Age Child 5');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['ff_id'] = 773;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['ff_class'] = 'ageChildren rangeinput form-control';
// 'ageChild_6'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['label'] = array('Age Child 6', '[ageChild_6] Age Child 6');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['ff_id'] = 776;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['ff_class'] = 'ageChildren rangeinput form-control';
// 'ageChild_7'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['label'] = array('Age Child 7', '[ageChild_7] Age Child 7');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['ff_id'] = 779;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['ff_class'] = 'ageChildren rangeinput form-control';
// 'ageChild_8'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['label'] = array('Age Child 8', '[ageChild_8] Age Child 8');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['0'] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['1'] = '1';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['2'] = '2';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['3'] = '3';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['4'] = '4';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['5'] = '5';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['6'] = '6';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['7'] = '7';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['8'] = '8';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['9'] = '9';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['10'] = '10';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['11'] = '11';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['12'] = '12';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['13'] = '13';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['14'] = '14';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['options']['15'] = '15';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['default'][] = '0';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['ff_id'] = 782;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['ff_class'] = 'ageChildren rangeinput form-control';
// 'salutation'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['label'] = array('Salutation', '[salutation] Salutation');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['inputType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['formfieldType'] = 'select';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['filter'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['options'][''] = 'please choose';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['options']['HR'] = 'Mr.';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['options']['FR'] = 'Mrs.';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['options']['FI'] = 'Company';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['options']['FA'] = 'Family';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['eval']['chosen'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['ff_id'] = 790;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['ff_class'] = 'form-control';
// 'firstname'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['label'] = array('First name', '[firstname] First name');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['eval']['rgxp'] = 'extnd';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['ff_id'] = 793;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['ff_class'] = 'form-control';
// 'lastname'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['label'] = array('Last name', '[lastname] Last name');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['eval']['rgxp'] = 'extnd';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['ff_id'] = 796;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['ff_class'] = 'form-control';
// 'email'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['label'] = array('E-Mail', '[email] E-Mail');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['eval']['rgxp'] = 'email';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['ff_id'] = 799;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['ff_class'] = 'form-control';
// 'phone'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['label'] = array('E-Mail wiederholen', '[phone] E-Mail wiederholen');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['ff_id'] = 800;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['ff_class'] = 'form-control text';
// 'tel'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['label'] = array('Phone', '[tel] Phone');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['eval']['maxlength'] = 20;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['eval']['rgxp'] = 'phone';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['ff_id'] = 803;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['ff_class'] = 'form-control';
// 'others'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['label'] = array('What else can we do for you? Tell us your requirements!', '[others] What else can we do for you? Tell us your requirements!');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['inputType'] = 'textarea';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['formfieldType'] = 'textarea';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['sorting'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['eval']['cols'] = 30;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['eval']['rows'] = 5;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['ff_id'] = 810;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['f_id'] = 24;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['ff_class'] = 'nachricht form-control';
// 'street'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['label'] = array('Street', '[street] Street');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['eval']['rgxp'] = 'alpha';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['ff_id'] = 495;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['f_id'] = 14;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['ff_class'] = 'form-control';
// 'zip'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['label'] = array('Postcode', '[zip] Postcode');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['eval']['maxlength'] = 16;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['eval']['rgxp'] = 'alnum';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['ff_id'] = 498;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['f_id'] = 14;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['ff_class'] = 'form-control';
// 'city'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['label'] = array('City', '[city] City');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['eval']['rgxp'] = 'extnd';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['ff_id'] = 501;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['f_id'] = 14;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['ff_class'] = 'form-control';
// 'country'
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['label'] = array('Country', '[country] Country');
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['inputType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['formfieldType'] = 'text';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['exclude'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['search'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['sorting'] = true;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['filter'] = false;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['eval']['rgxp'] = 'alpha';
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['ff_id'] = 504;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['f_id'] = 14;
$GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['ff_class'] = 'form-control';

/**
 * Class tl_fd_
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Thomas Kuhn 2007-2014
 * @author     Thomas Kuhn <mail@th-kuhn.de>
 * @package    Efg
 */
/**
 * erweitert fuer contao 4
 * PBD verhinderung der doppeldefinition der class bei delete eines Eintrags aus der Tabelle
 */
if (!class_exists('tl_fd_feedback', false)) {
class tl_fd_feedback extends \Backend
{

	/**
	 * Database result
	 * @var array
	 */
	protected $arrData = null;

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
		$strRowLabel .= '<div class="fd_head">' . date($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['date']) . '<span>[' . $arrRow['form'] . ']</span><span>' . $arrRow['alias'] . '</span></div>';
		$strRowLabel .= '<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h64' : '') . ' block">';
		$strRowLabel .= '<div class="fd_notes">' . $arrRow['be_notes'] . '</div>';
		$strRowLabel .= '<div class="mark_links">';
		if (strlen($arrRow['cal_von']))
		{
			$strRowLabel .= '<div class="fd_row field_cal_von">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_von']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['cal_von'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['cal_bis']))
		{
			$strRowLabel .= '<div class="fd_row field_cal_bis">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['cal_bis']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['cal_bis'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['countAdults']))
		{
			$strRowLabel .= '<div class="fd_row field_countAdults">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['countAdults']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['countAdults'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['countChildren']))
		{
			$strRowLabel .= '<div class="fd_row field_countChildren">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['countChildren']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['countChildren'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_1']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_1">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_1']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_1'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_2']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_2">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_2']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_2'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_3']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_3">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_3']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_3'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_4']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_4">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_4']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_4'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_5']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_5">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_5']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_5'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_6']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_6">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_6']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_6'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_7']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_7">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_7']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_7'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['ageChild_8']))
		{
			$strRowLabel .= '<div class="fd_row field_ageChild_8">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['ageChild_8']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['ageChild_8'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['salutation']))
		{
			$strRowLabel .= '<div class="fd_row field_salutation">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['salutation']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['salutation'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['firstname']))
		{
			$strRowLabel .= '<div class="fd_row field_firstname">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['firstname']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['firstname'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['lastname']))
		{
			$strRowLabel .= '<div class="fd_row field_lastname">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['lastname']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['lastname'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['email']))
		{
			$strRowLabel .= '<div class="fd_row field_email">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['email']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['email'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['phone']))
		{
			$strRowLabel .= '<div class="fd_row field_phone">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['phone']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['phone'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['tel']))
		{
			$strRowLabel .= '<div class="fd_row field_tel">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['tel']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['tel'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['others']))
		{
			$strRowLabel .= '<div class="fd_row field_others">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['others']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['others'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['street']))
		{
			$strRowLabel .= '<div class="fd_row field_street">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['street']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['street'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['zip']))
		{
			$strRowLabel .= '<div class="fd_row field_zip">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['zip']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['zip'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['city']))
		{
			$strRowLabel .= '<div class="fd_row field_city">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['city']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['city'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		if (strlen($arrRow['country']))
		{
			$strRowLabel .= '<div class="fd_row field_country">';
			$strRowLabel .= '<div class="fd_label">' . $GLOBALS['TL_DCA']['tl_formdata']['fields']['country']['label'][0] . ': </div>';
			$strRowLabel .= '<div class="fd_value">' . $arrRow['country'] . ' </div>';
			$strRowLabel .= '</div>';
		}
		$strRowLabel .= '</div>';
		$strRowLabel .= '</div></div>';

		return $strRowLabel;

	}

}
} 

