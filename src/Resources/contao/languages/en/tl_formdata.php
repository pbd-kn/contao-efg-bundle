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

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_formdata']['form'] = ['Form', 'Data from Form'];
$GLOBALS['TL_LANG']['tl_formdata']['date'] = ['Date', 'Date of entry'];
$GLOBALS['TL_LANG']['tl_formdata']['ip'] = ['IP address', 'IP address of sender'];
$GLOBALS['TL_LANG']['tl_formdata']['be_notes'] = ['Notes', 'Notes, todos etc.'];
$GLOBALS['TL_LANG']['tl_formdata']['import_source'] = ['Source file', 'Please choose the CSV file you want to import from the files directory.'];
$GLOBALS['TL_LANG']['tl_formdata']['import_preview'] = ['Preview and field mapping', 'Please select the form data fields in which the columns of the CSV file should be imported. The table below shows up to 50 lines of the CSV file.'];
$GLOBALS['TL_LANG']['tl_formdata']['csv_has_header'] = ['File with column labels', 'The first line of the file contains column labels.'];
$GLOBALS['TL_LANG']['tl_formdata']['option_import_ignore'] = '-do not import-';
$GLOBALS['TL_LANG']['tl_formdata']['published'] = ['Published', 'You can use this option as display condition when using a list module.'];
$GLOBALS['TL_LANG']['tl_formdata']['sorting'] = ['Sorting value', 'The sorting value can be used in a listing module.'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_member'] = ['Member', 'Member as owner of this record'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_user'] = ['User', 'User as owner of this record'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_member_group'] = ['Member group', 'Member group as owner of this record'];
$GLOBALS['TL_LANG']['tl_formdata']['fd_user_group'] = ['User group', 'User group as owner of this record'];
$GLOBALS['TL_LANG']['tl_formdata']['alias'] = ['Alias', 'An alias is a unique reference to the record which can be called instead of the record ID.'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_sender'] = ['Sender', 'Email address of sender'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_recipient'] = ['Recipient', 'Email address of recipient'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_subject'] = ['Subject', 'Subject of confirmation mail'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_body_plaintext'] = ['Message (plain text)', 'Text of mail as plain text'];
$GLOBALS['TL_LANG']['tl_formdata']['mail_body_html'] = ['Message (HTML)', 'Text of mail as HTML'];
$GLOBALS['TL_LANG']['tl_formdata']['attachments'] = 'Attachements';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_formdata']['new'] = ['New record', 'Create a new record'];
$GLOBALS['TL_LANG']['tl_formdata']['edit'] = ['Edit record', 'Edit record ID %s'];
$GLOBALS['TL_LANG']['tl_formdata']['copy'] = ['Duplicate record', 'Duplicate record ID %s'];
$GLOBALS['TL_LANG']['tl_formdata']['delete'] = ['Delete record', 'Delete record ID %s'];
$GLOBALS['TL_LANG']['tl_formdata']['show'] = ['Record details', 'Show details of record ID %s'];
$GLOBALS['TL_LANG']['tl_formdata']['mail'] = ['Send confirmation mail', 'Send confirmation mail for record ID %s'];
$GLOBALS['TL_LANG']['tl_formdata']['import'] = ['CSV import', 'Import records from a CSV file'];
$GLOBALS['TL_LANG']['tl_formdata']['export'] = ['CSV export', 'Export records to a CSV file'];
$GLOBALS['TL_LANG']['tl_formdata']['exportxls'] = ['Excel export', 'Export records to a MS Excel file'];

$GLOBALS['TL_LANG']['tl_formdata']['mail_sent'] = 'Mail has been sent to %s';
$GLOBALS['TL_LANG']['tl_formdata']['confirmation_sent'] = 'For this record a confirmation mail has been sent on %s at %s';
$GLOBALS['TL_LANG']['tl_formdata']['confirmationSent'] = ['Confirmation mail sent', 'Confirmation mail has been sent for this record'];
$GLOBALS['TL_LANG']['tl_formdata']['confirmationDate'] = ['Confirmation mail sent on', 'At this time confirmation mail has been sent'];
$GLOBALS['TL_LANG']['tl_formdata']['import_confirm'] = '%s new entries have been imported.';
$GLOBALS['TL_LANG']['tl_formdata']['import_invalid'] = '%s invalid entries have been skipped.';
$GLOBALS['TL_LANG']['tl_formdata']['error_select_source'] = 'Please select a source file!';

/*
 * Text links in frontend listing formdata
 */
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_details'] = ['Details', 'Show details'];
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_edit'] = ['Edit', 'Edit record'];
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_delete'] = ['Delete', 'Delete record'];
$GLOBALS['TL_LANG']['tl_formdata']['fe_link_export'] = ['CSV Export', 'Export record as CSV file'];

$GLOBALS['TL_LANG']['tl_formdata']['fe_deleteConfirm'] = 'Do you really want to delete entry?';

/*
 * legends
 */
$GLOBALS['TL_LANG']['tl_formdata']['confirmation_legend'] = 'Confirmation mail';
$GLOBALS['TL_LANG']['tl_formdata']['fdNotes_legend'] = 'Notes';
$GLOBALS['TL_LANG']['tl_formdata']['fdOwner_legend'] = 'Owner';
$GLOBALS['TL_LANG']['tl_formdata']['fdDetails_legend'] = 'Details';
