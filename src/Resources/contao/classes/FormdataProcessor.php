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

/**
 * Namespace.
 */

namespace PBDKN\Efgco4\Resources\contao\classes;

use Contao\Email;

/**
 * Class FormdataProcessor.
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class FormdataProcessor extends \Contao\Frontend
{
    protected $strFdDcaKey = '';
    //protected $myMailer;

    protected $strFormdataDetailsKey = 'details';

    public function __construct()
    {
        EfgLog::setEfgDebugmode('form');

        //$this->myMailer = \Contao\System::getContainer()->get('swiftmailer.mailer');
    }

    /**
     * Process submitted form data
     * Send mail, store data in backend.
     *
     * @param array      $arrSubmitted Submitted data
     * @param array|bool $arrForm      Form configuration
     * @param array|bool $arrFiles     Files uploaded
     * @param array|bool $arrLabels    Form field labels
     */
    public function processSubmittedData($arrSubmitted, $arrForm = false, $arrFiles = false, $arrLabels = false): void
    {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "processSubmittedData do '".\Input::get('do')."'");

        // Form config
        if (!$arrForm) {
            return;
        }

        $arrFormFields = [];

        $this->import('FrontendUser', 'Member');
        $this->import('Formdata');

        $this->strFdDcaKey = 'fd_'.(!empty($arrForm['alias']) ? $arrForm['alias'] : str_replace('-', '_', standardize($arrForm['title'])));
        $this->Formdata->FdDcaKey = $this->strFdDcaKey;
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'this->Formdata->FdDcaKey '.$this->Formdata->FdDcaKey);

        // Get params of related listing formdata
        if (!isset ($_SESSION['EFP']['LISTING_MOD']['id'])) {
          EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Fehler in der Sessionverwaltung ');
          return;      // Fehler in der Sessionverwaltung
        }
        $intListingId = (int) ($_SESSION['EFP']['LISTING_MOD']['id']);   // wird in ModuleFormdataListing gesetzt
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Module intListingId '.$intListingId);
        if ($intListingId > 0) {
            $objListing = \Database::getInstance()->prepare('SELECT * FROM tl_module WHERE id=?')   // lies module ein
                ->execute($intListingId)
            ;
            if ($objListing->numRows) {
                $arrListing = $objListing->fetchAssoc();

                // Mail delivery defined in frontend listing module
                $arrForm['sendConfirmationMailOnFrontendEditing'] = ($arrListing['efg_fe_no_confirmation_mail']) ? false : true;
                $arrForm['sendFormattedMailOnFrontendEditing'] = ($arrListing['efg_fe_no_formatted_mail']) ? false : true;
            }
        }

        if (!empty($arrListing['efg_DetailsKey'])) {
            $this->strFormdataDetailsKey = $arrListing['efg_DetailsKey'];
        }

        $blnFEedit = false;
        $intOldId = 0;
        $strRedirectTo = '';

        $strUrl = preg_replace('/\?.*$/', '', \Environment::get('request'));
        $strUrlParams = '';
        $blnQuery = false;
        foreach (preg_split('/&(amp;)?/', $_SERVER['QUERY_STRING']) as $fragment) {
            if (\strlen($fragment)) {
                if (0 !== strncasecmp($fragment, $this->strFormdataDetailsKey, \strlen($this->strFormdataDetailsKey)) && 0 !== strncasecmp($fragment, 'act', 3)) {
                    $strUrlParams .= (!$blnQuery ? '' : '&amp;').$fragment;
                    $blnQuery = true;
                }
            }
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'act '.\Input::get('act').' arrListing[efg_fe_edit_access] '.$arrListing['efg_fe_edit_access']);

        if (\in_array($arrListing['efg_fe_edit_access'], ['public', 'groupmembers', 'member'], true)) {
            if ('edit' === \Input::get('act')) {
                $blnFEedit = true;

                $objCheck = \Database::getInstance()->prepare('SELECT id FROM tl_formdata WHERE id=? OR alias=?')
                    ->execute(\Input::get($this->strFormdataDetailsKey), \Input::get($this->strFormdataDetailsKey))
                ;

                if (1 === $objCheck->numRows) {
                    $intOldId = (int) ($objCheck->id);
                } else {
                    $this->log('Could not identify record by ID "'.\Input::get($this->strFormdataDetailsKey).'"', __METHOD__, TL_GENERAL);
                }
            }
        }

        // Types of form fields with storable data
        $arrFFstorable = $this->Formdata->arrFFstorable;

        if (($arrForm['storeFormdata'] || $arrForm['sendConfirmationMail'] || $arrForm['sendFormattedMail']) && !empty($arrSubmitted)) {
            $timeNow = time();
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strFdDcaKey '.$this->strFdDcaKey);

            $this->loadDataContainer($this->strFdDcaKey);
            $this->loadDataContainer('tl_formdata_details');
            $this->loadDataContainer('tl_files');
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'bearbeite FORM id:  '.$arrForm['id'].' title: '.$arrForm['title']);

            $arrFormFields = $this->Formdata->getFormfieldsAsArray($arrForm['id']);

            $arrBaseFields = [];
            $arrDetailFields = [];
            if (!empty($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['baseFields'])) {
                $arrBaseFields = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['baseFields'];
            }
            if (!empty($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['detailFields'])) {
                $arrDetailFields = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['detailFields'];
            }
            $arrHookFields = array_merge($arrBaseFields, $arrDetailFields);

            $arrToSave = [];
            foreach ($arrSubmitted as $k => $varVal) {
                if (\in_array($k, ['id'], true)) {
                    continue;
                }
                if (\in_array($k, $arrHookFields, true) || \in_array($k, array_keys($arrFormFields), true) || \in_array($k, ['FORM_SUBMIT', 'MAX_FILE_SIZE'], true)) {
                    $arrToSave[$k] = $varVal;
                }
            }

            // HOOK: process efg form data callback
            if (\array_key_exists('processEfgFormData', $GLOBALS['TL_HOOKS']) && \is_array($GLOBALS['TL_HOOKS']['processEfgFormData'])) {
                foreach ($GLOBALS['TL_HOOKS']['processEfgFormData'] as $key => $callback) {
                    $this->import($callback[0]);
                    $arrResult = $this->{$callback[0]}->{$callback[1]}($arrToSave, $arrFiles, $intOldId, $arrForm, $arrLabels);     //Änderung PBD
                    if (!empty($arrResult)) {
                        $arrSubmitted = $arrResult;
                        $arrToSave = $arrSubmitted;
                    }
                }
            }
        }

        // Formdata storage
        if ($arrForm['storeFormdata'] && !empty($arrSubmitted)) {
            $blnStoreOptionsValue = ($arrForm['efgStoreValues']) ? true : false;

            // Get old record on frontend editing
            if ($intOldId > 0) {
                $arrOldData = $this->Formdata->getFormdataAsArray($intOldId);
                $arrOldFormdata = $arrOldData['fd_base'];
                $arrOldFormdataDetails = $arrOldData['fd_details'];
            }
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Formdata storage');

            // Prepare record tl_formdata
            $arrSet = [
                'form' => $arrForm['title'],
                'tstamp' => $timeNow,
                'date' => $timeNow,
                'ip' => \System::anonymizeIp(\Environment::get('ip')),
                'published' => ($GLOBALS['TL_DCA']['tl_formdata']['fields']['published']['default'] ? '1' : ''),
                'fd_member' => (int) ($this->Member->id),
                'fd_member_group' => (int) ($this->Member->groups[0]),
                'fd_user' => (int) ($this->User->id),
                'fd_user_group' => (int) ($this->User->groups[0]),
            ];

            // Keep some values from existing record on frontend editing
            if ($intOldId > 0) {
                $arrSet['form'] = $arrOldFormdata['form'];
                $arrSet['be_notes'] = $arrOldFormdata['be_notes'];
                $arrSet['fd_member'] = $arrOldFormdata['fd_member'];
                $arrSet['fd_member_group'] = $arrOldFormdata['fd_member_group'];
                if ((int) ($this->Member->id) > 0) {
                    $arrSet['fd_member'] = (int) ($this->Member->id);
                    if (1 === \count($this->Member->groups) && (int) ($this->Member->groups[0]) > 0) {
                        $arrSet['fd_member_group'] = (int) ($this->Member->groups[0]);
                    }
                } else {
                    $arrSet['fd_member'] = 0;
                }
                $arrSet['fd_user'] = $arrOldFormdata['fd_user'];
                $arrSet['fd_user_group'] = $arrOldFormdata['fd_user_group'];

                // Set published to value of old record, if no default value is defined
                if (!isset($GLOBALS['TL_DCA']['tl_formdata']['fields']['published']['default'])) {
                    $arrSet['published'] = $arrOldFormdata['published'];
                }
            }
            $implodearrSet = implode(', ', $arrSet);

            // Store formdata: Update or insert and delete
            if ($blnFEedit && \strlen($arrListing['efg_fe_keep_id'])) {
                $intNewId = $intOldId;
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'UPDATE tl_formdata '.$implodearrSet.' WHERE id='.$intOldId);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'DELETE FROM tl_formdata_details WHERE pid='.$intOldId);
                \Database::getInstance()->prepare('UPDATE tl_formdata %s WHERE id=?')->set($arrSet)->execute($intOldId);
                \Database::getInstance()->prepare('DELETE FROM tl_formdata_details WHERE pid=?')->execute($intOldId);
            } else {
                $objNewFormdata = \Database::getInstance()->prepare('INSERT INTO tl_formdata %s')->set($arrSet)->execute();
                $intNewId = $objNewFormdata->insertId;
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'INSERT INTO tl_formdata '.$implodearrSet.' WHERE id='.$intOldId.' intNewId '.$intNewId);

                // Update related comments
                if (\in_array('comments', \ModuleLoader::getActive(), true)) {
                    \Database::getInstance()->prepare("UPDATE tl_comments %s WHERE `source` = 'tl_formdata' AND parent=?")
                        ->set(['parent' => $intNewId])
                        ->execute($intOldId)
                ;
                }
            }

            // Store details data
            foreach ($arrFormFields as $k => $arrField) {
                $strType = $arrField['formfieldType'];
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "store Details Data $k type $strType");
                $strVal = '';

                if (\in_array($strType, $arrFFstorable, true)) {
                    if ($blnStoreOptionsValue) {
                        $arrField['eval']['efgStoreValues'] = true;
                    } else {
                        $arrField['eval']['efgStoreValues'] = false;
                    }

                    // Set rgxp 'date' for field type 'calendar' if not set
                    if ('calendar' === $strType) {
                        if (!isset($arrField['rgxp'])) {
                            $arrField['rgxp'] = 'date';
                        }
                    }
                    // Set rgxp 'date' and dateFormat for field type 'xdependentcalendarfields'
                    elseif ('xdependentcalendarfields' === $strType) {
                        $arrField['rgxp'] = 'date';
                        $arrField['dateFormat'] = $arrField['xdateformat'];
                    }

                    $strVal = $this->Formdata->preparePostValueForDatabase($arrSubmitted[$k], $arrField, $arrFiles[$k]);
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "$k strVal $strVal");

                    // Special treatment for type upload
                    // Keep old file on frontend editing, if no new file has been uploaded
                    if ('upload' === $strType) {
                        if ($intOldId) {
                            if (!$arrFiles[$k]['name']) {
                                if (\strlen($arrOldFormdataDetails[$k]['value'])) {
                                    $strVal = $arrOldFormdataDetails[$k]['value'];
                                }
                            }
                        }
                    }

                    if (isset($arrSubmitted[$k]) || ('upload' === $strType && \strlen($strVal))) {
                        // Prepare data
                        $arrFieldSet = [
                            'pid' => $intNewId,
                            'sorting' => $arrField['sorting'],
                            'tstamp' => $timeNow,
                            'ff_id' => $arrField['id'],
                            'ff_name' => $arrField['name'],
                            'value' => $strVal,
                        ];

                        $objNewFormdataDetails = \Database::getInstance()
                            ->prepare('INSERT INTO tl_formdata_details %s')
                            ->set($arrFieldSet)
                            ->execute()
                        ;
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "store Data in tl_formdata_detailsData value $strVal ");
                    }
                }
            }

            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'blnFEedit '.$blnFEedit);
            // Delete old record after frontend editing
            if ($blnFEedit) {
                if (!isset($arrListing['efg_fe_keep_id']) || '1' !== $arrListing['efg_fe_keep_id']) {
                    if ($intNewId > 0 && (int) $intOldId > 0 && (int) $intNewId !== (int) $intOldId) {
                        \Database::getInstance()->prepare('DELETE FROM tl_formdata_details WHERE pid=?')
                            ->execute($intOldId)
                        ;
                        \Database::getInstance()->prepare('DELETE FROM tl_formdata WHERE id=?')
                            ->execute($intOldId)
                        ;
                    }
                }
                $strRedirectTo = preg_replace('/\?.*$/', '', \Environment::get('request'));
            }

            // Auto-generate alias
            $strAlias = $this->Formdata->generateAlias($arrOldFormdata['alias'], $arrForm['title'], $intNewId);
            if (isset($strAlias) && \strlen($strAlias)) {
                $arrUpd = ['alias' => $strAlias];
                \Database::getInstance()->prepare('UPDATE tl_formdata %s WHERE id=?')
                    ->set($arrUpd)
                    ->execute($intNewId)
                ;
            }
        }

        // Store data in the session to display on confirmation page
        unset($_SESSION['EFP']['FORMDATA']);
        $blnSkipEmptyFields = ($arrForm['confirmationMailSkipEmpty']) ? true : false;

        foreach ($arrFormFields as $k => $arrField) {
            $strType = $arrField['formfieldType'];
            $strVal = '';
            if (\in_array($strType, $arrFFstorable, true)) {
                $strVal = $this->Formdata->preparePostValueForMail($arrSubmitted[$k], $arrField, $arrFiles[$k], $blnSkipEmptyFields);
            }

            $_SESSION['EFP']['FORMDATA'][$k] = $strVal;
        }
        $_SESSION['EFP']['FORMDATA']['_formId_'] = $arrForm['id'];
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'formFields stroed in Session ');

        // Confirmation Mail
        if ($blnFEedit && !$arrForm['sendConfirmationMailOnFrontendEditing']) {
            $arrForm['sendConfirmationMail'] = false;
        }

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'sendConfirmationMail '.$arrForm['sendConfirmationMail']);
        if ($arrForm['sendConfirmationMail']) {
            $objMailProperties = new \stdClass();
            $objMailProperties->subject = '';
            $objMailProperties->sender = '';
            $objMailProperties->senderName = '';
            $objMailProperties->replyTo = '';
            $objMailProperties->recipients = [];
            $objMailProperties->messageText = '';
            $objMailProperties->messageHtmlTmpl = '';
            $objMailProperties->messageHtml = '';
            $objMailProperties->attachments = [];
            $objMailProperties->skipEmptyFields = false;

            $objMailProperties->skipEmptyFields = ($arrForm['confirmationMailSkipEmpty']) ? true : false;

            // Set the sender as given in form configuration
            [$senderName, $sender] = \StringUtil::splitFriendlyEmail($arrForm['confirmationMailSender']);
            $objMailProperties->sender = $sender;
            $objMailProperties->senderName = $senderName;

            // Set the 'reply to' address, if given in form configuration
            if (!empty($arrForm['confirmationMailReplyto'])) {
                [$replyToName, $replyTo] = \StringUtil::splitFriendlyEmail($arrForm['confirmationMailReplyto']);
                $objMailProperties->replyTo = (\strlen($replyToName) ? $replyToName.' <'.$replyTo.'>' : $replyTo);
            }

            // Set recipient(s)
            $recipientFieldName = $arrForm['confirmationMailRecipientField'];
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'confirmationMailRecipientField Name '.$confirmationMailRecipientField);

            $varRecipient = $arrSubmitted[$recipientFieldName];

            if (\is_array($varRecipient)) {
                $arrRecipient = $varRecipient;
            } else {
                $arrRecipient = trimsplit(',', $varRecipient);
            }

            if (!empty($arrForm['confirmationMailRecipient'])) {
                $varRecipient = $arrForm['confirmationMailRecipient'];
                $arrRecipient = array_merge($arrRecipient, trimsplit(',', $varRecipient));
            }
            $arrRecipient = array_filter(array_unique($arrRecipient));

            if (!empty($arrRecipient)) {
                foreach ($arrRecipient as $kR => $recipient) {
                    [$recipientName, $recipient] = \StringUtil::splitFriendlyEmail($this->replaceInsertTags($recipient, false));
                    $arrRecipient[$kR] = (\strlen($recipientName) ? $recipientName.' <'.$recipient.'>' : $recipient);
                }
            }
            $objMailProperties->recipients = $arrRecipient;

            // Check if we want custom attachments... (Thanks to Torben Schwellnus)
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Check if we want custom addConfirmationMailAttachments '.$arrForm['addConfirmationMailAttachments']);
            if ($arrForm['addConfirmationMailAttachments']) {
                if ($arrForm['confirmationMailAttachments']) {
                    $arrCustomAttachments = deserialize($arrForm['confirmationMailAttachments'], true);

                    if (!empty($arrCustomAttachments)) {
                        foreach ($arrCustomAttachments as $varFile) {
                            $objFileModel = \FilesModel::findById($varFile);
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Check if we want custom addConfirmationMailAttachments file '.$varFile);

                            if (null !== $objFileModel) {
                                $objFile = new \File($objFileModel->path);
                                if ($objFile->size) {
                                    $objMailProperties->attachments[\System::getContainer()->getParameter('kernel.project_dir').'/'.$objFile->path] = [
                                        'file' => \System::getContainer()->getParameter('kernel.project_dir').'/'.$objFile->path,
                                        'name' => $objFile->basename,
                                        'mime' => $objFile->mime, ];
                                }
                            }
                        }
                    }
                }
            }

            $objMailProperties->subject = \StringUtil::decodeEntities($arrForm['confirmationMailSubject']);
            $objMailProperties->messageText = \StringUtil::decodeEntities($arrForm['confirmationMailText']);
            $objMailProperties->messageHtmlTmpl = $arrForm['confirmationMailTemplate'];

            // Replace Insert tags and conditional tags
            $objMailProperties = $this->Formdata->prepareMailData($objMailProperties, $arrSubmitted, $arrFiles, $arrForm, $arrFormFields);
            // Send Mail
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len objMailProperties->recipients '.count($objMailProperties->recipients));
            $blnConfirmationSent = false;

            if (!empty($objMailProperties->recipients)) {
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vor new Email');
                $objMail = new Email();

                $objMail->from = $objMailProperties->sender;

                if (!empty($objMailProperties->senderName)) {
                    $objMail->fromName = $objMailProperties->senderName;
                }

                if (!empty($objMailProperties->replyTo)) {
                    $objMail->replyTo($objMailProperties->replyTo);
                }

                $objMail->subject = $objMailProperties->subject;

                if (!empty($objMailProperties->attachments)) {
                    foreach ($objMailProperties->attachments as $strFile => $varParams) {
                        $strContent = file_get_contents($varParams['file'], false);
                        $objMail->attachFileFromString($strContent, $varParams['name'], $varParams['mime']);
                    }
                }

                if (!empty($objMailProperties->messageText)) {
                    $objMail->text = $objMailProperties->messageText;
                }

                if (!empty($objMailProperties->messageHtml)) {
                    $objMail->html = $objMailProperties->messageHtml;
                }

                foreach ($objMailProperties->recipients as $recipient) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vor sendTo to formular sender Recipient '.$recipient);
                    $objMail->sendTo($recipient);
                    $blnConfirmationSent = true;
                }
            }

            if ($blnConfirmationSent && isset($intNewId) && (int) $intNewId > 0) {
                $arrUpd = ['confirmationSent' => '1', 'confirmationDate' => $timeNow];
                $res = \Database::getInstance()->prepare('UPDATE tl_formdata %s WHERE id=?')
                    ->set($arrUpd)
                    ->execute($intNewId)
                ;
            }
        }

        // Information (formatted) Mail
        if ($blnFEedit && !$arrForm['sendFormattedMailOnFrontendEditing']) {
            $arrForm['sendFormattedMail'] = false;
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'sendFormattedMail '.$arrForm['sendFormattedMail']);

        if ($arrForm['sendFormattedMail']) {
            $objMailProperties = new \stdClass();
            $objMailProperties->subject = '';
            $objMailProperties->sender = '';
            $objMailProperties->senderName = '';
            $objMailProperties->replyTo = '';
            $objMailProperties->recipients = [];
            $objMailProperties->messageText = '';
            $objMailProperties->messageHtmlTmpl = '';
            $objMailProperties->messageHtml = '';
            $objMailProperties->attachments = [];
            $objMailProperties->skipEmptyFields = false;

            $objMailProperties->skipEmptyFields = ($arrForm['formattedMailSkipEmpty']) ? true : false;

            // Set the admin e-mail as "from" address
            $objMailProperties->sender = $GLOBALS['TL_ADMIN_EMAIL'];
            $objMailProperties->senderName = $GLOBALS['TL_ADMIN_NAME'];

            // Get 'reply to' address, if form contains field named 'email'
            if (isset($arrSubmitted['email']) && !empty($arrSubmitted['email']) && !\is_bool(strpos($arrSubmitted['email'], '@'))) {
                $replyTo = $arrSubmitted['email'];
                // add name
                if (isset($arrSubmitted['name']) && !empty($arrSubmitted['name'])) {
                    $replyTo = '"'.$arrSubmitted['name'].'" <'.$arrSubmitted['email'].'>';
                }
                $objMailProperties->replyTo = $replyTo;
            }
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Set recipient(s) '.$arrForm['sendFormattedMail']);

            // Set recipient(s)
            $varRecipient = $arrForm['formattedMailRecipient'];
            if (\is_array($varRecipient)) {
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len varRecipient  '.count($varRecipient));
                $arrRecipient = $varRecipient;
            } else {
                $arrRecipient = trimsplit(',', $varRecipient);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len varRecipient  '.$varRecipient);
            }
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len arrRecipient vor filter '.count($arrRecipient));
            $arrRecipient = array_filter(array_unique($arrRecipient));
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len arrRecipient nach filter '.count($arrRecipient));

            if (!empty($arrRecipient)) {
                foreach ($arrRecipient as $kR => $recipient) {
                    [$recipientName, $recipient] = \StringUtil::splitFriendlyEmail($this->replaceInsertTags($recipient, false));
                    $arrRecipient[$kR] = (\strlen($recipientName) ? $recipientName.' <'.$recipient.'>' : $recipient);
                }
            }
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len arrRecipient nach schleife '.count($arrRecipient));
            $objMailProperties->recipients = $arrRecipient;

            // Check if we want custom attachments... (Thanks to Torben Schwellnus)
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Check if we want custom addFormattedMailAttachments '.$arrForm['sendFormattedMail']);
            if ($arrForm['addFormattedMailAttachments']) {
                if ($arrForm['formattedMailAttachments']) {
                    $arrCustomAttachments = deserialize($arrForm['formattedMailAttachments'], true);

                    if (\is_array($arrCustomAttachments)) {
                        foreach ($arrCustomAttachments as $varFile) {
                            $objFileModel = \FilesModel::findById($varFile);
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Check if we want custom addFormattedMailAttachments file '.$varFile);

                            if (null !== $objFileModel) {
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'objFileModel->path '.$objFileModel->path);
                                $objFile = new \File($objFileModel->path);
                                if ($objFile->size) {
                                    $objMailProperties->attachments[\System::getContainer()->getParameter('kernel.project_dir').'/'.$objFile->path] = [
                                        'file' => \System::getContainer()->getParameter('kernel.project_dir').'/'.$objFile->path,
                                        'name' => $objFile->basename,
                                        'mime' => $objFile->mime,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            $objMailProperties->subject = \StringUtil::decodeEntities($arrForm['formattedMailSubject']);
            $objMailProperties->messageText = \StringUtil::decodeEntities($arrForm['formattedMailText']);
            $objMailProperties->messageHtmlTmpl = $arrForm['formattedMailTemplate'];

            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Replace Insert tags and conditional tags');
            // Replace Insert tags and conditional tags
            $objMailProperties = $this->Formdata->prepareMailData($objMailProperties, $arrSubmitted, $arrFiles, $arrForm, $arrFormFields);

            // Send Mail
            $blnInformationSent = false;

            if (!empty($objMailProperties->recipients)) {
                $objMail = new Email();
                $objMail->from = $objMailProperties->sender;
                if (!empty($objMailProperties->senderName)) {
                    $objMail->fromName = $objMailProperties->senderName;
                }

                if (!empty($objMailProperties->replyTo)) {
                    $objMail->replyTo($objMailProperties->replyTo);
                }

                $objMail->subject = $objMailProperties->subject;

                if (!empty($objMailProperties->attachments)) {
                    foreach ($objMailProperties->attachments as $strFile => $varParams) {
                        $strContent = file_get_contents($varParams['file'], false);
                        $objMail->attachFileFromString($strContent, $varParams['name'], $varParams['mime']);
                    }
                }

                if (!empty($objMailProperties->messageText)) {
                    $objMail->text = $objMailProperties->messageText;
                }

                if (!empty($objMailProperties->messageHtml)) {
                    $objMail->html = $objMailProperties->messageHtml;
                }

                foreach ($objMailProperties->recipients as $recipient) {
                    try {
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vor sendTo Recipient '.$recipient);
                        $objMail->sendTo($recipient);
                        $blnInformationSent = true;
                    }
                    //catch exception
                    catch (Exception $e) {
                        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'sendmail ERROR '.$e->getMessage());
                        $this->log('sendmail ERROR '.$e->getMessage(), __METHOD__, TL_errot);
                    }
                }
            }
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Redirect after frontend editing');

        // Redirect after frontend editing
        if ($blnFEedit) {
            if (!empty($strRedirectTo)) {
                $strRed = preg_replace(['/\/'.$this->strFormdataDetailsKey.'\/'.\Input::get($this->strFormdataDetailsKey).'/i', '/'.$this->strFormdataDetailsKey.'='.\Input::get($this->strFormdataDetailsKey).'/i', '/act=edit/i'], ['', '', ''], $strUrl).(!empty($strUrlParams) ? '?'.$strUrlParams : '');
                \Controller::redirect($strRed);
            }
        }
    }

    /* 
     * Callback function to display submitted data on confirmation page
     */
    public function processConfirmationContent($strContent)
    {
        $arrSubmitted = @$_SESSION['EFP']['FORMDATA'];   // ??? PBD

        // fix: after submission of normal single page form array $_SESSION['EFP']['FORMDATA'] is empty
        if (null === $arrSubmitted || (1 === \count($arrSubmitted) && array_keys($arrSubmitted) === ['_formId_'])) {
            $arrSubmitted = @$_SESSION['FORM_DATA'];          // ??? PBD
            $arrSubmitted['_formId_'] = @$_SESSION['EFP']['FORMDATA'];  // ??? PBD
        }

        $blnProcess = false;
        if (preg_match('/\{\{form::/si', $strContent)) {
            $blnProcess = true;
        }

        if (!empty($arrSubmitted) && isset($arrSubmitted['_formId_']) && $blnProcess) {
            $blnSkipEmptyFields = false;

            $objSkip = \Database::getInstance()->prepare('SELECT confirmationMailSkipEmpty FROM tl_form WHERE id=?')->execute($arrSubmitted['_formId_']);
            if (1 === $objSkip->confirmationMailSkipEmpty) {
                $blnSkipEmptyFields = true;
            }

            $this->import('Formdata');
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'bearbeite FORM id:  '.$arrSubmitted['_formId_']);

            $arrFormFields = $this->Formdata->getFormfieldsAsArray((int) ($arrSubmitted['_formId_']));
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'bearbeite FORM len FormFields :  '.count($arrFormFields));

            preg_match('/<body[^>]*?>.*?<\/body>/si', $strContent, $arrMatch);

            if (!empty($arrMatch)) {
                for ($m = 0; $m < \count($arrMatch); ++$m) {
                    $strTemp = $arrMatch[$m];
                    // aus {{form::name}}  wird __BRCL__form::name__BRCR__ 
                    $strTemp = preg_replace(['/\{\{/', '/\}\}/'], ['__BRCL__', '__BRCR__'], $strTemp);
                    $blnEval = $this->Formdata->replaceConditionTags($strTemp);

                    // Replace tags
                    $tags = [];
                    preg_match_all('/__BRCL__.*?__BRCR__/si', $strTemp, $tags);

                    // Replace tags of type {{form::<form field name>}}
                    // .. {{form::fieldname?label=Label for this field: }}
                    foreach ($tags[0] as $tag) {
                        $elements = explode('::', preg_replace(['/^__BRCL__/i', '/__BRCR__$/i'], ['', ''], $tag));
                        switch (strtolower($elements[0])) {
                            // Form
                            case 'form':
                                $strKey = $elements[1];
                                $arrKey = explode('?', $strKey);
                                $strKey = $arrKey[0];

                                $arrTagParams = null;
                                if (isset($arrKey[1]) && !empty($arrKey[1]) && \strlen($arrKey[1])) {
                                    $arrTagParams = $this->Formdata->parseInsertTagParams($tag);
                                }

                                $arrField = $arrFormFields[$strKey];

                                $strLabel = '';
                                $strVal = '';
                                if ($arrTagParams && !empty($arrTagParams['label']) && \strlen($arrTagParams['label'])) {
                                    $strLabel = $arrTagParams['label'];
                                }

                                $strVal = $arrSubmitted[$strKey];
                                if (\is_array($strVal)) {
                                    $strVal = implode(', ', $strVal);
                                }

                                if (isset($strVal) && \strlen($strVal)) {
                                    $strVal = nl2br($strVal);
                                }

                                if (!isset($arrTagParams['attachment'])) {
                                    $strVal = '';
                                }

                                if (isset($strVal) && !\strlen($strVal) && $blnSkipEmptyFields) {
                                    $strLabel = '';
                                }

                                $strTemp = str_replace($tag, $strLabel.$strVal, $strTemp);
                                break;
                        }
                    }

                    $strTemp = preg_replace(['/__BRCL__/', '/__BRCR__/'], ['{{', '}}'], $strTemp);

                    // Eval the code
                    if ($blnEval) {
                        $strTemp = $this->Formdata->evalConditionTags($strTemp);
                    }

                    $strContent = str_replace($arrMatch[$m], $strTemp, $strContent);
                }
            }
        }
        //EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'bearbeite FORM return :  '.$strContent);

        return $strContent;
    }
}
