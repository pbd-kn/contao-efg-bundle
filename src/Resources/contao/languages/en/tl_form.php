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
$GLOBALS['TL_LANG']['tl_form']['storeFormdata'] = ['Store data in module "form data"', 'If you choose this option, data will be stored in database "Form data".<br>Note: Please save this form again after adding or modifying form fields'];
$GLOBALS['TL_LANG']['tl_form']['efgAliasField'] = ['Form field for Alias', 'Choose the form field used to generate the alias of form data.'];
$GLOBALS['TL_LANG']['tl_form']['efgStoreValues'] = ['Save values of options', 'Choose this option, if you want to store the value instead of the label for field types select menu, radio button menu and checkbox menu. This option does not affect fied types select menu (DB), radio button menu (DB) and checkbox menu (DB).'];
$GLOBALS['TL_LANG']['tl_form']['useFormValues'] = ['Export field values', 'If you choose this option, during export field values will be exported instead of field identifiers for the selected form fields. This applies to form field types select menu, radio button menu and checkbox menu.'];
$GLOBALS['TL_LANG']['tl_form']['useFieldNames'] = ['Export field names', 'If you choose this option, during export field names will be exported instead of field labels.'];
$GLOBALS['TL_LANG']['tl_form']['sendConfirmationMail'] = ['Send confirmation via e-mail', 'If you choose this option, a confirmation will be sent via e-mail to the sender of the form.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipientField'] = ['Form field containing e-mail of recipient', 'Choose the form field in which the user enters his e-mail address or a field containing the e-mail address as value.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailRecipient'] = ['Recipient', 'Please enter one or more recipient e-mail addresses if e-mail address is not defined by a form field or if you want e-mail to be sent to additional recipients. Separate multiple addresses with comma.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailSender'] = ['Sender', 'Please enter e-mail address to be used as sender.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailReplyto'] = ['Reply to', 'Please enter one or more e-mail addresses if replies to this e-mail should not be sent to sender address. Separate multiple addresses with comma.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailSubject'] = ['Subject', 'Please enter the subject of confirmation mail. If you do not enter a subject, the probability increases that the e-mail is identified as SPAM.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailText'] = ['Text of confirmation mail', 'Please enter the text of confirmation mail. You can use standard insert tags and tags like form::NAME_OF_FORMFIELD .'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailTemplate'] = ['HTML-template for confirmation mail', 'If confirmation mail should be sent as HTML mail, please choose your HTML-template from file system.'];
$GLOBALS['TL_LANG']['tl_form']['addConfirmationMailAttachments'] = ['Add attachments to confirmation e-mail', 'You can select files that will be sent as attachments to the confirmation e-mail.'];
$GLOBALS['TL_LANG']['tl_form']['confirmationMailAttachments'] = ['Attachments', 'Pleae choose files to attach.'];
$GLOBALS['TL_LANG']['tl_form']['addFormattedMailAttachments'] = ['Add attachments to e-mail', 'You can select files that will be sent as attachments to the e-mail.'];
$GLOBALS['TL_LANG']['tl_form']['formattedMailAttachments'] = ['Attachments', 'Pleae choose files to attach.'];
$GLOBALS['TL_LANG']['tl_form']['sendFormattedMail'] = ['Send form data via e-mail (formatted text or html)', 'The text of mail can be defined using insert tags. Mail can be sent as HTML mail.'];
$GLOBALS['TL_LANG']['tl_form']['formattedMailText'] = ['Text of mail', 'Please enter the text of mail. You can use standard insert tags and tags like form::NAME_OF_FORMFIELD .'];
$GLOBALS['TL_LANG']['tl_form']['formattedMailTemplate'] = ['HTML-template for mail', 'If mail should be sent as HTML mail, please choose your HTML-template from file system.'];

$GLOBALS['TL_LANG']['tl_form']['efgStoreFormdata_legend'] = '(EFG) Store form data';
$GLOBALS['TL_LANG']['tl_form']['efgSendFormattedMail_legend'] = '(EFG) Send via e-mail';
$GLOBALS['TL_LANG']['tl_form']['efgSendConfirmationMail_legend'] = '(EFG) Send confirmation via e-mail';
$GLOBALS['TL_LANG']['tl_form']['efgDebugMode'] = ['EFG Debugmode', 'Set Debugmode'];
$GLOBALS['TL_LANG']['tl_form']['useSendto'] = ['use Hiddenfied sendto', 'Additional recipients can be specified under Hiddenfield sendto'];
