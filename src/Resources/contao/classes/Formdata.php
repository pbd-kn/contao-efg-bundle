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

//use PBDKN\Efgco4\Resources\contao\classes\EfgLog;

/**
 * Class Formdata.
 *
 * Provide methods to handle data stored in tables tl_formdata and tl_formdata_details.
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class Formdata extends \Contao\Frontend
{
    /**
     * Items in tl_form, all forms marked to store data in tl_formdata.
     *
     * @param array
     */
    protected $arrStoringForms;

    protected $arrFormsDcaKey;
    protected $arrFormdataDetailsKey;

    /**
     * Types of form fields with storable data.
     *
     * @var array
     */
    protected $arrFFstorable = [];

    protected $arrBaseFields;

    /**
     * Mapping of frontend form fields to backend widgets.
     *
     * @var array
     */
    protected $arrMapTL_FFL = [];

    protected $strFdDcaKey;

    protected $arrListingPages;

    protected $arrSearchableListingPages;

    protected $arrMembers;

    protected $arrUsers;

    protected $arrMemberGroups;

    protected $arrUserGroups;

    public function __construct()
    {
        parent::__construct();
        EfgLog::setEfgDebugmode('form');

        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "do '".\Input::get('do')."' act '".\Input::get('act')."'");

        // Types of form fields with storable data
        $this->arrFFstorable = [
            'sessionText', 'sessionOption', 'sessionCalculator',
            'hidden', 'text', 'calendar', 'xdependentcalendarfields', 'password', 'textarea',
            'select', 'efgImageSelect', 'condition', 'conditionalselect', 'countryselect', 'fp_preSelectMenu', 'efgLookupSelect',
            'radio', 'efgLookupRadio', 'cm_alternative',
            'checkbox', 'efgLookupCheckbox',
            'upload', 'fileTree',
        ];

        if (!empty($GLOBALS['efg_co4']['storable_fields'])) {
            $this->arrFFstorable = array_filter(array_unique(array_merge($this->arrFFstorable, $GLOBALS['efg_co4']['storable_fields'])));
        }

        // Mapping of frontend form fields to backend widgets for not identical types
        $this->arrMapTL_FFL = [
            'hidden' => 'text',
            'upload' => 'fileTree',
            'efgImageSelect' => 'fileTree',
            'sessionText' => 'text',
            'sessionOption' => 'checkbox',
            'sessionCalculator' => 'text',
            'condition' => 'checkbox',
            'conditionalselect' => 'select',
            'countryselect' => 'select',
            'fp_preSelectMenu' => 'select',
        ];

        if (!empty($GLOBALS['efg_co4']['BE_FFL'])) {
            foreach ($GLOBALS['efg_co4']['BE_FFL'] as $strTL_FFL => $strBE_FFL) {
                $this->arrMapTL_FFL[$strTL_FFL] = $strBE_FFL;
            }
        }
        $this->getStoringForms();
        $this->arrBaseFields = array_filter(array_diff(\Database::getInstance()->getFieldNames('tl_formdata'), ['PRIMARY']));
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'end');
    }

    /**
     * Return an object property.
     *
     * @param string $strKey
     */
    public function __get($strKey)
    {
        switch ($strKey) {
            case 'FdDcaKey':
                return $this->strFdDcaKey;
                break;

            case 'arrFFstorable':
                return $this->arrFFstorable;
                break;

            case 'arrMapTL_FFL':
                return $this->arrMapTL_FFL;
                break;

            case 'arrBaseFields':
                return $this->arrBaseFields;
                break;

            case 'arrStoringForms':
                return $this->arrStoringForms;
                break;

            case 'arrFormsDcaKey':
                return $this->arrFormsDcaKey;
                break;

            case 'arrMembers':
                return $this->getMembers();
                break;

            case 'arrUsers':
                return $this->getUsers();
                break;

            case 'arrMemberGroups':
                return $this->getMemberGroups();
                break;

            case 'arrUserGroups':
                return $this->getUserGroups();
                break;

            default:
                return parent::__get($strKey);
                break;
        }
    }

    /**
     * Autogenerate an alias if it has not been set yet.
     *
     * If no form field is configured to be used as alias field
     * first form field of type text not using rgxp=email/date/datim/time will be used
     *
     * @param string $varValue     Optional given alias
     * @param string $strFormTitle The title of the form
     * @param int    $intRecId     ID of the formdata record
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateAlias($varValue = null, $strFormTitle = null, $intRecId = null)
    {
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "input varValue=$varValue, strFormTitle=$strFormTitle, intRecId=$intRecId ");

        $autoAlias = false;
        $strAliasField = '';

        if (null === $strFormTitle) {
            return '';
        }
        if (0 === (int) $intRecId) {
            return '';
        }

        // Get field used to build alias
        $objForm = \Database::getInstance()->prepare('SELECT id, efgAliasField FROM tl_form WHERE title=?')
            ->limit(1)
            ->execute($strFormTitle)
        ;

        if ($objForm->numRows) {
            if (\strlen($objForm->efgAliasField)) {
                $strAliasField = $objForm->efgAliasField;
            }
        }

        if ('' === $strAliasField) {
            $objFormField = \Database::getInstance()->prepare("SELECT ff.name FROM tl_form f, tl_form_field ff WHERE (f.id=ff.pid) AND f.title=? AND ff.type=? AND ff.rgxp NOT IN ('email','date','datim','time') ORDER BY sorting")
                ->limit(1)
                ->execute($strFormTitle, 'text')
            ;

            if ($objFormField->numRows) {
                $strAliasField = $objFormField->name;
            }
        }

        // Generate alias if there is none
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "varValue '$varValue' strAliasField '$strAliasField'");
        if (empty($varValue)) {
            if (!empty($strAliasField)) {
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strAliasField not empty');
                $autoAlias = true;
                $strAliasFieldSuffix = '';

                // Additional key in mode editAll
                if ('editAll' === \Input::get('act')) {
                    $strAliasFieldSuffix = '_'.$intRecId;
                }

                // Get value from post
                if (isset($_POST[$strAliasField.$strAliasFieldSuffix])) {
                    $varValue = standardize(\Input::post($strAliasField.$strAliasFieldSuffix));
                } else {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'query: SELECT `value` FROM tl_formdata_details WHERE pid='.$intRecId.' AND ff_name='.$strAliasField);
                    $objValue = \Database::getInstance()->prepare('SELECT `value` FROM tl_formdata_details WHERE pid=? AND ff_name=?')
                        ->limit(1)
                        ->execute($intRecId, $strAliasField)
                    ;

                    $varValue = standardize($objValue->value);
                    if (empty($varValue)) {
                        $autoAlias = true;
                    }
                }
            } else {
                $autoAlias = true;
            }
        }

        $objAlias = \Database::getInstance()->prepare('SELECT id FROM tl_formdata WHERE alias=? AND id != ?')
            ->execute($varValue, $intRecId)
        ;
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'query: SELECT id FROM tl_formdata WHERE alias='.$varValue.' AND id != '.$intRecId);

        // Check whether the alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'alias "'.$varValue.'" Formid "'.$intRecId.'" existiert');
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= (!empty($varValue) ? '.' : '').$intRecId;
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "return $varValue");

        return $varValue;
    }

    /**
     * Add formdata details to the indexer.
     *
     * @param array $arrPages Array of URLs
     * @param int   $intRoot  ID of root page
     *
     * @return array
     */
    public function getSearchablePages($arrPages, $intRoot = 0)
    {
        $arrRoot = [];

        if ($intRoot > 0) {
            $arrRoot = \Database::getInstance()->getChildRecords($intRoot, 'tl_page', true);
        }

        $this->getSearchableListingPages();

        $arrProcessed = [];

        if (!empty($this->arrSearchableListingPages)) {
            $this->loadDataContainer('fd_feedback');

            foreach ($this->arrSearchableListingPages as $pageId => $arrParams) {
                if (!empty($arrRoot) && !\in_array($pageId, $arrRoot, true)) {
                    continue;
                }

                // Do not add if list condition contains insert tags
                if (!empty($arrParams['list_where'])) {
                    if (false !== strpos($arrParams['list_where'], '{{')) {
                        continue;
                    }
                }

                // Do not add if no listing details fields are defined
                if (empty($arrParams['list_info'])) {
                    continue;
                }

                if (empty($arrParams['list_formdata'])) {
                    continue;
                }

                if (!isset($arrProcessed[$pageId])) {
                    $arrProcessed[$pageId] = false;

                    $strForm = '';
                    $strFormsKey = substr($arrParams['list_formdata'], \strlen('fd_'));
                    if (isset($this->arrFormsDcaKey[$strFormsKey])) {
                        $strForm = $this->arrFormsDcaKey[$strFormsKey];
                    }

                    $pageAlias = (!empty($arrParams['alias']) ? $arrParams['alias'] : null);

                    if (!empty($strForm)) {
                        $strFormdataDetailsKey = 'details';
                        if (!empty($arrParams['formdataDetailsKey'])) {
                            $strFormdataDetailsKey = $arrParams['formdataDetailsKey'];
                        }

                        // Determine domain
                        if ((int) $pageId > 0) {
                            $domain = \Environment::get('base');
                            $objParent = \PageModel::findWithDetails($pageId);

                            if (!empty($objParent->domain)) {
                                $domain = (\Environment::get('ssl') ? 'https://' : 'http://').$objParent->domain.TL_PATH.'/';
                            }
                        }
                        $arrProcessed[$pageId] = $domain.$this->generateFrontendUrl($objParent->row(), '/'.$strFormdataDetailsKey.'/%s', $objParent->language);
                    }

                    if (false === $arrProcessed[$pageId]) {
                        continue;
                    }

                    $strUrl = $arrProcessed[$pageId];

                    // Prepare conditions
                    $strQuery = 'SELECT id,alias FROM tl_formdata f';
                    $strWhere = ' WHERE form=?';

                    if (!empty($arrParams['list_where'])) {
                        $arrListWhere = [];
                        $arrListConds = preg_split('/(\sAND\s|\sOR\s)/si', $arrParams['list_where'], -1, PREG_SPLIT_DELIM_CAPTURE);

                        foreach ($arrListConds as $strListCond) {
                            if (preg_match('/\sAND\s|\sOR\s/si', $strListCond)) {
                                $arrListWhere[] = $strListCond;
                            } else {
                                $arrListCond = preg_split('/([\s!=><]+)/', $strListCond, -1, PREG_SPLIT_DELIM_CAPTURE);
                                $strCondField = $arrListCond[0];

                                unset($arrListCond[0]);
                                if (\in_array($strCondField, $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['detailFields'], true)) {
                                    $arrListWhere[] = '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$strCondField.'" AND pid=f.id ) '.implode('', $arrListCond);
                                }
                                if (\in_array($strCondField, $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['baseFields'], true)) {
                                    $arrListWhere[] = $strCondField.implode('', $arrListCond);
                                }
                            }
                        }
                        $strListWhere = (!empty($arrListWhere)) ? '('.implode('', $arrListWhere).')' : '';
                        $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').$strListWhere;
                    }

                    $strQuery .= $strWhere;

                    // Add details pages to the indexer
                    $objData = \Database::getInstance()->prepare($strQuery)
                        ->execute($strForm)
                    ;

                    while ($objData->next()) {
                        $arrPages[] = sprintf($strUrl, ((!empty($objData->alias) && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objData->alias : $objData->id));
                    }
                }
            }
        }

        return $arrPages;
    }

    /**
     * Get all forms marked to store data in tl_formdata.
     */
    public function getStoringForms(): void
    {
        if (!$this->arrStoringForms) {
            // Get all forms marked to store data
//            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Read from DB');
            $objForms = \Database::getInstance()->prepare('SELECT id,title,alias,formID,useFormValues,useFieldNames,efgAliasField,efgDebugMode FROM tl_form WHERE storeFormdata=?')
                ->execute('1')
            ;

            while ($objForms->next()) {
                $strFormKey = (!empty($objForms->alias)) ? $objForms->alias : str_replace('-', '_', standardize($objForms->title));
                $this->arrStoringForms[$strFormKey] = $objForms->row();
                $this->arrFormsDcaKey[$strFormKey] = $objForms->title;
//                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "erzeugt fuer title[$strFormKey]".$objForms->title.' efgDebugMode '.$objForms->efgDebugMode);
            }
        }
    }

    /**
     * Get all forms marked to store data in tl_formdata.
     */
    public function removeFromStoringForm($strFormKey): void
    {
        if (isset($this->arrStoringForms[$strFormKey])) {
            unset($this->arrStoringForms[$strFormKey]);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "delete $strFormKey");
        }
    }

    /**
     * Return record from tl_formdata as Array('fd_base' => base fields from tl_formdata, 'fd_details' => detail fields from tl_formdata_details).
     *
     * @param int $intId ID of tl_formdata record
     */
    public function getFormdataAsArray($intId = 0)
    {
        $varReturn = [];

        if ($intId > 0) {
            $objFormdata = \Database::getInstance()->prepare('SELECT * FROM tl_formdata WHERE id=?')
                ->execute($intId)
            ;
            if (1 === $objFormdata->numRows) {
                $varReturn['fd_base'] = $objFormdata->fetchAssoc();

                $objFormdataDetails = \Database::getInstance()->prepare('SELECT * FROM tl_formdata_details WHERE pid=?')
                    ->execute($intId)
                ;
                if ($objFormdataDetails->numRows) {
                    $arrTemp = $objFormdataDetails->fetchAllAssoc();
                    foreach ($arrTemp as $k => $arr) {
                        $varReturn['fd_details'][$arr['ff_name']] = $arr;
                    }
                    unset($arrTemp);
                }
            }

            return $varReturn;
        }

        return false;
    }

    /**
     * Return form fields as associative array.
     *
     * @param int $intId ID of tl_form record
     */
    public function getFormfieldsAsArray($intId = 0)
    {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "formId $intId");

        $varReturn = [];

        if ($intId > 0) {
            /* liefert alle Felder, die for diese Form angelegt sind (pid) */
            $objFormFields = \Database::getInstance()->prepare('SELECT * FROM tl_form_field WHERE pid=? ORDER BY sorting ASC')
                ->execute($intId)
            ;

            while ($objFormFields->next()) {
                $varKey = (!empty($objFormFields->name) && !\in_array($objFormFields->name, array_keys($varReturn), true)) ? $objFormFields->name : $objFormFields->id;
                //EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "formFields varKey $varKey");
                $arrField = $objFormFields->row();

                // Set type of frontend widget
                $arrField['formfieldType'] = $arrField['type'];

                // Set type of backend widget
                if (isset($this->arrMapTL_FFL[$arrField['formfieldType']])) {
                    $arrField['inputType'] = $this->arrMapTL_FFL[$arrField['formfieldType']];
                } else {
                    $arrField['inputType'] = $arrField['type'];
                }

                $varReturn[$varKey] = $arrField;
            }

            return $varReturn;
        }

        return false;
    }

    /**
     * Get all members (FE).
     */
    public function getMembers()
    {
        if (null === $this->arrMembers) {
            $members = [];
            $objMembers = \Database::getInstance()->prepare("SELECT id, CONCAT(firstname,' ',lastname) AS name,`groups`,login,username,locked,disable,start,stop FROM tl_member ORDER BY name ASC")
                ->execute()
            ;
            $members[] = '-';
            if ($objMembers->numRows) {
                while ($objMembers->next()) {
                    $k = $objMembers->id;
                    $v = $objMembers->name;
                    $members[$k] = $v;
                }
            }
            $this->arrMembers = $members;
        }

        return $this->arrMembers;
    }

    /**
     * Get all users (BE).
     */
    public function getUsers()
    {
        if (null === $this->arrUsers) {
            $users = [];

            // Get all users mysql like groups
            $objUsers = \Database::getInstance()->prepare('SELECT id,username,name,locked,disable,start,stop,admin,`groups`,modules,inherit,fop FROM tl_user ORDER BY name ASC')
                ->execute()
            ;
            $users[] = '-';
            if ($objUsers->numRows) {
                while ($objUsers->next()) {
                    $k = $objUsers->id;
                    $v = $objUsers->name;
                    $users[$k] = $v;
                }
            }
            $this->arrUsers = $users;
        }

        return $this->arrUsers;
    }

    /**
     * Get all member groups (FE).
     */
    public function getMemberGroups()
    {
        if (null === $this->arrMemberGroups) {
            $groups = [];

            // Get all member groups
            $objGroups = \Database::getInstance()->prepare('SELECT id, `name` FROM tl_member_group ORDER BY `name` ASC')
                ->execute()
            ;
            $groups[] = '-';
            if ($objGroups->numRows) {
                while ($objGroups->next()) {
                    $k = $objGroups->id;
                    $v = $objGroups->name;
                    $groups[$k] = $v;
                }
            }
            $this->arrMemberGroups = $groups;
        }

        return $this->arrMemberGroups;
    }

    /**
     * Get all user groups (BE).
     */
    public function getUserGroups()
    {
        if (null === $this->arrUserGroups) {
            $groups = [];

            // Get all user groups
            $objGroups = \Database::getInstance()->prepare('SELECT id, `name` FROM tl_user_group ORDER BY `name` ASC')
                ->execute()
            ;
            $groups[] = '-';
            if ($objGroups->numRows) {
                while ($objGroups->next()) {
                    $k = $objGroups->id;
                    $v = $objGroups->name;
                    $groups[$k] = $v;
                }
            }
            $this->arrUserGroups = $groups;
        }

        return $this->arrUserGroups;
    }

    /**
     * Prepare post value for tl_formdata / tl_formdata_details DB record.
     *
     * @param string|array $varSubmitted Post value
     * @param array|bool   $arrField     Form field properties
     * @param bool         $varFile      File
     */
    public function preparePostValueForDatabase($varSubmitted = '', $arrField = false, $varFile = false)
    {
        if (!\is_array($arrField)) {
            return false;
        }

        $strType = $arrField['type'];
        if (TL_MODE === 'FE' && !empty($arrField['formfieldType'])) {
            $strType = $arrField['formfieldType'];
        } elseif (TL_MODE === 'BE' && !empty($arrField['inputType'])) {
            $strType = $arrField['inputType'];
        }

        $strVal = '';

        if (\in_array($strType, $this->arrFFstorable, true)) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strType '.$strType);
            switch ($strType) {
                case 'efgLookupCheckbox':
                case 'checkbox':
                case 'condition': // conditionalforms
                    $strSep = '';
                    $strVal = '';
                    $arrOptions = $this->prepareWidgetOptions($arrField);

                    $arrSel = [];
                    if (\is_string($varSubmitted)) {
                        $arrSel[] = $varSubmitted;
                    } elseif (\is_array($varSubmitted)) {
                        $arrSel = $varSubmitted;
                    }

                    foreach ($arrOptions as $o => $mxVal) {
                        if (\in_array($mxVal['value'], $arrSel, true)) {
                            if ('checkbox' === $strType && $arrField['eval']['efgStoreValues']) {
                                $strVal .= $strSep.$arrOptions[$o]['value'];
                            } else {
                                $strVal .= $strSep.$arrOptions[$o]['label'];
                            }
                            $strSep = (isset($arrField['eval']['csv'])) ? $arrField['eval']['csv'] : '|';
                        }
                    }

                    if ('' === $strVal) {
                        $strVal = $varSubmitted;
                        if (\is_array($strVal)) {
                            $strVal = implode(($arrField['eval']['csv'] ?? '|'), $strVal);
                        }
                    }
                    break;

                case 'efgLookupRadio':
                case 'radio':
                    $strVal = $varSubmitted;
                    $arrOptions = $this->prepareWidgetOptions($arrField);
                    foreach ($arrOptions as $o => $mxVal) {
                        if ($mxVal['value'] === $varSubmitted) {
                            if ('radio' === $strType && $arrField['eval']['efgStoreValues']) {
                                $strVal = $arrOptions[$o]['value'];
                            } else {
                                $strVal = $arrOptions[$o]['label'];
                            }
                        }
                    }
                    break;

                case 'efgLookupSelect':
                case 'conditionalselect':
                case 'countryselect':
                case 'fp_preSelectMenu':
                case 'select':
                case 'cm_alternative':
                    $strSep = '';
                    $strVal = '';
                    $arrOptions = $this->prepareWidgetOptions($arrField);

                    // select multiple
                    if (\is_array($varSubmitted)) {
                        foreach ($arrOptions as $o => $mxVal) {
                            if (\in_array($mxVal['value'], $varSubmitted, true)) {
                                if ($arrField['eval']['efgStoreValues'] && \in_array($strType, ['select', 'conditionalselect', 'countryselect', 'fp_preSelectMenu'], true)) {
                                    $strVal .= $strSep.$arrOptions[$o]['value'];
                                } else {
                                    $strVal .= $strSep.$arrOptions[$o]['label'];
                                }
                                $strSep = (isset($arrField['eval']['csv'])) ? $arrField['eval']['csv'] : '|';
                            }
                        }
                    }

                    // select single
                    if (\is_string($varSubmitted)) {
                        foreach ($arrOptions as $o => $mxVal) {
                            if ($mxVal['value'] === $varSubmitted) {
                                if ($arrField['eval']['efgStoreValues'] && \in_array($strType, ['select', 'conditionalselect', 'countryselect', 'fp_preSelectMenu'], true)) {
                                    $strVal = $arrOptions[$o]['value'];
                                } else {
                                    $strVal = $arrOptions[$o]['label'];
                                }
                            }
                        }
                    }
                    break;

                case 'efgImageSelect':
                    $strVal = '';
                    if (\is_array($varSubmitted)) {
                        $strVal = implode(($arrField['eval']['csv'] ?? '|'), $varSubmitted);
                    } elseif (\strlen($varSubmitted)) {
                        $strVal = $varSubmitted;
                    }
                    break;

                case 'upload':
                    $strVal = '';

                    if (!empty($varFile['name'])) {
                        if ($arrField['storeFile']) {
                            $varUploadFolder = $arrField['uploadFolder'];

                            if ($arrField['useHomeDir']) {
                                // Overwrite upload folder with user home directory
                                if (FE_USER_LOGGED_IN) {
                                    $this->import('FrontendUser', 'User');
                                    if ($this->User->assignDir && $this->User->homeDir) {
                                        $varUploadFolder = $this->User->homeDir;
                                    }
                                }
                            }

                            $objUploadFolder = \FilesModel::findById($varUploadFolder);

                            // The upload folder could not be found
                            if (null === $objUploadFolder) {
                                $this->log('Invalid upload folder ID '.$varUploadFolder.', file "'.$varFile['name'].'" could not been saved in file manager', __METHOD__, 'ERROR');
                            } else {
                                $strVal = $objUploadFolder->path.'/'.$varFile['name'];
                            }
                        } else {
                            // TODO: change field type (backend inputType) to text ?
                            $strVal = $varFile['name'];
                        }
                    }
                    break;

                case 'text':
                case 'calendar':
                case 'xdependentcalendarfields':
                    $strVal = $varSubmitted;
                    if (\is_string($strVal) && \strlen($strVal) && \in_array($arrField['rgxp'], ['date', 'time', 'datim'], true)) {
                        $strFormat = $GLOBALS['TL_CONFIG'][$arrField['rgxp'].'Format'];
                        if (!empty($arrField['dateFormat'])) {
                            $strFormat = $arrField['dateFormat'];
                        }
                        $objDate = new \Date($strVal, $strFormat);
                        $strVal = $objDate->tstamp;
                    } else {
                        $strVal = $varSubmitted;
                    }
                    break;

                default:
                    $strVal = $varSubmitted;
                    break;
            }

            if (\is_array($strVal)) {
                foreach ($strVal as $k => $value) {
                    $strVal[$k] = \StringUtil::decodeEntities($value);
                }
                $strVal = serialize($strVal);
            } else {
                $strVal = \StringUtil::decodeEntities($strVal);
            }
            
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strValstrType '.$strVal);
            return $strVal;
        }
        
        return \is_array($varSubmitted) ? serialize($varSubmitted) : $varSubmitted;
    }

    /**
     * Prepare value from CSV for tl_formdata / tl_formdata_details DB record.
     *
     * @param mixed      $varValue Field value from csv file
     * @param array|bool $arrField Form field properties
     */
    public function prepareImportValueForDatabase($varValue = '', $arrField = false)
    {
        if (!\is_array($arrField)) {
            return false;
        }

        $strType = $arrField['type'];
        if (TL_MODE === 'FE' && !empty($arrField['formfieldType'])) {
            $strType = $arrField['formfieldType'];
        } elseif (TL_MODE === 'BE' && !empty($arrField['inputType'])) {
            $strType = $arrField['inputType'];
        }

        $strVal = '';

        if (\in_array($strType, $this->arrFFstorable, true)) {
            switch ($strType) {
                case 'efgLookupCheckbox':
                case 'checkbox':
                case 'condition': // conditionalforms
                case 'efgLookupRadio':
                case 'radio':
                case 'efgLookupSelect':
                case 'efgImageSelect':
                case 'conditionalselect':
                case 'countryselect':
                case 'fp_preSelectMenu':
                case 'select':
                    if ($arrField['eval']['multiple']) {
                        $arrSel = [];
                        if (\strlen($varValue)) {
                            $arrSel = trimsplit('[,|]', $varValue);
                        }
                        $strVal = $arrSel;
                    } else {
                        $strVal = $varValue;
                    }
                    break;

                case 'text':
                case 'calendar':
                case 'xdependentcalendarfields':
                    $strVal = $varValue;
                    // Convert date formats into timestamps
                    if (\in_array($arrField['eval']['rgxp'], ['date', 'time', 'datim'], true)) {
                        if (is_numeric($strVal) && 10 === \strlen($strVal)) {
                            $strVal = (int) $strVal;
                        } elseif (\is_string($strVal) && \strlen($strVal)) {
                            $strFormat = $GLOBALS['TL_CONFIG'][$arrField['eval']['rgxp'].'Format'];
                            $objDate = new \Date($strVal, $strFormat);
                            $strVal = $objDate->tstamp;
                        }
                    }
                    break;

                case 'fileTree':
                case 'upload':
                case 'efgImageSelect':
                    $strVal = '';
                    if ($arrField['eval']['multiple']) {
                        $arrSel = [];
                        if (\strlen($varValue)) {
                            $arrVal = trimsplit('[,|]', $varValue);
                            if (!empty($arrVal)) {
                                foreach ($arrVal as $kVal => $mxVal) {
                                    if (\Validator::isUuid($mxVal) || is_numeric($mxVal)) {
                                        $objFileModel = \FilesModel::findById($mxVal);

                                        if ($objFileModel->path) {
                                            $arrSel[] = $objFileModel->path;
                                        }
                                    } else {
                                        $arrSel[] = $mxVal;
                                    }
                                }
                            }
                        }
                        $strVal = $arrSel;
                    } else {
                        if (\Validator::isUuid($varValue) || is_numeric($varValue)) {
                            $objFileModel = \FilesModel::findById($varValue);

                            if ($objFileModel->path) {
                                $strVal = $objFileModel->path;
                            }
                        } else {
                            $strVal = $varValue;
                        }
                    }
                    break;

                case 'hidden':
                case 'textarea':
                case 'password':
                default:
                    $strVal = $varValue;
                    break;
            }

            $varValue = $strVal;
        }

        if (\is_array($varValue)) {
            if ($arrField['eval']['multiple'] && isset($arrField['eval']['csv'])) {
                $varValue = implode($arrField['eval']['csv'], $varValue);
            } else {
                $varValue = serialize($varValue);
            }
        } elseif (\is_object($varValue)) {
            $varValue = serialize($varValue);
        } else {
            $varValue = \StringUtil::decodeEntities($varValue);
        }

        return $varValue;
    }

    /**
     * Prepare post value for mail / text.
     *
     * @param string|array $varSubmitted       Post value
     * @param array|bool   $arrField           Form field properties
     * @param array|bool   $varFile            File
     * @param bool         $blnSkipEmptyFields Skip empty values (do not return label of selected option if its value is empty)
     */
    public function preparePostValueForMail($varSubmitted = '', $arrField = false, $varFile = false, $blnSkipEmptyFields = false)
    {
        if (!\is_array($arrField)) {
            return false;
        }

        $strType = $arrField['type'];
        if (TL_MODE === 'FE' && !empty($arrField['formfieldType'])) {
            $strType = $arrField['formfieldType'];
        } elseif (TL_MODE === 'BE' && !empty($arrField['inputType'])) {
            $strType = $arrField['inputType'];
        }

        $strVal = '';

        if (isset($arrField['efgMailSkipEmpty'])) {
            $blnSkipEmptyFields = $arrField['efgMailSkipEmpty'];
        }

        if (\in_array($strType, $this->arrFFstorable, true)) {
            switch ($strType) {
                case 'efgLookupCheckbox':
                case 'checkbox':
                case 'condition': // conditionalforms
                    $strSep = '';
                    $strVal = '';
                    $arrOptions = $this->prepareWidgetOptions($arrField);

                    $arrSel = [];
                    if (\is_string($varSubmitted)) {
                        $arrSel[] = $varSubmitted;
                    } elseif (\is_array($varSubmitted)) {
                        $arrSel = $varSubmitted;
                    }

                    foreach ($arrOptions as $o => $mxVal) {
                        if ($blnSkipEmptyFields && !\strlen($mxVal['value'])) {
                            continue;
                        }

                        if (\in_array($mxVal['value'], $arrSel, true)) {
                            $strVal .= $strSep.$mxVal['label'];
                            $strSep = ', ';
                        }
                    }

                    if ('' === $strVal) {
                        $strVal = (\is_array($varSubmitted)) ? implode(', ', $varSubmitted) : $varSubmitted;
                    }
                    break;

                case 'efgLookupRadio':
                case 'radio':
                    $strVal = (\is_array($varSubmitted)) ? $varSubmitted[0] : $varSubmitted;
                    $arrOptions = $this->prepareWidgetOptions($arrField);
                    foreach ($arrOptions as $o => $mxVal) {
                        if ($mxVal['value'] === $varSubmitted) {
                            $strVal = $mxVal['label'];
                        }
                    }
                    break;

                case 'efgLookupSelect':
                case 'conditionalselect':
                case 'countryselect':
                case 'fp_preSelectMenu':
                case 'select':
                case 'cm_alternative': // cm_alternativeforms
                    $strSep = '';
                    $strVal = '';
                    $arrOptions = $this->prepareWidgetOptions($arrField);

                    // select multiple
                    if (\is_array($varSubmitted)) {
                        foreach ($arrOptions as $o => $mxVal) {
                            if ($blnSkipEmptyFields && !\strlen($mxVal['value'])) {
                                continue;
                            }

                            if (\in_array($mxVal['value'], $varSubmitted, true)) {
                                $strVal .= $strSep.$mxVal['label'];
                                $strSep = ', ';
                            }
                        }
                    }

                    // select single
                    elseif (\is_string($varSubmitted)) {
                        foreach ($arrOptions as $o => $mxVal) {
                            if ($blnSkipEmptyFields && !\strlen($mxVal['value'])) {
                                continue;
                            }

                            if ($mxVal['value'] === $varSubmitted) {
                                $strVal = $mxVal['label'];
                            }
                        }
                    }
                    break;

                case 'efgImageSelect':
                    $strVal = '';
                    if (\is_string($varSubmitted) && \strlen($varSubmitted)) {
                        $strVal = $varSubmitted;
                    } elseif (\is_array($varSubmitted)) {
                        $strVal = $varSubmitted;
                    }
                    break;

                case 'upload':
                    $strVal = '';
                    if (!empty($varFile['name'])) {
                        $strVal = $varFile['name'];
                    }
                    break;

                case 'password':
                case 'hidden':
                case 'text':
                case 'textarea':
                default:
                    $strVal = $varSubmitted;
                    break;
            }

            return (\is_string($strVal) && \strlen($strVal)) ? \StringUtil::decodeEntities($strVal) : $strVal;
        }

        return (\is_string($varSubmitted) && \strlen($varSubmitted)) ? \StringUtil::decodeEntities($varSubmitted) : $varSubmitted;
    }

    /**
     * Prepare database value for Mail / Text.
     *
     * @param mixed      $varValue Database value
     * @param array|bool $arrField Form field properties
     * @param array|bool $varFile  File
     */
    public function prepareDatabaseValueForMail($varValue = '', $arrField = false, $varFile = false)
    {
        if (!\is_array($arrField)) {
            return false;
        }

        $strType = $arrField['type'];
        if (TL_MODE === 'FE' && !empty($arrField['formfieldType'])) {
            $strType = $arrField['formfieldType'];
        } elseif (TL_MODE === 'BE' && !empty($arrField['inputType'])) {
            $strType = $arrField['inputType'];
        }

        $strVal = '';

        if (\in_array($strType, $this->arrFFstorable, true)) {
            switch ($strType) {
                case 'efgLookupCheckbox':
                case 'checkbox':
                case 'condition': // conditionalforms
                    $blnEfgStoreValues = ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['efgStoreValues'] ? true : false);

                    $strVal = '';
                    $arrSel = [];
                    $strSep = (isset($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['csv'])) ? $GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['csv'] : '|';

                    if (\is_string($varValue) && false !== strpos($varValue, $strSep)) {
                        $arrSel = explode($strSep, $varValue);
                    } else {
                        $arrSel = deserialize($varValue, true);
                    }

                    if (!empty($arrSel)) {
                        // Get options labels instead of values for mail / text
                        if ($blnEfgStoreValues && \is_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'])) {
                            foreach ($arrSel as $kSel => $vSel) {
                                foreach ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'] as $strOptsKey => $varOpts) {
                                    if (\is_array($varOpts)) {
                                        if (isset($varOpts[$vSel])) {
                                            $arrSel[$kSel] = $varOpts[$vSel];
                                            break;
                                        }
                                    } else {
                                        if ($strOptsKey === $vSel) {
                                            $arrSel[$kSel] = $varOpts;
                                            break;
                                        }
                                    }
                                }
                            }
                            $strVal = implode(', ', $arrSel);
                        } else {
                            $strVal = implode(', ', $arrSel);
                        }
                    }
                    break;

                case 'efgLookupRadio':
                case 'radio':
                    $blnEfgStoreValues = ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['efgStoreValues'] ? true : false);

                    $strVal = (\is_array($varValue) ? $varValue[0] : $varValue);

                    if ($blnEfgStoreValues && \is_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'])) {
                        foreach ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'] as $strOptsKey => $varOpts) {
                            if (\is_array($varOpts)) {
                                if (isset($varOpts[$strVal])) {
                                    $strVal = $varOpts[$strVal];
                                    break;
                                }
                            } else {
                                if ($strOptsKey === $strVal) {
                                    $strVal = $varOpts;
                                    break;
                                }
                            }
                        }
                    }
                    break;

                case 'efgLookupSelect':
                case 'conditionalselect':
                case 'countryselect':
                case 'fp_preSelectMenu':
                case 'select':
                    $blnEfgStoreValues = ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['efgStoreValues'] ? true : false);

                    $strVal = '';
                    $arrSel = [];
                    $strSep = (isset($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['csv']))
                        ? $GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['csv']
                        : '|';

                    if (\is_string($varValue) && false !== strpos($varValue, $strSep)) {
                        $arrSel = explode($strSep, $varValue);
                    } else {
                        $arrSel = deserialize($varValue, true);
                    }

                    if (!empty($arrSel)) {
                        // Get options labels instead of values for mail / text
                        if ($blnEfgStoreValues && \is_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'])) {
                            foreach ($arrSel as $kSel => $vSel) {
                                foreach ($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['options'] as $strOptsKey => $varOpts) {
                                    if (\is_array($varOpts)) {
                                        if (isset($varOpts[$vSel])) {
                                            $arrSel[$kSel] = $varOpts[$vSel];
                                            break;
                                        }
                                    } else {
                                        if ($strOptsKey === $vSel) {
                                            $arrSel[$kSel] = $varOpts;
                                            break;
                                        }
                                    }
                                }
                            }
                            $strVal = implode(', ', $arrSel);
                        } else {
                            $strVal = implode(', ', $arrSel);
                        }
                    }
                    break;

                case 'efgImageSelect':
                case 'fileTree':
                    $strVal = '';
                    $arrSel = [];

                    $strSep = (isset($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['csv']))
                        ? $GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['csv']
                        : '|';

                    if (\is_string($varValue) && false !== strpos($varValue, $strSep)) {
                        $arrSel = explode($strSep, $varValue);
                    } else {
                        $arrSel = deserialize($varValue, true);
                    }

                    if (!empty($arrSel)) {
                        $strVal = $arrSel;
                    }
                    break;

                case 'upload':
                    $strVal = '';
                    if (!empty($varFile['name'])) {
                        $strVal = $varFile['name'];
                    }
                    break;

                case 'password':
                case 'hidden':
                case 'text':
                case 'textarea':
                default:
                    $strVal = $varValue;

                    if ('date' === $GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['rgxp']) {
                        $strVal = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $strVal);
                    } elseif ('time' === $GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['rgxp']) {
                        $strVal = \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $strVal);
                    } elseif ('datim' === $GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['eval']['rgxp']
                        || \in_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrField['name']]['flag'], [5, 6, 7, 8, 9, 10], true)) {
                        $strVal = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $strVal);
                    }

                    break;
            }

            return (\is_string($strVal) && \strlen($strVal)) ? \StringUtil::decodeEntities($strVal) : $strVal;
        }

        return (\is_string($varValue) && \strlen($varValue)) ? \StringUtil::decodeEntities($varValue) : $varValue;
    }

    /**
     * Prepare database value from tl_formdata / tl_formdata_details for widget.
     *
     * @param mixed      $varValue Stored value
     * @param array|bool $arrField Form field properties (NOTE: set from dca or from tl_form_field, with differences in the structure)
     * @param mixed      $varFile  File
     */
    public function prepareDatabaseValueForWidget($varValue = '', $arrField = false, $varFile = false)
    {
        if (!\is_array($arrField)) {
            return false;
        }

        $strType = $arrField['type'];
        if (TL_MODE === 'FE' && !empty($arrField['formfieldType'])) {
            $strType = $arrField['formfieldType'];
        } elseif (TL_MODE === 'BE' && !empty($arrField['inputType'])) {
            $strType = $arrField['inputType'];
        }
        if ('Logo' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['label'][0]) {
            //$this->log("PBD Formdata prepareDatabaseValueForWidget arrField da label " . $arrField['label'][0] . " type $strType varValue $varValue", __METHOD__, 'TL_GENERAL');
        }

        $varVal = $varValue;

        if (\in_array($strType, $this->arrFFstorable, true)) {
            switch ($strType) {
                case 'efgLookupCheckbox':
                case 'checkbox':
                case 'condition': // conditionalforms
                case 'efgLookupRadio':
                case 'radio':
                case 'efgLookupSelect':
                case 'conditionalselect':
                case 'countryselect':
                case 'fp_preSelectMenu':
                case 'select':
                    $strSep = (isset($arrField['eval']['csv'])) ? $arrField['eval']['csv'] : '|';

                    if ($arrField['options']) {
                        $arrOptions = deserialize($arrField['options']);
                    } else {
                        $arrOptions = $this->prepareWidgetOptions($arrField);
                    }

                    if (\is_string($varVal)) {
                        $varVal = explode($strSep, $varVal);
                    }

                    if (\is_array($arrOptions)) {
                        $arrTempOptions = [];
                        foreach ($arrOptions as $sK => $mxVal) {
                            $arrTempOptions[$mxVal['value']] = $mxVal['label'];
                        }
                    }

                    if (\is_array($varVal)) {
                        foreach ($varVal as $k => $v) {
                            $sNewVal = array_search($v, $arrTempOptions, true);
                            if ($sNewVal) {
                                $varVal[$k] = $sNewVal;
                            }
                        }
                    }
                    break;

                case 'efgImageSelect':
                case 'fileTree':
                    $strSep = (isset($arrField['eval']['csv'])) ? $arrField['eval']['csv'] : '|';

                    if (\is_string($varVal) && false !== strpos($varVal, $strSep)) {
                        $varVal = explode($strSep, $varVal);
                    } elseif (\is_array($varVal)) {
                        $varVal = array_filter($varVal);
                    } elseif (\strlen($varVal)) {
                        $varVal = deserialize($varValue);
                    }

                    if (!empty($varVal)) {
                        //$this->log("PBD Formdata prepareDatabaseValueForWidget 2 varVal nicht leer varVal $varVal", __METHOD__, 'TL_GENERAL');
                        if (\is_array($varVal)) {
                            //$this->log('PBD Formdata prepareDatabaseValueForWidget 2 varval da array', __METHOD__, 'TL_GENERAL');
                            foreach ($varVal as $key => $varFile) {
                                $objFileModel = null;

                                if (\Validator::isUuid($varFile) || is_numeric($varFile)) {
                                    $objFileModel = \FilesModel::findById($varFile);
                                } else {
                                    $objFileModel = \FilesModel::findOneBy('path', $varFile);
                                }

                                if (null !== $objFileModel) {
                                    $varVal[$key] = (TL_MODE === 'FE' ? $objFileModel->path : $objFileModel->uuid);
                                }
                            }
                        } elseif (\is_string($varVal)) {
                            $objFileModel = null;

                            if (\Validator::isUuid($varVal) || is_numeric($varVal)) {
                                $objFileModel = \FilesModel::findById($varVal);
                            } else {
                                $objFileModel = \FilesModel::findOneBy('path', $varVal);
                            }

                            if (null !== $objFileModel) {
                                $varVal = (TL_MODE === 'FE' ? $objFileModel->path : $objFileModel->uuid);
                            }
                        }
                    }
                    break;

                case 'cm_alternative': // cm_alternativeforms
                    if ($arrField['options']) {
                        $arrOptions = deserialize($arrField['options']);
                    } else {
                        $arrOptions = $this->prepareWidgetOptions($arrField);
                    }

                    foreach ($arrOptions as $sK => $mxVal) {
                        if (\is_array($mxVal) && $mxVal['label'] === $varVal) {
                            $varVal = $mxVal['value'];
                            break;
                        }
                        if ($mxVal === $varVal) {
                            $varVal = $sK;
                            break;
                        }
                    }
                    break;

                case 'upload':
                    $varVal = '';
                    if (\strlen($varValue)) {
                        if ($arrField['storeFile']) {
                            $strVal = $varValue;
                        } else {
                            $strVal = $varValue;
                        }
                        $varVal = $strVal;
                    }
                    break;

                case 'text':
                case 'calendar':
                case 'xdependentcalendarfields':
                    // NOTE: different array structure in Backend (set by dca) and Frontend (set from tl_form_field)
                    // .. in Frontend: one-dimensional array like $arrField['rgxp'], $arrField['dateFormat']
                    // .. in Backend: multidimensional array like $arrField['eval']['rgxp']
                    if ($arrField['rgxp'] && \in_array($arrField['rgxp'], ['date', 'datim', 'time'], true)) {
                        if ($varVal) {
                            if ('date' === $arrField['rgxp']) {
                                $varVal = \Date::parse((!empty($arrField['dateFormat']) ? $arrField['dateFormat'] : $GLOBALS['TL_CONFIG']['dateFormat']), $varVal);
                            } elseif ('datim' === $arrField['rgxp']) {
                                $varVal = \Date::parse((!empty($arrField['dateFormat']) ? $arrField['dateFormat'] : $GLOBALS['TL_CONFIG']['datimFormat']), $varVal);
                            } elseif ('time' === $arrField['rgxp']) {
                                $varVal = \Date::parse((!empty($arrField['dateFormat']) ? $arrField['dateFormat'] : $GLOBALS['TL_CONFIG']['timeFormat']), $varVal);
                            }
                        }
                    } else {
                        $varVal = $varValue;
                    }
                    break;

                default:
                    $varVal = $varValue;
                    break;
            }

            return $varVal;
        }

        return $varVal;
    }

    /**
     * Prepare widget options array
     * Used in backend and frontend.
     *
     * @param array|bool $arrField Form field properties
     *
     * @return array DCA/widget options
     */
    public function prepareWidgetOptions($arrField = false)
    {
        if (!\is_array($arrField)) {
            return false;
        }
        $strType = $arrField['type'];
        if (TL_MODE === 'FE' && !empty($arrField['formfieldType'])) {
            $strType = $arrField['formfieldType'];
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'TL_MODE '.TL_MODE.' formfieldType '.$arrField['formfieldType']);
        } elseif (TL_MODE === 'BE' && !empty($arrField['inputType'])) {
            $strType = $arrField['inputType'];
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'TL_MODE '.TL_MODE.' type '.$arrField['type'].' strType '.$strType);

        $arrOptions = [];

        switch ($strType) {
            case 'efgLookupCheckbox':
            case 'efgLookupRadio':
            case 'efgLookupSelect':
                // Get efgLookupOptions: array('lookup_field' => TABLENAME.FIELDNAME, 'lookup_val_field' => TABLENAME.FIELDNAME, 'lookup_where' => CONDITION, 'lookup_sort' => ORDER BY)
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len arrfield '.count($arrField));
                foreach ($arrField as $k => $v) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "arrField[$k]: $v");
                }
                $arrLookupOptions = deserialize($arrField['efgLookupOptions']);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len arrLookupOptions '.count($arrLookupOptions));
                foreach ($arrLookupOptions as $k => $v) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "arrLookupOptions[$k]: $v");
                }
                $strLookupField = $arrLookupOptions['lookup_field'];
                $strLookupValField = ((isset($arrLookupOptions['lookup_val_field']) && \strlen($arrLookupOptions['lookup_val_field']))) ? $arrLookupOptions['lookup_val_field'] : null;
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strLookupValField '.$strLookupValField);

                $strLookupWhere = \StringUtil::decodeEntities($arrLookupOptions['lookup_where']);
                if (!empty($strLookupWhere)) {
                    $strLookupWhere = $this->replaceInsertTags($strLookupWhere, false);
                }
                if (isset($strLookupField)) {
                  EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strLookupField '.$strLookupField);
                  $arrLookupField = explode('.', $strLookupField);
                  $sqlLookupTable = $arrLookupField[0];
                  $sqlLookupField = $arrLookupField[1];
                } else {
                  EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strLookupField not set ');
                  // so nicht continue;
                }
                $sqlLookupValField = (isset($strLookupValField) && \strlen($strLookupValField)) ? substr($strLookupValField, strpos($strLookupValField, '.') + 1) : null;

                $sqlLookupIdField = 'id';
                $sqlLookupWhere = (!empty($strLookupWhere) ? ' WHERE '.$strLookupWhere : '');
                $sqlLookupOrder = $arrLookupField[0].'.'.$arrLookupField[1];
                if (!empty($arrLookupOptions['lookup_sort'])) {
                    $sqlLookupOrder = $arrLookupOptions['lookup_sort'];
                }

                $arrOptions = [];

                // Handle lookup formdata
                if ('fd_' === substr($sqlLookupTable, 0, 3)) {
                    $strFormKey = $this->arrFormsDcaKey[substr($sqlLookupTable, 3)];

                    $sqlLookupTable = 'tl_formdata f, tl_formdata_details fd';
                    $sqlLookupIdField = 'f.id';
                    $sqlLookupWhere = " WHERE (f.id=fd.pid AND f.form='".$strFormKey."' AND ff_name='".$arrLookupField[1]."')";

                    $arrDetailFields = [];
                    if (!empty($strLookupWhere) || !empty($arrLookupOptions['lookup_sort'])) {
                        $objDetailFields = \Database::getInstance()->prepare("SELECT DISTINCT(ff.`name`) FROM tl_form f, tl_form_field ff WHERE f.storeFormdata=? AND (f.id=ff.pid) AND ff.`type` IN ('".implode("','", $this->arrFFstorable)."')")
                            ->execute('1')
                        ;
                        if ($objDetailFields->numRows) {
                            $arrDetailFields = $objDetailFields->fetchEach('name');
                        }
                    }

                    if (!empty($strLookupWhere)) {
                        // Special treatment for fields in tl_formdata_details
                        $arrPattern = [];
                        $arrReplace = [];
                        foreach ($arrDetailFields as $strDetailField) {
                            $arrPattern[] = '/\b'.$strDetailField.'\b/i';
                            $arrReplace[] = '(SELECT value FROM tl_formdata_details fd WHERE (fd.pid=f.id AND ff_name=\''.$strDetailField.'\'))';
                        }
                        $sqlLookupWhere .= (\strlen($sqlLookupWhere) ? ' AND ' : ' WHERE ').'('.preg_replace($arrPattern, $arrReplace, $strLookupWhere).')';
                    }
                    $sqlLookupField = '(SELECT value FROM tl_formdata_details fd WHERE (fd.pid=f.id AND ff_name=\''.$arrLookupField[1].'\') ) AS `'.$arrLookupField[1].'`';

                    if (!empty($arrLookupOptions['lookup_sort'])) {
                        // Special treatment for fields in tl_formdata_details
                        $arrPattern = [];
                        $arrReplace = [];
                        foreach ($arrDetailFields as $strDetailField) {
                            $arrPattern[] = '/\b'.$strDetailField.'\b/i';
                            $arrReplace[] = '(SELECT value FROM tl_formdata_details fd WHERE (fd.pid=f.id AND ff_name=\''.$strDetailField.'\'))';
                        }
                        $sqlLookupOrder = preg_replace($arrPattern, $arrReplace, str_replace($arrLookupField[0].'.', '', $arrLookupOptions['lookup_sort']));
                    } else {
                        $sqlLookupOrder = '(SELECT value FROM tl_formdata_details fd WHERE (fd.pid=f.id AND ff_name=\''.$arrLookupField[1].'\'))';
                    }
                }

                // Handle lookup calendar events
                if ('tl_calendar_events' === $sqlLookupTable) {
                    $sqlLookupOrder = '';

                    // Handle order (max. 2 fields)
                    // .. default startTime ASC
                    $arrSortKeys = [['field' => 'startTime', 'order' => 'ASC'], ['field' => 'startTime', 'order' => 'ASC']];
                    if (!empty($arrLookupOptions['lookup_sort'])) {
                        $sqlLookupOrder = $arrLookupOptions['lookup_sort'];
                        $arrSortOn = trimsplit(',', $arrLookupOptions['lookup_sort']);
                        $arrSortKeys = [];
                        foreach ($arrSortOn as $strSort) {
                            $arrSortParam = explode(' ', $strSort);
                            if (!isset($arrSortParam[1])) $arrSortParam[1] = 'ASC';        // default 
                            $arrSortKeys[] = ['field' => $arrSortParam[0], 'order' => ('DESC' === strtoupper($arrSortParam[1]) ? 'DESC' : 'ASC')];
                        }
                    }

                    $sqlLookupWhere = (!empty($strLookupWhere) ? '('.$strLookupWhere.')' : '');

                    $strReferer = @$this->getReferer();            // ??? PBD 

                    // If form is placed on an events detail page, automatically add restriction to event(s)
                    $ev = \Input::get('events');
                    if (isset($ev) && \strlen(\Input::get('events'))) {
                        if (is_numeric(\Input::get('events'))) {
                            $sqlLookupWhere .= (!empty($sqlLookupWhere) ? ' AND ' : '').' tl_calendar_events.id='.(int) (\Input::get('events')).' ';
                        } elseif (\is_string(\Input::get('events'))) {
                            $sqlLookupWhere .= (!empty($sqlLookupWhere) ? ' AND ' : '')." tl_calendar_events.alias='".\Input::get('events')."' ";
                        }
                    }
                    // If linked from event reader page
                    if (strpos($strReferer, 'event-reader/events/') || strpos($strReferer, '&events=')) {
                        if (strpos($strReferer, 'events/')) {
                            $strEvents = substr($strReferer, strrpos($strReferer, '/') + 1);
                        } elseif (strpos($strReferer, '&events=')) {
                            $strEvents = substr($strReferer, strpos($strReferer, '&events=') + \strlen('&events='));
                        }

                        if (is_numeric($strEvents)) {
                            $sqlLookupWhere .= (\strlen($sqlLookupWhere) ? ' AND ' : '').' tl_calendar_events.id='.(int) $strEvents.' ';
                        } elseif (\is_string($strEvents)) {
                            $strEvents = str_replace('.html', '', $strEvents);
                            $sqlLookupWhere .= (!empty($sqlLookupWhere) ? ' AND ' : '')." tl_calendar_events.alias='".$strEvents."' ";
                        }
                    }

                    $sqlLookup = 'SELECT tl_calendar_events.* FROM tl_calendar_events, tl_calendar WHERE (tl_calendar.id=tl_calendar_events.pid) '.(!empty($sqlLookupWhere) ? ' AND ('.$sqlLookupWhere.')' : '').(\strlen($sqlLookupOrder) ? ' ORDER BY '.$sqlLookupOrder : '');

                    $objEvents = \Database::getInstance()->prepare($sqlLookup)->execute();

                    $arrEvents = [];

                    if ($objEvents->numRows) {
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'anz Events '.$objEvents->numRows);

                        while ($arrEvent = $objEvents->fetchAssoc()) {
                            $intDate = $arrEvent['startDate'];
                            $intStart = time();
                            $intEnd = time() + 60 * 60 * 24 * 178; // max. half year
                            $span = \Calendar::calculateSpan($arrEvent['startTime'], $arrEvent['endTime']);
                            $strTime = '';

                            $strTime .= date($GLOBALS['TL_CONFIG']['dateFormat'], (int)$arrEvent['startDate']);
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'event datFormat '.$GLOBALS['TL_CONFIG']['dateFormat'].' startTime '.$arrEvent['startDate']);

                            if ($arrEvent['addTime']) {
                                if ($span > 0) {
                                    $strTime .= ' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['startTime']).' - '.date($GLOBALS['TL_CONFIG']['dateFormat'], (int)$arrEvent['endTime']).' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['endTime']);
                                } elseif ($arrEvent['startTime'] === $arrEvent['endTime']) {
                                    $strTime .= ' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['startTime']);
                                } else {
                                    $strTime .= ' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['startTime']).' - '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['endTime']);
                                }
                            } else {
                                if ($span > 1) {
                                    $strTime .= ' - '.date($GLOBALS['TL_CONFIG']['dateFormat'], (int)$arrEvent['endTime']);
                                }
                            }
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strime '.$strTime);

                            if ($sqlLookupValField) {
                                // $arrEvents[$arrEvent[$sqlLookupValField].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'sqlLookupValfield count'.\count($arrSortKeys));
                                if (\count($arrSortKeys) >= 2) {
                                    $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent[$arrSortKeys[1]['field']]][] = [
                                        'value' => $arrEvent[$sqlLookupValField].'@'.$strTime,
                                        'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : ''),
                                    ];
                                } else {
                                    $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent['startTime']][] = [
                                        'value' => $arrEvent[$sqlLookupValField].'@'.$strTime,
                                        'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : ''),
                                    ];
                                }
                            } else {
                                // $arrEvents[$arrEvent['id'].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'NO sqlLookupValfield count'.\count($arrSortKeys));
                                if (\count($arrSortKeys) >= 2) {
                                    $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent[$arrSortKeys[1]['field']]][] = [
                                        'value' => $arrEvent[$sqlLookupValField].'@'.$strTime,
                                        'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : ''),
                                    ];
                                } else {
                                    $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent['startTime']][] = [
                                        'value' => $arrEvent[$sqlLookupValField].'@'.$strTime,
                                        'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : ''),
                                    ];
                                }
                            }

                            // Recurring events
                            if ($arrEvent['recurring']) {
                                $count = 0;
                                $arrRepeat = deserialize($arrEvent['repeatEach']);
                                $blnSummer = date('I', (int)$arrEvent['startTime']);
                                $intEnd = time() + 60 * 60 * 24 * 178; // max. 1/2 Year

                                while ($arrEvent['endTime'] < $intEnd) {
                                    if ($arrEvent['recurrences'] > 0 && $count++ >= $arrEvent['recurrences']) {
                                        break;
                                    }

                                    $arg = $arrRepeat['value'];
                                    $unit = $arrRepeat['unit'];

                                    if (1 === $arg) {
                                        $unit = substr($unit, 0, -1);
                                    }

                                    $strtotime = '+ '.$arg.' '.$unit;

                                    $arrEvent['startTime'] = strtotime($strtotime, $arrEvent['startTime']);
                                    $arrEvent['endTime'] = strtotime($strtotime, $arrEvent['endTime']);

                                    if ($arrEvent['startTime'] >= $intStart || $arrEvent['endTime'] <= $intEnd) {
                                        $strTime = '';
                                        $strTime .= date($GLOBALS['TL_CONFIG']['dateFormat'], (int)$arrEvent['startTime']);

                                        if ($arrEvent['addTime']) {
                                            if ($span > 0) {
                                                $strTime .= ' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['startTime']).' - '.date($GLOBALS['TL_CONFIG']['dateFormat'], (int)$arrEvent['endTime']).' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['endTime']);
                                            } elseif ($arrEvent['startTime'] === $arrEvent['endTime']) {
                                                $strTime .= ' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['startTime']);
                                            } else {
                                                $strTime .= ' '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['startTime']).' - '.date($GLOBALS['TL_CONFIG']['timeFormat'], (int)$arrEvent['endTime']);
                                            }
                                        }

                                        if ($sqlLookupValField) {
                                            // $arrEvents[$arrEvent[$sqlLookupValField].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
                                            if (\count($arrSortKeys) >= 2) {
                                                $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent[$arrSortKeys[1]['field']]][] = ['value' => $arrEvent[$sqlLookupValField].'@'.$strTime, 'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : '')];
                                            } else {
                                                $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent['startTime']][] = ['value' => $arrEvent[$sqlLookupValField].'@'.$strTime, 'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : '')];
                                            }
                                        } else {
                                            // $arrEvents[$arrEvent['id'].'@'.$strTime] = $arrEvent[$arrLookupField[1]] . (strlen($strTime) ? ', ' . $strTime : '');
                                            if (\count($arrSortKeys) >= 2) {
                                                $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent[$arrSortKeys[1]['field']]][] = ['value' => $arrEvent[$sqlLookupValField].'@'.$strTime, 'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : '')];
                                            } else {
                                                $arrEvents[$arrEvent[$arrSortKeys[0]['field']]][$arrEvent['startTime']][] = ['value' => $arrEvent[$sqlLookupValField].'@'.$strTime, 'label' => $arrEvent[$arrLookupField[1]].(\strlen($strTime) ? ', '.$strTime : '')];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Sort events
                        foreach ($arrEvents as $k => $arr) {
                            if (isset($arrSortKeys[1]['order'])) {
                            if ('DESC' === $arrSortKeys[1]['order']) {
                                krsort($arrEvents[$k]);
                            } else {
                                ksort($arrEvents[$k]);
                            }
                            } else {
                                ksort($arrEvents[$k]);
                            }
                        }
                        if ('DESC' === $arrSortKeys[0]['order']) {
                            krsort($arrEvents);
                        } else {
                            ksort($arrEvents);
                        }
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'set arrOptions');

                        // Set options
                        foreach ($arrEvents as $k1 => $arr1) {
                            foreach ($arr1 as $k2 => $arr2) {
                                foreach ($arr2 as $k3 => $arr3) {
                                    $arrOptions[] = $arr3;
                                    foreach ($arr3 as $k1=>$v1) {EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "setting arr3[$k1]:$v1");}
                                }
                            }
                        }

                        if (1 === \count($arrOptions)) {
                            $blnDoNotAddEmptyOption = true;
                        }

                        // Include blank option
                        if ('efgLookupSelect' === $strType) {
                            if (isset($blnDoNotAddEmptyOption)&&!$blnDoNotAddEmptyOption) {
                                array_unshift($arrOptions, ['value' => '', 'label' => '-']);
                            }
                        }
                    }
                    foreach ($arrOptions as $k=>$v) {
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "arrOptions[$k]");
                        foreach ($v as $k1=>$v1) {EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "  arr[$k1]:$v1");}
                    }
                    return $arrOptions;
                }

                // Normal lookup table or formdata lookup table

                    $sqlLookup = 'SELECT '.$sqlLookupIdField.(!empty($sqlLookupField) ? ', ' : '').$sqlLookupField.(!empty($sqlLookupValField) ? ', ' : '').$sqlLookupValField.' FROM '.$sqlLookupTable.$sqlLookupWhere.(!empty($sqlLookupOrder) ? ' ORDER BY '.$sqlLookupOrder : '');

                    if (!empty($sqlLookupTable)) {
                        $objOptions = \Database::getInstance()->prepare($sqlLookup)->execute();
                    }
                    if ($objOptions->numRows) {
                        $arrOptions = [];
                        while ($arrOpt = $objOptions->fetchAssoc()) {
                            if ($sqlLookupValField) {
                                $arrOptions[$arrOpt[$sqlLookupValField]] = $arrOpt[$arrLookupField[1]];
                            } else {
                                $arrOptions[$arrOpt['id']] = $arrOpt[$arrLookupField[1]];
                            }
                        }
                    }

                $arrTempOptions = [];
                // Include blank option
                if ('efgLookupSelect' === $strType) {
                    if (!$blnDoNotAddEmptyOption) {
                        $arrTempOptions[] = ['value' => '', 'label' => '-'];
                    }
                }

                foreach ($arrOptions as $sK => $sV) {
                    $strKey = (string) $sK;
                    $arrTempOptions[] = ['value' => $strKey, 'label' => $sV];
                }
                $arrOptions = $arrTempOptions;

                break;

            case 'countryselect': // countryselectmenu
                $arrCountries = $this->getCountries();
                $arrTempOptions = [];
                foreach ($arrCountries as $strKey => $strVal) {
                    $arrTempOptions[] = ['value' => $strKey, 'label' => $strVal];
                }
                $arrOptions = $arrTempOptions;

                break;

            case 'condition': // conditionalforms
                $arrOptions = [['value' => '1', 'label' => $arrField['label']]];
                break;

            case 'cm_alternative': // cm_alternativeforms
                $arrTempOptions = [];
                if (!\is_array($arrField['options'])) {
                    $arrField['options'] = [$arrField['cm_alternativelabel'], $arrField['cm_alternativelabelelse']];
                }
                foreach ($arrField['options'] as $strKey => $strVal) {
                    $arrTempOptions[] = ['value' => $strKey, 'label' => $strVal];
                }
                $arrOptions = $arrTempOptions;
                break;

            default:
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'default '.$strType);

                if ($arrField['options']) {
                    $arrOptions = deserialize($arrField['options']);
                } else {
                    $strClass = $GLOBALS['TL_FFL'][$arrField['type']];
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strClass '.$strClass);
                    if (class_exists($strClass)) {
                        $objWidget = new $strClass($arrField);

                        if ($objWidget instanceof \FormSelectMenu || $objWidget instanceof \FormCheckbox || $objWidget instanceof \FormRadioButton) {
                            // HOOK: load form field callback
                            if (isset($GLOBALS['TL_HOOKS']['loadFormField']) && \is_array($GLOBALS['TL_HOOKS']['loadFormField'])) {
                                foreach ($GLOBALS['TL_HOOKS']['loadFormField'] as $callback) {
                                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'hook loadFormField '.$callback[0]);
                                    $this->import($callback[0]);
                                    $objWidget = $this->{$callback[0]}->{$callback[1]}($objWidget, $arrField['pid'], []); //Aenderung PBD
                                }
                            }
                            $arrOptions = $objWidget->options;
                        }
                    }
                }
                break;
        }

        // Decode 'special chars', encoded by \Input::encodeSpecialChars (for example labels of checkbox options containing '(')
        $arrOptions = $this->decodeSpecialChars($arrOptions);
        /*
                foreach ($arrOptions as $k=>$v) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__,"arrOptions[$k]: $v" );
                    if (is_array($v)) {
                        foreach ($v as $k1=>$v1) {
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__,"    v[$k1]: $v1" );
                        }
                    }
                }
        */
        return $arrOptions;
    }

    /**
     * @param object $objMailProperties Mail properties
     * @param array  $arrSubmitted      Submitted data
     * @param array  $arrFiles          Array of files
     * @param array  $arrForm           Form configuration data
     * @param array  $arrFormFields     Form fields
     *
     * @return object
     */
    public function prepareMailData($objMailProperties, $arrSubmitted, $arrFiles, $arrForm, $arrFormFields)
    {
        $sender = $objMailProperties->sender;
        $senderName = $objMailProperties->senderName;
        $subject = $objMailProperties->subject;
        $arrRecipients = $objMailProperties->recipients;
        $replyTo = $objMailProperties->replyTo;
        $messageText = $objMailProperties->messageText;
        $messageHtml = $objMailProperties->messageHtml;
        $messageHtmlTmpl = $objMailProperties->messageHtmlTmpl;
        $attachments = $objMailProperties->attachments;

        $blnSkipEmptyFields = $objMailProperties->skipEmptyFields;
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "prepareMailData sender $sender subject $subject messageHtmlTmpl $messageHtmlTmpl len arrRecipients ".count($arrRecipients));

        if (\Validator::isUuid($messageHtmlTmpl) || (is_numeric($messageHtmlTmpl) && $messageHtmlTmpl > 0)) {
            $objFileModel = \FilesModel::findById($messageHtmlTmpl);
            if (null !== $objFileModel) {
                $messageHtmlTmpl = $objFileModel->path;
            }
        }
        if (isset($messageHtmlTmpl) && \strlen($messageHtmlTmpl) > 0) {
            $fileTemplate = new \File($messageHtmlTmpl);
            if ('text/html' === $fileTemplate->mime) {
                $messageHtml = $fileTemplate->getContent();
            }
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Prepare insert tags to handle separate from condition tags ');

        // Prepare insert tags to handle separate from 'condition tags'
        // aus {{form::name}}  wird __BRCL__form::name__BRCR__ 
        if (!empty($messageText)) {
            $messageText = preg_replace(['/\{\{/', '/\}\}/'], ['__BRCL__', '__BRCR__'], $messageText);
        }
        if (!empty($messageHtml)) {
            $messageHtml = preg_replace(['/\{\{/', '/\}\}/'], ['__BRCL__', '__BRCR__'], $messageHtml);
        }
        if (!empty($subject)) {
            $subject = preg_replace(['/\{\{/', '/\}\}/'], ['__BRCL__', '__BRCR__'], $subject);
        }
        if (!empty($sender)) {
            $sender = preg_replace(['/\{\{/', '/\}\}/'], ['__BRCL__', '__BRCR__'], $sender);
        }
        if (!empty($senderName)) {
            $senderName = preg_replace(['/\{\{/', '/\}\}/'], ['__BRCL__', '__BRCR__'], $senderName);
        }

        $blnEvalSender = $this->replaceConditionTags($sender);
        $blnEvalSenderName = $this->replaceConditionTags($senderName);
        $blnEvalSubject = $this->replaceConditionTags($subject);
        $blnEvalMessageText = $this->replaceConditionTags($messageText);
        $blnEvalMessageHtml = $this->replaceConditionTags($messageHtml);

        // Replace tags in messageText, messageHtml ...
        $tags = [];
        preg_match_all('/__BRCL__.*?__BRCR__/si', $messageText.$messageHtml.$subject.$sender.$senderName, $tags);

        // Replace tags of type {{form::<form field name>}}
        // .. {{form::uploadfieldname?attachment=true}}
        // .. {{form::fieldname?label=Label for this field: }}
        foreach ($tags[0] as $tag) {
            $elements = explode('::', preg_replace(['/^__BRCL__/i', '/__BRCR__$/i'], ['', ''], $tag));

            switch (strtolower($elements[0])) {
                // Formdata field
                case 'form':
                    $strKey = $elements[1];
                    [$strKey, $arrTagParams] = explode('?', $strKey);

                    if (!empty($arrTagParams)) {
                        $arrTagParams = $this->parseInsertTagParams($tag);
                    }

                    $arrField = $arrFormFields[$strKey];
                    $arrField['efgMailSkipEmpty'] = $blnSkipEmptyFields;

                    $strType = $arrField['formfieldType'];

                    if (!isset($arrFormFields[$strKey]) && \in_array($strKey, $this->arrBaseFields, true)) {
                        $arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey];
                        $strType = $arrField['inputType'];
                    }

                    $strLabel = '';
                    $strVal = '';

                    if ($arrTagParams && !empty($arrTagParams['label'])) {
                        $strLabel = $arrTagParams['label'];
                    }

                    if (\in_array($strType, $this->arrFFstorable, true)) {
                        if ('efgImageSelect' === $strType) {
                            $varText = [];
                            $varHtml = [];

                            if (TL_MODE === 'BE') {
                                $varVal = $this->prepareDatabaseValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
                            } else {
                                $varVal = $this->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
                            }

                            if (\is_string($varVal)) {
                                $varVal = [$varVal];
                            }
                            if (!empty($varVal)) {
                                foreach ($varVal as $strVal) {
                                    if (\strlen($strVal)) {
                                        $varText[] = \Environment::get('base').$strVal;
                                        $varHtml[] = '<img src="'.$strVal.'"'.$this->getEmptyTagEnd();
                                    }
                                }
                            }
                            if (empty($varText) && $blnSkipEmptyFields) {
                                $strLabel = '';
                            }

                            $messageText = str_replace($tag, $strLabel.implode(', ', $varText), $messageText);
                            $messageHtml = str_replace($tag, $strLabel.implode(' ', $varHtml), $messageHtml);
                        } elseif ('upload' === $strType) {
                            $varText = [];
                            $varHtml = [];

                            if (TL_MODE === 'BE') {
                                if (\strlen($arrSubmitted[$strKey])) {
                                    if (!\array_key_exists($strKey, $arrFiles)) {
                                        $objFile = new \File($arrSubmitted[$strKey]);
                                        if ($objFile->size) {
                                            $arrFiles[$strKey] = [
                                                'tmp_name' => \System::getContainer()->getParameter('kernel.project_dir').'/'.$objFile->path,
                                                'file' => \System::getContainer()->getParameter('kernel.project_dir').'/'.$objFile->path,
                                                'name' => $objFile->basename,
                                                'mime' => $objFile->mime,
                                            ];
                                        }
                                    }
                                }
                            }

                            // Add file as attachment
                            if ($arrTagParams && $arrTagParams['attachment']) {
                                if (!empty($arrFiles[$strKey]['tmp_name']) && is_file($arrFiles[$strKey]['tmp_name'])) {
                                    if (!isset($attachments[$arrFiles[$strKey]['tmp_name']])) {
                                        $attachments[$arrFiles[$strKey]['tmp_name']] = [
                                            'name' => $arrFiles[$strKey]['name'],
                                            'file' => $arrFiles[$strKey]['tmp_name'],
                                            'mime' => $arrFiles[$strKey]['type'],
                                        ];
                                    }
                                }
                                $varVal = [];
                            }
                            // File info
                            else {
                                if (TL_MODE === 'BE') {
                                    $varVal = $this->prepareDatabaseValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
                                } else {
                                    $varVal = $this->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
                                }
                            }

                            if (\is_string($varVal)) {
                                $varVal = [$varVal];
                            }
                            if (!empty($varVal)) {
                                foreach ($varVal as $strVal) {
                                    if (\strlen($strVal)) {
                                        $varText[] = basename($strVal);
                                        $varHtml[] = basename($strVal);
                                    }
                                }
                            }
                            if (empty($varText) && $blnSkipEmptyFields) {
                                $strLabel = '';
                            }

                            $subject = str_replace($tag, $strLabel.implode(', ', $varText), $subject);
                            $messageText = str_replace($tag, $strLabel.implode(', ', $varText), $messageText);
                            $messageHtml = str_replace($tag, $strLabel.implode(', ', $varHtml), $messageHtml);
                        } else {
                            if (TL_MODE === 'BE') {
                                $strVal = $this->prepareDatabaseValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
                            } else {
                                $strVal = $this->preparePostValueForMail($arrSubmitted[$strKey], $arrField, $arrFiles[$strKey]);
                            }

                            if (empty($strVal) && $blnSkipEmptyFields) {
                                $strLabel = '';
                            }
                            $messageText = str_replace($tag, $strLabel.$strVal, $messageText);

                            if (\is_string($strVal) && !empty($strVal) && !\is_bool(strpos($strVal, "\n"))) {
                                $strVal = $this->formatMultilineValue($strVal);
                            }
                            $messageHtml = str_replace($tag, $strLabel.$strVal, $messageHtml);
                        }
                    }

                    // Replace insert tags in subject
                    if (!empty($subject)) {
                        $subject = str_replace($tag, $strVal, $subject);
                    }

                    // Replace insert tags in sender
                    if (!empty($sender)) {
                        $sender = str_replace($tag, $strVal, $sender);
                    }

                    // Replace insert tags in senderName
                    if (!empty($senderName)) {
                        $senderName = str_replace($tag, $strVal, $senderName);
                    }

                    break;
            }
        }

        // Replace standard insert tags and eval condition tags
        // aus __BRCL__form::name__BRCR__ wird wieder __BRCL__form::name__BRCR__ 
        
        if (!empty($messageText)) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'messageText da');
            $messageText = preg_replace(['/__BRCL__/', '/__BRCR__/'], ['{{', '}}'], $messageText);
            $messageText = $this->replaceInsertTags($messageText, false);
            if ($blnEvalMessageText) {
                $messageText = $this->evalConditionTags($messageText, $arrSubmitted, $arrFiles, $arrForm);
            }
            $messageText = strip_tags($messageText);
            $messageText = html_entity_decode($messageText, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
        }
        if (!empty($messageHtml)) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'messageHtml da');
            $messageHtml = preg_replace(['/__BRCL__/', '/__BRCR__/'], ['{{', '}}'], $messageHtml);
            $messageHtml = $this->replaceInsertTags($messageHtml, false);
            if ($blnEvalMessageHtml) {
                $messageHtml = $this->evalConditionTags($messageHtml, $arrSubmitted, $arrFiles, $arrForm);
            }
        }

        // Replace insert tags in subject
        if (!empty($subject)) {
            $subject = preg_replace(['/__BRCL__/', '/__BRCR__/'], ['{{', '}}'], $subject);
            $subject = $this->replaceInsertTags($subject, false);
            if ($blnEvalSubject) {
                $subject = $this->evalConditionTags($subject, $arrSubmitted, $arrFiles, $arrForm);
            }
        }

        // Replace insert tags in sender
        if (!empty($sender)) {
            $sender = preg_replace(['/__BRCL__/', '/__BRCR__/'], ['{{', '}}'], $sender);
            $sender = trim($this->replaceInsertTags($sender, false));
            if ($blnEvalSender) {
                $sender = $this->evalConditionTags($sender, $arrSubmitted, $arrFiles, $arrForm);
            }
        }

        // Replace insert tags in senderName
        if (!empty($senderName)) {
            $senderName = preg_replace(['/__BRCL__/', '/__BRCR__/'], ['{{', '}}'], $senderName);
            $senderName = trim($this->replaceInsertTags($senderName, false));
            if ($blnEvalSenderName) {
                $senderName = $this->evalConditionTags($senderName, $arrSubmitted, $arrFiles, $arrForm);
            }
        }

        // Replace insert tags in replyTo
        if (!empty($replyTo)) {
            $replyTo = $this->replaceInsertTags($replyTo, false);
        }

        $objMailProperties->sender = $sender;
        $objMailProperties->senderName = $senderName;
        $objMailProperties->subject = $subject;
        $objMailProperties->recipients = $arrRecipients;
        $objMailProperties->replyTo = $replyTo;
        $objMailProperties->messageText = $messageText;
        $objMailProperties->messageHtmlTmpl = $messageHtmlTmpl;
        $objMailProperties->messageHtml = $messageHtml;
        $objMailProperties->attachments = $attachments;

        return $objMailProperties;
    }

    /**
     * Parse Insert tag params.
     *
     * @param string $strTag Insert tag
     *
     * @return array|null
     */
    public function parseInsertTagParams($strTag = '')
    {
        if ('' === $strTag) {
            return null;
        }
        if (false === strpos($strTag, '?')) {
            return null;
        }
        $strTag = str_replace(['{{', '}}', '__BRCL__', '__BRCR__'], ['', ''], $strTag);

        $arrTag = explode('?', $strTag);
        $strKey = $arrTag[0];
        if (isset($arrTag[1]) && \strlen($arrTag[1])) {
            $arrTag[1] = str_replace('[&]', '__AMP__', $arrTag[1]);
            $strParams = \StringUtil::decodeEntities($arrTag[1]);
            $arrParams = preg_split('/&/sim', $strParams);

            $arrReturn = [];
            foreach ($arrParams as $strParam) {
                [$key, $value] = explode('=', $strParam);
                $arrReturn[$key] = str_replace('__AMP__', '&', $value);
            }
        }

        return $arrReturn;
    }

    /**
     * Replace 'condition tags': {if ...}, {elseif ...}, {else} and  {endif}.
     *
     * @param string String to parse
     *
     * @return bool
     */
    public function replaceConditionTags(&$strBuffer)
    {
        if (!\strlen($strBuffer)) {
            return false;
        }

        $blnEval = false;
        $strReturn = '';

        $arrTags = preg_split('/(\{[^}]+\})/sim', $strBuffer, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (!empty($arrTags)) {
            // Replace tags
            foreach ($arrTags as $strTag) {
                if (0 === strncmp($strTag, '{if', 3)) {
                    $strReturn .= preg_replace('/\{if (.*)\}/i', '<?php if ($1): ?>', $strTag);
                    $blnEval = true;
                } elseif (0 === strncmp($strTag, '{elseif', 7)) {
                    $strReturn .= preg_replace('/\{elseif (.*)\}/i', '<?php elseif ($1): ?>', $strTag);
                    $blnEval = true;
                } elseif (0 === strncmp($strTag, '{else', 5)) {
                    $strReturn .= '<?php else: ?>';
                    $blnEval = true;
                } elseif (0 === strncmp($strTag, '{endif', 6)) {
                    $strReturn .= '<?php endif; ?>';
                    $blnEval = true;
                } else {
                    $strReturn .= $strTag;
                }
            }

            $strBuffer = $strReturn;
        }

        return $blnEval;
    }

    /**
     * Handle multiline string.
     *
     * @param $strBuffer
     *
     * @return string
     */
    public function formatMultilineValue($strBuffer)
    {
        if (empty($strBuffer) || false === strpos($strBuffer, "\n")) {
            return $strBuffer;
        }

        $strBuffer = preg_replace('/(<\/|<)(h\d|p|div|section|ul|ol|li|table|tbody|tr|td|th)([^>]*)(>)(\n)/si', '\\1\\2\\3\\4', $strBuffer);
        $strBuffer = nl2br($strBuffer, false);

        return preg_replace('/(<\/)(h\d|p|div|section|ul|ol|li|table|tbody|tr|td|th)([^>]*)(>)(\n)/si', "\\1\\2\\3\\4\n", $strBuffer);
    }

    /**
     * Eval code.
     *
     * @param string     $strBuffer
     * @param array|null $arrSubmitted
     * @param array|null $arrFiles
     * @param array|null $arrForm
     *
     * @throws \Exception
     *
     * @return mixed|string
     */
    public function evalConditionTags($strBuffer, $arrSubmitted = null, $arrFiles = null, $arrForm = null)
    {
        if (!\strlen($strBuffer)) {
            return;
        }
        $strBuffer=$this->decodeSpecialChars($strBuffer);        // Sonderzeichen wie &#34; 
        $strReturn = str_replace('?><br />', '?>', $strBuffer);

        // Eval the code
        ob_start();
        try {                             // irgendwie funktioniert das nicht eval fuehrt direkt zur exception 
        $blnEval = @eval('?>'.$strReturn);
        $strReturn = ob_get_contents();
        }
        catch(Exception $e) {
           echo 'Message: ' .$e->getMessage();
            throw new \Exception("!!! Error eval() in Formdata::evalConditionTags ($strReturn)");
        }
        ob_end_clean();
        // Throw an exception if there is an eval() error
        if (false === $blnEval) {
            throw new \Exception("Error eval() in Formdata::evalConditionTags ($strReturn)");
        }

        // Return the evaled code
        return $strReturn;
    }

    /**
     * Get the empty tag closing tag.
     *
     * @return string
     */
    public function getEmptyTagEnd()
    {
        if (TL_MODE === 'BE') {
            return ' />';
        }

        global $objPage;

        return ('xhtml' === $objPage->outputFormat) ? ' />' : '>';
    }

    /**
     * Handle ajax post actions.
     *
     * @param $strAction
     * @param $dc
     */
    public function executePostActions($strAction, $dc): void
    {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "strAction $strAction ");

        switch ($strAction) {
            case 'toggleEfgSubpalette':
                if ($dc instanceof DC_Formdata) {
                    if ('editAll' === \Input::get('act')) {
                        $this->strAjaxId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', \Input::post('id'));
                        if (\in_array(\Input::post('field'), $this->arrBaseFields, true)) {
                            $up = 'UPDATE tl_formdata SET '.\Input::post('field')."='".(1 === (int) (\Input::post('state')) ? 1 : '')."' WHERE id=".$this->strAjaxId;
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "generate Update (Up1) $up  ");
                            \Database::getInstance()->prepare('UPDATE tl_formdata SET '.\Input::post('field')."='".(1 === (int) (\Input::post('state')) ? 1 : '')."' WHERE id=?")->execute($this->strAjaxId);
                        } else {
                            $strValue = '';
                            if (1 === (int) (\Input::post('state'))) {
                                $option = array_pop($GLOBALS['TL_DCA']['tl_formdata']['fields'][\Input::post('field')]['options']);
                                if (!empty($option)) {
                                    $strValue = $option;
                                }
                            }
                            $objResult = \Database::getInstance()->prepare('SELECT * FROM tl_formdata_details WHERE pid=? AND ff_name=?')->execute($this->strAjaxId, \Input::post('field'));
                            if ($objResult->numRows < 1) {
                                $arrFieldSet = [
                                    'pid' => $this->strAjaxId,
                                    'tstamp' => time(),
                                    'ff_id' => $GLOBALS['TL_DCA']['tl_formdata']['fields'][\Input::post('field')]['f_id'],
                                    'ff_name' => \Input::post('field'),
                                    'value' => $strValue,
                                ];
                                \Database::getInstance()->prepare('INSERT INTO tl_formdata_details %s')->set($arrFieldSet)->execute();
                            } else {
                                \Database::getInstance()->prepare("UPDATE tl_formdata_details SET `value`='".$strValue."' WHERE pid=? AND ff_name=?")->execute($this->strAjaxId, \Input::post('field'));
                            }
                        }

                        if (\Input::post('load')) {
                            echo $dc->editAll($this->strAjaxId, \Input::post('id'));
                        }
                    } else {
                        if (\in_array(\Input::post('field'), $this->arrBaseFields, true)) {
                            $up = 'UPDATE tl_formdata SET '.\Input::post('field')."='".(1 === (int) (\Input::post('state')) ? 1 : '')."' WHERE id=".$dc->strAjaxId;
                            //$this->log("PBD Formdata executePostActions generate Up2 $up  ", __METHOD__, TL_GENERAL);
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "generate Update (Up2) $up  ");
                            \Database::getInstance()->prepare('UPDATE tl_formdata SET '.\Input::post('field')."='".(1 === (int) (\Input::post('state')) ? 1 : '')."' WHERE id=?")->execute($dc->id);
                        } else {
                            $strValue = '';
                            if (1 === (int) (\Input::post('state'))) {
                                $option = array_pop($GLOBALS['TL_DCA']['tl_formdata']['fields'][\Input::post('field')]['options']);
                                if (!empty($option)) {
                                    $strValue = $option;
                                }
                            }
                            $objResult = \Database::getInstance()->prepare('SELECT * FROM tl_formdata_details WHERE pid=? AND ff_name=?')->execute($dc->id, \Input::post('field'));
                            if ($objResult->numRows < 1) {
                                $arrFieldSet = [
                                    'pid' => $dc->id,
                                    'tstamp' => time(),
                                    'ff_id' => $GLOBALS['TL_DCA']['tl_formdata']['fields'][\Input::post('field')]['f_id'],
                                    'ff_name' => \Input::post('field'),
                                    'value' => $strValue,
                                ];
                                \Database::getInstance()->prepare('INSERT INTO tl_formdata_details %s')->set($arrFieldSet)->execute();
                            } else {
                                \Database::getInstance()->prepare("UPDATE tl_formdata_details SET `value`='".$strValue."' WHERE pid=? AND ff_name=?")->execute($dc->id, \Input::post('field'));
                            }
                        }

                        if (\Input::post('load')) {
                            echo $dc->edit(false, \Input::post('id'));
                        }
                    }
                }
                break;

            // Load nodes of the file tree
            case 'loadFiletree':
                $arrData['strTable'] = $dc->table;
                $arrData['id'] = $this->strAjaxName ?: $dc->id;
                $arrData['name'] = \Input::post('name');
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'loadFiletree name '.\Input::post('name').' id '.$arrData['id']);

                $objWidget = new $GLOBALS['BE_FFL']['fileSelector']($arrData, $dc);

                // Load a particular node
                if ('' !== \Input::post('folder', true)) {
                    echo $objWidget->generateAjax(\Input::post('folder', true), \Input::post('field'), (int) (\Input::post('level')));
                } else {
                    echo $objWidget->generate();
                }
                exit; break;

            case 'reloadEfgFiletree':
            case 'reloadFiletree':              // PBD  ob das in Allen Faellen funktioniert ??
                                          // Im Javascript erzeugen in DC_FORMDATA wird reloadEfgFiletree eingetragen
                                          // Warum hier dann reloadFiletree ankommt habe ich nicht verstanden.
                $intId = \Input::get('id');
                $strField = $strFieldName = \Input::post('name');

                // Handle the keys in "edit multiple" mode
                if ('editAll' === \Input::get('act')) {
                    $intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                    $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
                }

                // Validate the request data
                if (\Database::getInstance()->tableExists($dc->table)) {
//                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "reloadFiletree tabelle da '".$dc->table." Feld $strField ");
                    // The field does not exist
                    if (!\in_array($strField, $dc->arrBaseFields, true) && !\in_array($strField, $dc->arrDetailFields ?? [], true)) {
                        $this->log('Field "'.$strField.'" does not belong to form data table', 'Formdata executePostActions()', TL_ERROR);
                        header('HTTP/1.1 400 Bad Request');
                        exit('Bad Request');
                    }

                    $objRow = \Database::getInstance()->prepare('SELECT * FROM '.$dc->table.' WHERE id=?')
                        ->execute($intId)
                    ;

                    // The record does not exist
                    if ($objRow->numRows < 1) {
                        $this->log('A record with the ID "'.$intId.'" does not exist in table "'.$dc->table.'"', 'Ajax executePostActions()', TL_ERROR);
                        header('HTTP/1.1 400 Bad Request');
                        exit('Bad Request');
                    }
                }

                $varValue = \Input::post('value');
                $strKey = 'fileTree';

                // Convert the selected values
                if ('' !== $varValue) {
                    $varValue = trimsplit("\t", $varValue);

                    // Automatically add resources to the DBAFS
                    if ('fileTree' === $strKey) {
                        foreach ($varValue as $k => $v) {
                            $varValue[$k] = \Dbafs::addResource($v)->uuid;
                        }
                    }

                    $varValue = serialize($varValue);
                }

                // Set the new value
                if (\in_array($strField, $dc->arrBaseFields ?? [], true) || \in_array($strField, $dc->arrDetailFields ?? [], true)) {
                    $objRow->$strField = $varValue;
                    $arrAttribs['activeRecord'] = $objRow;
                }

                $arrAttribs['id'] = $strFieldName;
                $arrAttribs['name'] = $strFieldName;
                $arrAttribs['value'] = $varValue;
                $arrAttribs['strTable'] = $dc->table;
                $arrAttribs['strField'] = $strField;

                $objWidget = new $GLOBALS['BE_FFL'][$strKey]($arrAttribs);
                echo $objWidget->generate().'<script>handleEfgFileselectorButton();</script>';
         exit;    // PBD bei Ajax Request mit exit verlassen
                break; exit;

            case 'reloadEfgImportSource':
                $intId = \Input::get('id');
                $strField = $strFieldName = \Input::post('name');

                $varValue = \Input::post('value');
                $strKey = 'fileTree';
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "reloadEfgImportSource intId $intId strField $strField varValue $varValue");

                // Convert the selected values
                if ('' !== $varValue) {
                    $varValue = explode("\t", $varValue);

                    // Automatically add resources to the DBAFS
                    if ('fileTree' === $strKey) {
                        foreach ($varValue as $k => $v) {
                            $v=urldecode($v);
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "urldecode $v");
                            $varValue[$k] = \Dbafs::addResource($v)->uuid;
                        }
                    }

                    $varValue = serialize($varValue);
                }
                $arrAttribs['id'] = $strFieldName;
                $arrAttribs['name'] = $strFieldName;
                $arrAttribs['value'] = $varValue;
                $arrAttribs['strTable'] = $dc->table;
                $arrAttribs['strField'] = $strField;

                $objWidget = new $GLOBALS['BE_FFL'][$strKey]($arrAttribs);
                echo $objWidget->generate().'<script>handleEfgFileselectorButton();</script>';
//                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'reloadEfgImportSource vor generate Widget globals '.$GLOBALS['BE_FFL'][$strKey]."strFieldName $strFieldName strField $strField table ".$dc->table);
        exit;                                      // PBD bei Ajax Request mit exit verlassen
                break; exit;
        }
    }

    /**
     * Decode special characters.
     *
     * @param mixed $varValue A string or array
     *
     * @return mixed The decoded string or array
     */
    protected function decodeSpecialChars($varValue)
    {
        if (null === $varValue || '' === $varValue) {
            return $varValue;
        }

        // Recursively clean arrays
        if (\is_array($varValue)) {
            foreach ($varValue as $k => $v) {
                $varValue[$k] = $this->decodeSpecialChars($v);
            }

            return $varValue;
        }
        // ', ", = hinzugefuegt
        $arrSearch = ['&#35;', '&#60;', '&#62;', '&#40;', '&#41;', '&#92;', '&#61;','&#39;','&#34;'];
        $arrReplace = ['#', '<', '>', '(', ')', '\\', '=','\'','"'];

        return str_replace($arrSearch, $arrReplace, $varValue);
    }

    /**
     * Get all pages containing frontend module formdata listing.
     *
     * @return array
     */
    private function getListingPages()
    {
        if (!$this->arrListingPages) {
            // Get all pages containing listing formdata
            $objListingPages = \Database::getInstance()->prepare('SELECT tl_page.id,tl_page.alias FROM tl_page, tl_content, tl_article, tl_module WHERE (tl_page.id=tl_article.pid AND tl_article.id=tl_content.pid AND tl_content.module=tl_module.id) AND tl_content.type=? AND tl_module.type=?')
                ->execute('module', 'formdatalisting')
            ;

            while ($objListingPages->next()) {
                $this->arrListingPages[$objListingPages->id] = $objListingPages->alias;
            }
        }

        return $this->arrListingPages;
    }

    /**
     * Get all pages for search indexer.
     *
     * @return array
     */
    private function getSearchableListingPages()
    {
        if (!$this->arrSearchableListingPages) {
            // Get all pages containing listing formdata with details page
            $objListingPages = \Database::getInstance()->prepare("SELECT tl_page.id,tl_page.alias,tl_page.protected,tl_module.list_formdata,tl_module.efg_DetailsKey,tl_module.list_where,tl_module.efg_list_access,tl_module.list_fields,tl_module.list_info FROM tl_page, tl_content, tl_article, tl_module WHERE (tl_page.id=tl_article.pid AND tl_article.id=tl_content.pid AND tl_content.module=tl_module.id) AND tl_content.type=? AND tl_module.type=? AND tl_module.list_info != '' AND tl_module.efg_list_access=? AND (tl_page.start=? OR tl_page.start<?) AND (tl_page.stop=? OR tl_page.stop>?) AND tl_page.published=?")
                ->execute('module', 'formdatalisting', 'public', '', time(), '', time(), 1)
            ;

            while ($objListingPages->next()) {
                $strFormdataDetailsKey = 'details';
                if (!empty($objListingPages->efg_DetailsKey)) {
                    $strFormdataDetailsKey = $objListingPages->efg_DetailsKey;
                }
                $this->arrSearchableListingPages[$objListingPages->id] = [
                    'formdataDetailsKey' => $strFormdataDetailsKey,
                    'alias' => $objListingPages->alias,
                    'protected' => $objListingPages->protected,
                    'list_formdata' => $objListingPages->list_formdata,
                    'list_where' => $objListingPages->list_where,
                    'list_fields' => $objListingPages->list_fields,
                    'list_info' => $objListingPages->list_info,
                    'efg_list_access' => $objListingPages->efg_list_access,
                ];
            }
        }

        return $this->arrSearchableListingPages;
    }
}
