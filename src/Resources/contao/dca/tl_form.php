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
 * Table tl_form
 */

$GLOBALS['TL_DCA']['tl_form']['config']['onsubmit_callback'][] = ['FormdataBackend', 'createFormdataDca'];   /* neues Form erzeugen */
$GLOBALS['TL_DCA']['tl_form']['config']['ondelete_callback'][] = ['FormdataBackend', 'deleteFormdataDca'];   /* form löschen */

// fields
$GLOBALS['TL_DCA']['tl_form']['fields']['storeFormdata'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['storeFormdata'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => ['helpwizard' => true, 'submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['efgStoreValues'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['efgStoreValues'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['useFormValues'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['useFormValues'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['useFieldNames'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['useFieldNames'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['efgAliasField'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['efgAliasField'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'select',
    'options_callback' => ['tl_form_efg', 'getAliasFormFields'],
    'eval' => ['chosen' => true, 'mandatory' => true, 'maxlength' => 64],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['sendConfirmationMail'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['sendConfirmationMail'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => ['helpwizard' => true, 'submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailRecipientField'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipientField'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'select',
    'options_callback' => ['tl_form_efg', 'getEmailFormFields'],
    'eval' => ['chosen' => true, 'mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailRecipient'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipient'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailSender'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailSender'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailReplyto'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailReplyto'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'text',
    'eval' => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailSubject'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailSubject'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailText'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailText'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'textarea',
    'eval' => ['mandatory' => true, 'rows' => 15, 'allowHTML' => false, 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailTemplate'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailTemplate'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'fileTree',
    'eval' => ['helpwizard' => false, 'files' => true, 'fieldType' => 'radio', 'extensions' => 'htm,html,txt,tpl'],
    'sql' => 'binary(16) NULL',
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailSkipEmpty'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['skipEmtpy'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['sendFormattedMail'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['sendFormattedMail'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailRecipient'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['recipient'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'extnd', 'tl_class' => 'w50'],
    'sql' => 'text NULL',
];
$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailSubject'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['subject'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailText'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['formattedMailText'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'textarea',
    'eval' => ['rows' => 15, 'allowHTML' => false, 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailTemplate'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['formattedMailTemplate'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'fileTree',
    'eval' => ['helpwizard' => false, 'files' => true, 'fieldType' => 'radio', 'extensions' => 'htm,html,txt,tpl'],
    'sql' => 'binary(16) NULL',
];
$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailSkipEmpty'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['skipEmtpy'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_form']['fields']['addConfirmationMailAttachments'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['addConfirmationMailAttachments'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['confirmationMailAttachments'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['confirmationMailAttachments'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'fileTree',
    'eval' => ['fieldType' => 'checkbox', 'files' => true, 'filesOnly' => true, 'multiple' => true, 'mandatory' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_form']['fields']['addFormattedMailAttachments'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['addFormattedMailAttachments'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['formattedMailAttachments'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['formattedMailAttachments'],
    'exclude' => true,
    'filter' => false,
    'inputType' => 'fileTree',
    'eval' => ['fieldType' => 'checkbox', 'files' => true, 'filesOnly' => true, 'multiple' => true, 'mandatory' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_form']['fields']['efgDebugMode'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['efgDebugMode'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'select',
    'options' => ['0' => '-', '1' => 'small', '3' => 'medium', '7' => 'full', '8' => 'emailsmall', '24' => 'emailmedium', '56' => 'emailfull', '255' => 'all'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(12) default '-'",
];
$GLOBALS['TL_DCA']['tl_form']['fields']['useSendto'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_form']['useSendto'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default '0'",
];

// Palettes
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'sendFormattedMail';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'sendConfirmationMail';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'addConfirmationMailAttachments';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'addFormattedMailAttachments';
$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'storeFormdata';
$GLOBALS['TL_DCA']['tl_form']['palettes']['default'] = str_replace(['storeValues', 'sendViaEmail', 'formID'], ['storeValues;{efgStoreFormdata_legend:hide},storeFormdata', 'sendViaEmail;{efgSendFormattedMail_legend:hide},sendFormattedMail;{efgSendConfirmationMail_legend:hide},sendConfirmationMail', 'formID,useSendto,efgDebugMode'], $GLOBALS['TL_DCA']['tl_form']['palettes']['default']);
// Subpalettes
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], \count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
    ['sendFormattedMail' => 'formattedMailRecipient,formattedMailSubject,formattedMailText,formattedMailTemplate,formattedMailSkipEmpty,addFormattedMailAttachments']
);
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], \count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
    ['addFormattedMailAttachments' => 'formattedMailAttachments']
);
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], \count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
    ['sendConfirmationMail' => 'confirmationMailRecipientField,confirmationMailRecipient,confirmationMailSender,confirmationMailReplyto,confirmationMailSubject,confirmationMailText,confirmationMailTemplate,confirmationMailSkipEmpty,addConfirmationMailAttachments']
);
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], \count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
    ['addConfirmationMailAttachments' => 'confirmationMailAttachments']
);
array_insert($GLOBALS['TL_DCA']['tl_form']['subpalettes'], \count($GLOBALS['TL_DCA']['tl_form']['subpalettes']),
    ['storeFormdata' => 'efgAliasField,efgStoreValues,useFormValues,useFieldNames']
);

/**
 * Class tl_form_efg.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class tl_form_efg extends \Backend
{
    /**
     * Return all possible Email fields  as array.
     *
     * @return array
     */
    public function getEmailFormFields()
    {
        $fields = [];

        // Get all form fields which can be used to define recipient of confirmation mail
        $objFields = \Database::getInstance()->prepare('SELECT id,name,label FROM tl_form_field WHERE pid=? AND `type`!=? AND `type`!=? AND `type`!=? AND `type`!=? AND `type`!=? AND `type`!=? AND `type`!=? AND `type`!=? AND `type`!=? ORDER BY name ASC')
            ->execute(\Input::get('id'), 'calendar', 'captcha', 'condition', 'efgFormPaginator', 'explanation', 'headline', 'submit', 'upload', 'xdependentcalendarfields')
        ;

        $fields[] = '-';
        while ($objFields->next()) {
            $k = $objFields->name;
            if (\strlen($k)) {
                $v = $objFields->label;
                $v = \strlen($v) ? $v.' ['.$k.']' : $k;
                $fields[$k] = $v;
            }
        }

        return $fields;
    }

    /**
     * Return all possible Alias fields as array.
     *
     * @return array
     */
    public function getAliasFormFields()
    {
        $fields = [];

        // Get all form fields which can be used to build auto alias
        $objFields = \Database::getInstance()->prepare('SELECT id,name,label FROM tl_form_field WHERE pid=? AND (type=? OR type=?) ORDER BY name ASC')
            ->execute(\Input::get('id'), 'text', 'hidden')
        ;

        $fields[] = '-';
        while ($objFields->next()) {
            $k = $objFields->name;
            $v = $objFields->label;
            $v = \strlen($v) ? $v.' ['.$k.']' : $k;
            $fields[$k] = $v;
        }

        return $fields;
    }
}
