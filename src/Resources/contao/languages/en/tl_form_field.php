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

$GLOBALS['TL_LANG']['FFL']['efgLookupSelect'] = ['Select menu (DB)', 'A select menu (DB) is a drop down menu containing several options from which one can be selected. The options are taken from a definable database table.'];
$GLOBALS['TL_LANG']['FFL']['efgLookupCheckbox'] = ['Checkbox menu (DB)', 'A checkbox menu (DB) is a multi-line menu containing one or more options from which any can be selected. The options are taken from a definable database table.'];
$GLOBALS['TL_LANG']['FFL']['efgLookupRadio'] = ['Radio button menu (DB)', 'A radio button menu (DB) is a multi-line menu containing several options from which one can be selected. The options are taken from a definable database table.'];
$GLOBALS['TL_LANG']['FFL']['efgImageSelect'] = ['Image select menu', 'An image select menu renders an image gallery with radio buttons or checkboxes in the form.'];
$GLOBALS['TL_LANG']['FFL']['efgFormPaginator'] = ['Submit field and page break', 'buttons and page break for a multipage form.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgLookupOptions'] = ['Options from database'];
$GLOBALS['TL_LANG']['tl_form_field']['lookup_field'] = ['Database field (label)', 'Please choose the database field where the available options (label) should be taken from.'];
$GLOBALS['TL_LANG']['tl_form_field']['lookup_val_field'] = ['Database field (value)', 'Please choose the database field where the available options (value) should be taken from.'];
$GLOBALS['TL_LANG']['tl_form_field']['lookup_where'] = ['Condition', 'If you want to exclude certain records, you can enter a condition here (e.g. <em>published=\'1\'</em> or <em>type!=\'admin\'</em>).'];
$GLOBALS['TL_LANG']['tl_form_field']['lookup_sort'] = ['Order by', 'Here you can enter a comma seperated list of fields to sort the results by (e.g.  <em>title ASC</em> or <em>city ASC, gender DESC</em>). If you don\'t specify a sorting, records are sorted according to the label field.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgMultiSRC'] = ['Source files', 'Please select one or more files or folders (files in a folder will be included automatically).'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageUseHomeDir'] = ['Use home directory as source', 'If there is a logged in user, use his home directory as source instead of the \'source files\' defined above.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageMultiple'] = ['Multiple selection', 'Allow visitors to select more than one image.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageSortBy'] = ['Order by', 'Please select a sort order.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageSize'] = ['Image width and height', 'Please enter either the image width, the image height or both measures to resize the image. If you leave both fields blank, the original image size will be displayed.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImagePerRow'] = ['Thumbnails per row', 'Please enter the number of thumbnails per row.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageMargin'] = ['Image margin', 'Please enter the top, right, bottom and left margin and the unit. Image margin is the space inbetween an image and its neighbour elements.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgImageFullsize'] = ['Fullsize view', 'If you choose this option, the image can be viewed fullsize by clicking it.'];
$GLOBALS['TL_LANG']['tl_form_field']['name_asc'] = 'File name (ascending)';
$GLOBALS['TL_LANG']['tl_form_field']['name_desc'] = 'File name (descending)';
$GLOBALS['TL_LANG']['tl_form_field']['date_asc'] = 'Date (ascending)';
$GLOBALS['TL_LANG']['tl_form_field']['date_desc'] = 'Date (descending)';
$GLOBALS['TL_LANG']['tl_form_field']['meta'] = 'Meta file (meta.txt)';
$GLOBALS['TL_LANG']['tl_form_field']['random'] = 'Random order';
$GLOBALS['TL_LANG']['tl_form_field']['efgAddBackButton'] = ['Add a back button', 'Create a back button as link to previous form page.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgSwitchButtonOrder'] = ['Swap order of buttons', 'Position the back button to the right side of the submit button.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackStoreSessionValues'] = ['Save form input on \'back\'', 'Store data in frontend form on submitting \'back\' to populate fields if this page is recalled.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackSlabel'] = ['Back button label', 'Please enter the label of the back button.'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackImageSubmit'] = $GLOBALS['TL_LANG']['tl_form_field']['imageSubmit'];
$GLOBALS['TL_LANG']['tl_form_field']['efgBackSingleSRC'] = $GLOBALS['TL_LANG']['tl_form_field']['singleSRC'];
$GLOBALS['TL_LANG']['tl_form_field']['backbutton_legend'] = 'Back button';
