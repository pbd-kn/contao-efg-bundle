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

namespace PBDKN\Efgco4\Resources\contao\modules;

use PBDKN\Efgco4\Resources\contao\forms\ExtendedForm;
use PBDKN\Efgco4\Resources\contao\classes\EfgLog;
/**
 * Class ModuleFormdataListing.
 *
 * based on ModuleListing by Leo Feyer
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class ModuleFormdataListing extends \Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'list_fd_table_default';

    protected $strTable = 'tl_formdata';

    protected $strIconFolder;

    /**
     * Related form, like fd_frm_contact.
     *
     * @param string
     */
    protected $strFormKey;

    /**
     * Related dca file, like fd_frm_contact.
     *
     * @param string
     */
    protected $strDcaKey;

    /**
     * Related form filter key, name of field in table tl_formdata hlding form-identifier.
     *
     * @param string
     */
    protected $strFormFilterKey;

    /**
     * Related form filter value, title of related form like 'Contact Form".
     *
     * @param string
     */
    protected $strFormFilterValue;

    /**
     * sql condition for form to filter.
     *
     * @param string
     */
    protected $sqlFormFilter;

    /**
     * Base fields in table tl_formdata.
     *
     * @param mixed
     */
    protected $arrBaseFields;

    /**
     * Base fields for owner restriction (member,user,..).
     *
     * @param mixed
     */
    protected $arrOwnerFields;

    /**
     * Detail fields in table tl_formdata_details.
     *
     * @param mixed
     */
    protected $arrDetailFields;

    /**
     * Form fields of forms storing data.
     *
     * @param mixed
     */
    protected $arrFF;

    /**
     * Names of form fields.
     *
     * @param mixed
     */
    protected $arrFFNames;
    protected $arrAllowedOwnerIds;
    protected $arrAllowedEditOwnerIds;
    protected $arrAllowedDeleteOwnerIds;
    protected $arrAllowedExportOwnerIds;

    protected $arrMembers;

    protected $arrUsers;

    protected $arrMemberGroups;

    protected $arrUserGroups;

    protected $intRecordId;
    protected $strDetailKey = 'details';

    protected $arrDetailKeys = [];

    // Decode UTF8 on CSV-/XLS-Export
    // This can be deactivated by configuration setting: $GLOBALS['efg_co4']['exportUTF8Decode'] = false
    protected $blnExportUTF8Decode = true;

    // Target charset when converting from UTF8 on CSV-/XLS-Export
    // This can be changed by configuration setting: $GLOBALS['efg_co4']['exportConvertToCharset'] = 'TARGET_CHARSET'
    protected $strExportConvertToCharset = 'CP1252';

    /**
     * Fields to ignore on export.
     */
    protected $arrExportIgnoreFields;
    /* so geht das nicht
    public function __construct()
    {
        $this->log('PBD ModuleFormdataListing construct 1', __METHOD__, TL_GENERAL);
        EfgLog::setEfgDebugmode('form');
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'TL_MODE '.TL_MODE);
    }
    */

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        EfgLog::setEfgDebugmode('form');
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'TL_MODE '.TL_MODE);
        if (TL_MODE === 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### LISTING FORMDATA ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id.'&amp;rt='.REQUEST_TOKEN;

            return $objTemplate->parse();
        }

        if (\strlen($this->efg_DetailsKey)) {
            $this->strDetailKey = $this->efg_DetailsKey;
        }

        $this->blnExportUTF8Decode = true;
        $this->strExportConvertToCharset = 'CP1252';
        if (isset($GLOBALS['efg_co4']['exportUTF8Decode']) && false === $GLOBALS['efg_co4']['exportUTF8Decode']) {
            $this->blnExportUTF8Decode = false;
        }
        if (isset($GLOBALS['efg_co4']['exportConvertToCharset'])) {
            $this->strExportConvertToCharset = $GLOBALS['efg_co4']['exportConvertToCharset'];
        }

        if (isset($GLOBALS['efg_co4']['exportIgnoreFields'])) {
            if (\is_string($GLOBALS['efg_co4']['exportIgnoreFields']) && \strlen($GLOBALS['efg_co4']['exportIgnoreFields'])) {
                $this->arrExportIgnoreFields = trimsplit(',', $GLOBALS['efg_co4']['exportIgnoreFields']);
            }
        }

        // remove download and export from referer
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vor remove download and export from referer');
        $session = $this->Session->getData();
        $session['referer']['last'] = preg_replace('@(\?|&amp;|&)download=.*?(&amp;|&|$)@si', '', $session['referer']['last']);
        $session['referer']['current'] = preg_replace('@(\?|&amp;|&)download=.*?(&amp;|&|$)@si', '', $session['referer']['current']);
        $session['referer']['last'] = preg_replace('@(\?|&amp;|&)act=export(&amp;|&|$)@si', '', $session['referer']['last']);
        $session['referer']['current'] = preg_replace('@(\?|&amp;|&)act=export(&amp;|&|$)@si', '', $session['referer']['current']);
        $this->Session->setData($session);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vor checking strDetailKey this->list_layout '.$this->list_layout);

        if (\Input::get($this->strDetailKey) && !\strlen($this->list_info) && !\strlen(\Input::get('act'))) {
            return '';
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'nach checking strDetailKey this->list_layout '.$this->list_layout);

        // Fallback template
        if ('' === $this->list_layout) {
            $this->list_layout = 'list_fd_table_default';
        }

        $this->import('FrontendUser', 'Member');
        $this->import('Formdata');

        $this->arrOwnerFields = ['fd_member', 'fd_user', 'fd_member_group', 'fd_user_group'];

        $this->arrMembers = $this->Formdata->arrMembers;
        $this->arrMemberGroups = $this->Formdata->arrMemberGroups;
        $this->arrUsers = $this->Formdata->arrUsers;
        $this->arrUserGroups = $this->Formdata->arrUserGroups;

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'check list access '.$this->efg_fe_list_access);

        // check list access
        if (\strlen($this->efg_list_access)) {
            $arrAllowedOwnerIds = [];
            switch ($this->efg_list_access) {
                case 'member': // display own records only
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;

                case 'groupmembers': // display records of group members
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                        $arrGroups = $this->Member->groups;
                        $arrGroupsWhere = [];
                        if (!empty($arrGroups)) {
                            foreach ($arrGroups as $group) {
                                $arrGroupsWhere[] = ' groups LIKE \'%"'.(int) $group.'"%\'';
                            }
                            $objGroupMembers = \Database::getInstance()->prepare('SELECT DISTINCT id FROM tl_member WHERE '.implode(' OR ', $arrGroupsWhere))
                                ->execute()
                            ;
                            $arrGroupMembers = $objGroupMembers->fetchEach('id');
                            if (!empty($arrGroupMembers)) {
                                $arrAllowedOwnerIds = array_merge($arrAllowedOwnerIds, $arrGroupMembers);
                            }
                        }
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;

                case 'public':
                default:
                    break;
            }
            if ('public' !== $this->efg_list_access) {
                for ($n = 0; $n < \count($arrAllowedOwnerIds); ++$n) {
                    $arrAllowedOwnerIds[$n] = (int) ($arrAllowedOwnerIds[$n]);
                }
                $this->arrAllowedOwnerIds = array_unique($arrAllowedOwnerIds);
            }
            unset($arrAllowedOwnerIds);
        }

        // check edit access
        if (\strlen($this->efg_fe_edit_access)) {
            $arrAllowedOwnerIds = [];
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'check edit access '.$this->efg_fe_edit_access);
            switch ($this->efg_fe_edit_access) {
                case 'member': // edit own records only
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;

                case 'groupmembers': // edit records of group members
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                        $arrGroups = $this->Member->groups;
                        $arrGroupsWhere = [];
                        if (!empty($arrGroups)) {
                            foreach ($arrGroups as $group) {
                                $arrGroupsWhere[] = ' groups LIKE \'%"'.(int) $group.'"%\'';
                            }
                            $objGroupMembers = \Database::getInstance()->prepare('SELECT DISTINCT id FROM tl_member WHERE '.implode(' OR ', $arrGroupsWhere))
                                ->execute()
                            ;
                            $arrGroupMembers = $objGroupMembers->fetchEach('id');
                            if (!empty($arrGroupMembers)) {
                                $arrAllowedOwnerIds = array_merge($arrAllowedOwnerIds, $arrGroupMembers);
                            }
                        }
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;

                case 'public':
                default:
                    break;
            }
            if ('public' !== $this->efg_fe_edit_access) {
                for ($n = 0; $n < \count($arrAllowedOwnerIds); ++$n) {
                    $arrAllowedOwnerIds[$n] = (int) ($arrAllowedOwnerIds[$n]);
                }
                $this->arrAllowedEditOwnerIds = array_unique($arrAllowedOwnerIds);
            }
            unset($arrAllowedOwnerIds);
        }

        // check delete access
        if (\strlen($this->efg_fe_delete_access)) {
            $arrAllowedOwnerIds = [];
            switch ($this->efg_fe_delete_access) {
                case 'member': // delete own records only
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;

                case 'groupmembers': // delete records of group members
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                        $arrGroups = $this->Member->groups;
                        $arrGroupsWhere = [];
                        if (!empty($arrGroups)) {
                            foreach ($arrGroups as $group) {
                                $arrGroupsWhere[] = ' groups LIKE \'%"'.(int) $group.'"%\'';
                            }
                            $objGroupMembers = \Database::getInstance()->prepare('SELECT DISTINCT id FROM tl_member WHERE '.implode(' OR ', $arrGroupsWhere))
                                ->execute()
                            ;
                            $arrGroupMembers = $objGroupMembers->fetchEach('id');
                            if (!empty($arrGroupMembers)) {
                                $arrAllowedOwnerIds = array_merge($arrAllowedOwnerIds, $arrGroupMembers);
                            }
                        }
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;
                case 'public':
                default:
                    break;
            }
            if ('public' !== $this->efg_fe_delete_access) {
                for ($n = 0; $n < \count($arrAllowedOwnerIds); ++$n) {
                    $arrAllowedOwnerIds[$n] = (int) ($arrAllowedOwnerIds[$n]);
                }
                $this->arrAllowedDeleteOwnerIds = array_unique($arrAllowedOwnerIds);
            }
            unset($arrAllowedOwnerIds);
        }

        // check export access
        if (\strlen($this->efg_fe_export_access)) {
            $arrAllowedOwnerIds = [];
            switch ($this->efg_fe_export_access) {
                case 'member': // export own records only
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;

                case 'groupmembers': // export records of group members
                    if ((int) ($this->Member->id) > 0) {
                        $arrAllowedOwnerIds[] = (int) ($this->Member->id);
                        $arrGroups = $this->Member->groups;
                        $arrGroupsWhere = [];
                        if (!empty($arrGroups)) {
                            foreach ($arrGroups as $group) {
                                $arrGroupsWhere[] = ' groups LIKE \'%"'.(int) $group.'"%\'';
                            }
                            $objGroupMembers = \Database::getInstance()->prepare('SELECT DISTINCT id FROM tl_member WHERE '.implode(' OR ', $arrGroupsWhere))
                                ->execute()
                            ;
                            $arrGroupMembers = $objGroupMembers->fetchEach('id');
                            if (!empty($arrGroupMembers)) {
                                $arrAllowedOwnerIds = array_merge($arrAllowedOwnerIds, $arrGroupMembers);
                            }
                        }
                    } else {
                        $arrAllowedOwnerIds[] = -1;
                    }
                    break;
                case 'public':
                default:
                    break;
            }
            if ('public' !== $this->efg_fe_export_access) {
                for ($n = 0; $n < \count($arrAllowedOwnerIds); ++$n) {
                    $arrAllowedOwnerIds[$n] = (int) ($arrAllowedOwnerIds[$n]);
                }
                $this->arrAllowedExportOwnerIds = array_unique($arrAllowedOwnerIds);
            }
            unset($arrAllowedOwnerIds);
        }

        // file download
        $down= \Input::get('download');
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'input download '.$down);
        if (isset($down)&&\strlen($down)) {
            $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

            $arrParams = explode('.', \Input::get('download'));
            $intFdId = $arrParams[0];
            unset($arrParams[0]);

            $strField = implode('.', $arrParams);
            $objDownload = \Database::getInstance()->prepare('SELECT fd.fd_member, fd.fd_user, fdd.value FROM tl_formdata fd, tl_formdata_details fdd WHERE (fd.id=fdd.pid) AND fd.id=? AND fdd.ff_name=?')
                ->execute($intFdId, $strField)
            ;
            if ($objDownload->numRows) {
                $arrDownload = $objDownload->fetchAssoc();

                if ($this->arrAllowedOwnerIds && !\in_array($arrDownload['fd_member'], $this->arrAllowedOwnerIds, true)) {
                    return;
                }

                // Send file to the browser
                if (is_file(TL_ROOT.'/'.$arrDownload['value'])) {
                    $objFile = new \File($arrDownload['value']);
                    if (\in_array($objFile->extension, $allowedDownload, true)) {
                        $this->sendFileToBrowser($arrDownload['value']);

                        return;
                    }
                }
            }
        }

        $this->strTemplate = $this->list_layout;
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'call parent generate strTemplate '.$this->strTemplate);

        return parent::generate();
    }

    /**
     * Generate frontend editing form.
     *
     * @return string
     */
    public function generateEditForm($objFormElement, $objRecord)
    {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, '-> ');

        if (TL_MODE === 'BE') {
            return '';
        }

        $objFormElement->typePrefix = 'ce_';

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'generateEditForm vor create strTemplate '.$this->strTemplate);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'class objFormElement '.get_class($objFormElement));
        $this->EditForm = new ExtendedForm($objFormElement);
        $this->EditForm->objEditRecord = $objRecord;

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'generateEditForm nach create strTemplate '.$this->strTemplate.' extendedForm Class '.get_class($this->EditForm));
        return $this->EditForm->generate();
    }

    /**
     * Format a value.
     *
     * @param mixed
     */
    public function formatValue($k, $value)
    {
        global $objPage;

        $value = deserialize($value);

        $rgxp = '';
        if ($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['rgxp']) {
            $rgxp = $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['rgxp'];
        } else {
            $rgxp = $this->arrFF[$k]['rgxp'];
        }

        // Array
        if (\is_array($value)) {
            $value = implode(', ', $value);
        }

        // Date and time
        elseif ($value && 'date' === $rgxp) {
            $value = \Date::parse((!empty($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['dateFormat']) ? $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['dateFormat'] : $objPage->dateFormat), $value);
        }

        // Time
        elseif ($value && 'time' === $rgxp) {
            $value = \Date::parse($objPage->timeFormat, $value);
        }

        // Date and time
        elseif ($value && 'datim' === $rgxp) {
            $value = \Date::parse($objPage->datimFormat, $value);
        } elseif ($value && ('checkbox' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']
                || 'efgLookupCheckbox' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']
                || 'select' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']
                || 'conditionalselect' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']
                || 'efgLookupSelect' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']
                || 'radio' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']
                || 'fileTree' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType'])
        ) {
            $value = str_replace('|', ', ', $value);
        }

        // owner fields fd_member, fd_user
        if (isset($this->arrBaseFields)&&\in_array($k, $this->arrBaseFields, true) && \in_array($k, $this->arrOwnerFields, true)) {
            if ('fd_member' === $k) {
                $value = $this->arrMembers[$value];
            } elseif ('fd_user' === $k) {
                $value = $this->arrUsers[$value];
            } elseif ('fd_member_group' === $k) {
                $value = $this->arrMemberGroups[$value];
            } elseif ('fd_user_group' === $k) {
                $value = $this->arrUserGroups[$value];
            }
        }

        // URLs
        if ($value && 'url' === $rgxp && preg_match('@^(https?://|ftp://)@i', $value)) {
            $value = '<a href="'.$value.'"'.(('xhtml' === $objPage->outputFormat) ? ' onclick="return !window.open(this.href)"' : ' target="_blank"').'>'.$value.'</a>';

            return $value;
        }

        // E-mail addresses
        if ($value && ('email' === $rgxp || false !== strpos($this->arrFF[$k]['name'], 'mail') || false !== strpos($k, 'mail'))) {
            $value = \StringUtil::encodeEmail($value);
            $value = '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.$value.'">'.$value.'</a>';

            return $value;
        }

        if (isset($value)&&\strlen($value)) {
            $value = \StringUtil::decodeEntities($value);
            $value = ampersand($value);

            if (!\is_bool(strpos($value, "\n"))) {
                $value = $this->Formdata->formatMultilineValue($value);
            }
        }

        return $value;
    }

    /**
     * Convert encoding.
     *
     * @param $strString String to convert
     * @param $from charset to convert from
     * @param $to charset to convert to
     *
     * @return string
     */
    public function convertEncoding($strString, $from, $to)
    {
        if (USE_MBSTRING) {
            @mb_substitute_character('none');

            return @mb_convert_encoding($strString, $to, $from);
        }
        if (\function_exists('iconv')) {
            if (\strlen($iconv = @iconv($from, $to.'//IGNORE', $strString))) {
                return $iconv;
            }

            return @iconv($from, $to, $strString);
        }

        return $strString;
    }

    /**
     * Generate module.
     */
    protected function compile(): void
    {
        global $objPage;

        $blnExport = false;
        $blnCustomXlsExport = false;
        $strExportMode = 'csv';

        $strSearchFormType = 'dropdown';
        if ($this->efg_list_searchtype) {
            $strSearchFormType = $this->efg_list_searchtype;
        }
        $this->strIconFolder = (\strlen($this->efg_iconfolder) ? $this->efg_iconfolder : 'bundles/contaoefgco4/icons');
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'efg_iconfolder'.$this->efg_iconfolder.' strIconFolder '.$this->strIconFolder);

        $this->import('FrontendUser', 'Member');

        $this->list_table = 'tl_formdata';

        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        // get names of detail fields
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'query detailfields '."SELECT ff.name, ff.label, ff.type, ff.rgxp FROM tl_form_field ff, tl_form f WHERE (ff.pid=f.id) AND ff.name != '' AND f.storeFormdata=1");
        $objFF = \Database::getInstance()->prepare("SELECT ff.name, ff.label, ff.type, ff.rgxp FROM tl_form_field ff, tl_form f WHERE (ff.pid=f.id) AND ff.name != '' AND f.storeFormdata=?")
            ->execute('1')
        ;
        if ($objFF->numRows) {
            $this->arrFF = [];
            $this->arrFFNames = [];

            while ($objFF->next()) {
                $arrField = $objFF->row();
                $this->arrFF[$arrField['name']] = $arrField;
                $this->arrFFNames[] = $arrField['name'];
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'row gespeichert '.$arrField['name']);
            }
        }

        $page_get = 'page_fd'.$this->id;

        $this->strFormKey = '';
        $this->strDcaKey = 'tl_formdata';
        $this->strFormFilterKey = '';
        $this->strFormFilterValue = '';

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'this->list_formdata '.$this->list_formdata);
        if (\strlen($this->list_formdata)) {
            if ('feedback' === $this->list_formdata || 'fd_feedback' === $this->list_formdata || 'tl_formdata' === $this->list_formdata) {
                $this->strFormKey = '';
                $this->strDcaKey = 'fd_feedback';
                $this->strFormFilterKey = '';
                $this->strFormFilterValue = '';
            } else {
                $this->strFormKey = ('fd_' === substr($this->list_formdata, 0, 3)) ? $this->list_formdata : 'fd_'.$this->list_formdata;
                $this->strDcaKey = ('fd_' === substr($this->list_formdata, 0, 3)) ? $this->list_formdata : 'fd_'.$this->list_formdata;
                $this->strFormFilterKey = 'form';
                $this->strFormFilterValue = $this->Formdata->arrStoringForms[str_replace('fd_', '', $this->strFormKey)]['title'];
                $this->sqlFormFilter = ' AND '.$this->strFormFilterKey.'=\''.$this->strFormFilterValue.'\' ';
            }
        }

            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vor loadDataContainer '.$this->strDcaKey.' strFormKey '.$this->strFormKey);
        // load dca-config into $GLOBALS['TL_DCA']['tl_formdata']
        $this->loadDataContainer($this->strDcaKey);
        \System::loadLanguageFile('tl_formdata');

        // Export
        if ('export' === \Input::get('act')) {
            $blnExport = true;
            $strExportMode = 'csv';
        } elseif ('exportxls' === \Input::get('act')) {
            $blnExport = true;
            $strExportMode = 'xls';
        }

        if ($blnExport) {
            $blnCustomXlsExport = false;
            $arrHookData = [];
            $arrHookDataColumns = [];

            $useFormValues = $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['useFormValues'];
            $useFieldNames = $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['useFieldNames'];

            $this->blnExportUTF8Decode = true;
            $this->strExportConvertToCharset = 'CP1252';
            if (isset($GLOBALS['efg_co4']['exportUTF8Decode']) && false === $GLOBALS['efg_co4']['exportUTF8Decode']) {
                $this->blnExportUTF8Decode = false;
            }
            if (isset($GLOBALS['efg_co4']['exportConvertToCharset'])) {
                $this->strExportConvertToCharset = $GLOBALS['efg_co4']['exportConvertToCharset'];
            }

            if ('xls' === $strExportMode) {
                // check for HOOK efgExportXls
                if (\array_key_exists('efgExportXls', $GLOBALS['TL_HOOKS']) && \is_array($GLOBALS['TL_HOOKS']['efgExportXls'])) {
                    $blnCustomXlsExport = true;
                } else {
                    include TL_ROOT.'/plugins/xls_export/xls_export.php';
                }
            }
        } else {
            $this->Template->textlink_details = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_details'];
            $this->Template->textlink_edit = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_edit'];
            $this->Template->textlink_delete = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_delete'];
            $this->Template->text_confirmDelete = $GLOBALS['TL_LANG']['tl_formdata']['fe_deleteConfirm'];
            $this->Template->textlink_export = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_export'];
            $this->Template->iconFolder = $this->strIconFolder;

            $this->Template->details = \strlen($this->list_info) ? true : false;

            $this->Template->editable = false;
            if (\strlen($this->efg_fe_edit_access)) {
                if ('public' === $this->efg_fe_edit_access) {
                    $this->Template->editable = true;
                } elseif (('member' === $this->efg_fe_edit_access || 'groupmembers' === $this->efg_fe_edit_access) && (int) ($this->Member->id) > 0) {
                    $this->Template->editable = true;
                }
            }

            $this->Template->deletable = false;
            if (\strlen($this->efg_fe_delete_access)) {
                if ('public' === $this->efg_fe_delete_access) {
                    $this->Template->deletable = true;
                } elseif (('member' === $this->efg_fe_delete_access || 'groupmembers' === $this->efg_fe_delete_access) && (int) ($this->Member->id) > 0) {
                    $this->Template->deletable = true;
                }
            }

            $this->Template->exportable = false;
            if (\strlen($this->efg_fe_export_access)) {
                if ('public' === $this->efg_fe_export_access) {
                    $this->Template->exportable = true;
                } elseif (('member' === $this->efg_fe_export_access || 'groupmembers' === $this->efg_fe_export_access) && (int) ($this->Member->id) > 0) {
                    $this->Template->exportable = true;
                }
            }
        }

        $this->arrBaseFields = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['baseFields'];
        $this->arrDetailFields = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['detailFields'];

        if (isset($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['formFilterKey'])&&\strlen($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['formFilterKey'])) {
            $this->strFormFilterKey = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['formFilterKey'];
        }
        if (isset($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['formFilterValue'])&&\strlen($GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['formFilterValue'])) {
            $this->strFormFilterValue = $GLOBALS['TL_DCA']['tl_formdata']['tl_formdata']['formFilterValue'];
        }

        // List, edit or delete a single record
        $strDetailKey= \Input::get($this->strDetailKey);
        if (isset($strDetailKey)&&\strlen($strDetailKey)) {
            // check details record
            $strQuery = 'SELECT id FROM tl_formdata f';
            $strWhere = ' WHERE (id=? OR alias=?)';
            $strListWhere = $this->prepareListWhere();
            $strWhere .= (\strlen($strListWhere) ? ' AND '.$strListWhere : '');

            if (\strlen($this->strFormKey)) {
                $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').$this->strFormFilterKey."='".$this->strFormFilterValue."'";
            }

            // replace insert tags in where, e.g. {{user::id}}
            $strWhere = $this->replaceWhereTags($strWhere);
            $strWhere = $this->replaceInsertTags($strWhere, false);
            $strQuery .= $strWhere;
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'check strQuery '.$strQuery.' strDetailKey '.\Input::get($this->strDetailKey));

            $objCheck = \Database::getInstance()->prepare($strQuery)
                ->execute(\Input::get($this->strDetailKey), \Input::get($this->strDetailKey))
            ;

            if (1 === $objCheck->numRows) {
                $this->intRecordId = (int) ($objCheck->id);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'numrows=1 id '.$objCheck->numRows);
            } else {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Could not identify record by ID "'.\Input::get($this->strDetailKey).'"');
                $strUrl = preg_replace('/\?.*$/', '', urldecode(\Environment::get('request')));
                $strUrlParams = '';
                $strUrlSuffix = $GLOBALS['TL_CONFIG']['urlSuffix'];

                $blnQuery = false;
                foreach (preg_split('/&(amp;)?/', urldecode($_SERVER['QUERY_STRING'])) as $fragment) {
                    if (\strlen($fragment)) {
                        if (0 !== strncasecmp($fragment, 'file', 5) && 0 !== strncasecmp($fragment, $this->strDetailKey, \strlen($this->strDetailKey)) && 0 !== strncasecmp($fragment, 'order_by', 8) && 0 !== strncasecmp($fragment, 'sort', 4) && 0 !== strncasecmp($fragment, $page_get, \strlen($page_get))) {
                            $strUrlParams .= (!$blnQuery ? '' : '&amp;').$fragment;
                            $blnQuery = true;
                        }
                    }
                }

                $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i'], ['', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
                \Controller::redirect($strRed);
            }

            if ('edit' === \Input::get('act') && (int) ($this->intRecordId) > 0) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'id -> editSingleRecord id'.$this->intRecordId);
                $this->editSingleRecord();

                return;
            }
            if ('delete' === \Input::get('act') && (int) ($this->intRecordId) > 0) {
                $this->deleteSingleRecord();

                return;
            }
            if ('export' === \Input::get('act') && (int) ($this->intRecordId) > 0) {
                $this->exportSingleRecord($strExportMode);

                return;
            }
            if ((int) ($this->intRecordId) > 0) {
                $this->listSingleRecord();

                return;
            }
        }

        $page = \Input::get($page_get) ?: 1;
        $per_page = \Input::get('per_page') ?: $this->perPage;

        /**
         * Add search query.
         */
        $strWhere = '';
        $varKeyword = '';
        $strOptions = '';

        if (!empty($this->arrAllowedOwnerIds)) {
            $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').'fd_member IN ('.implode(',', $this->arrAllowedOwnerIds).')';
        }

        if (!$blnExport) {
            $this->Template->searchable = false;
        }

        $arrSearchFields = trimsplit(',', $this->list_search);

        if ('none' === $strSearchFormType) {
            unset($arrSearchFields);
        }

        if (!empty($arrSearchFields)) {
            if (!$blnExport) {
                $this->Template->searchable = true;
                $this->Template->search_form_type = $strSearchFormType;
            }

            switch ($strSearchFormType) {
                case 'singlefield':
                    if (\strlen(\Input::get('search')) && \strlen(\Input::get('for'))) {
                        $varKeyword = '%'.\Input::get('for').'%';

                        $arrConds = [];
                        foreach (trimsplit(',', urldecode(\Input::get('search'))) as $field) {
                            if (\in_array($field, $this->arrOwnerFields, true)) {
                                if ('fd_member' === $field) {
                                    $prop = 'arrMembers';
                                } elseif ('fd_member_group' === $field) {
                                    $prop = 'arrMemberGroups';
                                } elseif ('fd_user' === $field) {
                                    $prop = 'arrUsers';
                                } elseif ('fd_user_group' === $field) {
                                    $prop = 'arrUserGroups';
                                }

                                $arrMatches = $this->array_filter_like($this->{$prop}, \Input::get('for'));
                                if (!empty($arrMatches)) {
                                    $arrConds[] = $field.' IN('.implode(',', array_keys($arrMatches)).')';
                                }
                            } elseif (\in_array($field, $this->arrBaseFields, true)) {
                                $arrConds[] = $field.' LIKE ?';
                            } else {
                                $arrConds[] = '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$field.'" AND pid=f.id ) LIKE ?';
                            }
                        }

                        if (!empty($arrConds)) {
                            $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').'('.implode(' OR ', $arrConds).')';
                            $varKeyword = array_fill(0, \count($arrConds), '%'.\Input::get('for').'%');
                        }
                    }

                    $strOptions = implode(',', $arrSearchFields);

                    break;

                case 'multiplefields':
                    $arrOptions = [];
                    $sr=\Input::get('search');
                    if (isset($sr)&&\strlen($sr) && \is_array(\Input::get('for'))) {
                        $arrConds = [];
                        $arrKeywords = [];
                        foreach (\Input::get('for') as $field => $for) {
                            if (\in_array($field, $arrSearchFields, true) && \strlen($for)) {
                                if (\in_array($field, $this->arrOwnerFields, true)) {
                                    if ('fd_member' === $field) {
                                        $prop = 'arrMembers';
                                    } elseif ('fd_member_group' === $field) {
                                        $prop = 'arrMemberGroups';
                                    } elseif ('fd_user' === $field) {
                                        $prop = 'arrUsers';
                                    } elseif ('fd_user_group' === $field) {
                                        $prop = 'arrUserGroups';
                                    }

                                    $arrMatches = $this->array_filter_like($this->{$prop}, urldecode($for));
                                    if (!empty($arrMatches)) {
                                        $arrConds[] = $field.' IN('.implode(',', array_keys($arrMatches)).')';
                                    }
                                } elseif (\in_array($field, $this->arrBaseFields, true)) {
                                    $arrConds[] = $field.' LIKE ?';
                                    $arrKeywords[] = '%'.urldecode($for).'%';
                                } else {
                                    $arrConds[] = '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$field.'" AND pid=f.id ) LIKE ?';
                                    $arrKeywords[] = '%'.urldecode($for).'%';
                                }
                            }
                        }

                        if (!empty($arrConds)) {
                            $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').'('.implode(' AND ', $arrConds).')';
                            $varKeyword = $arrKeywords;
                        }
                    }

                    foreach (trimsplit(',', $this->list_search) as $field) {
                        if (isset($this->arrBaseFields)&&\in_array($field, $this->arrBaseFields, true)) {
                            if (\strlen($this->strFormKey) && 'form' === $field) {
                                continue;
                            }

                            $arrOptions[] = ['name' => $field, 'label' => ($GLOBALS['TL_DCA'][$this->list_table]['fields'][$field]['label'][0] ? htmlspecialchars($GLOBALS['TL_DCA'][$this->list_table]['fields'][$field]['label'][0]) : $field)];
                        } elseif (\is_array($this->arrDetailFields) && !empty($this->arrDetailFields) && \in_array($field, $this->arrDetailFields, true)) {
                            $arrOptions[] = ['name' => $field, 'label' => htmlspecialchars($GLOBALS['TL_DCA'][$this->list_table]['fields'][$field]['label'][0])];
                        }
                    }

                    $strOptions = $this->list_search;
                    $this->Template->search_searchfields = $arrOptions;
                    unset($arrOptions);

                    break;

                case 'dropdown':
                default:
                    if (\strlen(\Input::get('search')) && \strlen(\Input::get('for'))) {
                        $varKeyword = '%'.\Input::get('for').'%';

                        if (\in_array(\Input::get('search'), $this->arrOwnerFields, true)) {
                            $field = \Input::get('search');
                            if ('fd_member' === $field) {
                                $prop = 'arrMembers';
                            } elseif ('fd_member_group' === $field) {
                                $prop = 'arrMemberGroups';
                            } elseif ('fd_user' === $field) {
                                $prop = 'arrUsers';
                            } elseif ('fd_user_group' === $field) {
                                $prop = 'arrUserGroups';
                            }

                            $arrMatches = $this->array_filter_like($this->{$prop}, \Input::get('for'));
                            if (!empty($arrMatches)) {
                                $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').$field.' IN('.implode(',', array_keys($arrMatches)).')';
                            }
                        } elseif (\in_array(\Input::get('search'), $this->arrBaseFields, true)) {
                            $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').\Input::get('search').' LIKE ?';
                        } else {
                            $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').'(SELECT value FROM tl_formdata_details WHERE ff_name="'.\Input::get('search').'" AND pid=f.id ) LIKE ?';
                        }
                    }

                    foreach (trimsplit(',', $this->list_search) as $field) {
                        if (\in_array($field, $this->arrBaseFields, true)) {
                            if (\strlen($this->strFormKey) && 'form' === $field) {
                                continue;
                            }

                            $strOptions .= '  <option value="'.$field.'"'.(($field === \Input::get('search')) ? ' selected="selected"' : '').'>'.htmlspecialchars($GLOBALS['TL_DCA'][$this->list_table]['fields'][$field]['label'][0]).'</option>'."\n";
                        } elseif (\is_array($this->arrDetailFields) && !empty($this->arrDetailFields) && \in_array($field, $this->arrDetailFields, true)) {
                            $strOptions .= '  <option value="'.$field.'"'.(($field === \Input::get('search')) ? ' selected="selected"' : '').'>'.($GLOBALS['TL_DCA'][$this->list_table]['fields'][$field]['label'][0] ? htmlspecialchars($GLOBALS['TL_DCA'][$this->list_table]['fields'][$field]['label'][0]) : $field).'</option>'."\n";
                        }
                    }

                    break;
            }
        }

        if (!$blnExport) {
            $this->Template->search_fields = $strOptions;
        }

        /**
         * Get total number of records.
         */
        $strQuery = 'SELECT COUNT(*) AS count FROM '.$this->list_table.' f';
        $strListWhere = $this->prepareListWhere();

        if (\strlen($strListWhere)) {
            $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').$strListWhere;
        }
        if (\strlen($this->strFormKey)) {
            $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').$this->strFormFilterKey."='".$this->strFormFilterValue."'";
        }

        // replace insert tags in where, e.g. {{user::id}}
        $strWhere = $this->replaceWhereTags($strWhere);
        $strWhere = $this->replaceInsertTags($strWhere, false);

        $strQuery .= $strWhere;

        $objTotal = \Database::getInstance()->prepare($strQuery)->execute($varKeyword);
        $rowTotalcount = $objTotal->row();
        $intTotalcount = max(0, $rowTotalcount['count']);

        /**
         * Get the selected records.
         */
        $arrListFields = trimsplit(',', $this->list_fields);

        $intLastCol = -1;

        if ($this->Template->details || $this->Template->editable || $this->Template->deletable || $this->Template->exportable) {
            ++$intLastCol;
        }

        $strListFields = '';
        $strListFields .= 'id,alias';

        if (!empty($arrListFields)) {
            foreach ($arrListFields as $field) {
                // do not display field id
                if ('id' === $field) {
                    continue;
                }

                ++$intLastCol;

                if (isset($this->arrBaseFields)&&\in_array($field, $this->arrBaseFields, true)) {
                    $strListFields .= ','.$field;
                }

                if (\is_array($this->arrDetailFields) && !empty($this->arrDetailFields) && \in_array($field, $this->arrDetailFields, true)) {
                    $strListFields .= ',(SELECT value FROM tl_formdata_details WHERE ff_name="'.$field.'" AND pid=f.id) AS `'.$field.'`';
                }
            }
        }

        // member and user
        if (!\in_array('fd_user', $arrListFields, true)) {
            $strListFields .= ',fd_user';
        }
        if (!\in_array('fd_member', $arrListFields, true)) {
            $strListFields .= ',fd_member';
        }

        $strQuery = 'SELECT '.$strListFields.' FROM '.$this->list_table.' f';
        $strQuery .= $strWhere;

        // Order by
        $ord=\Input::get('order_by');
        if (isset($ord)&&\strlen($ord)) {
            if (\in_array(\Input::get('order_by'), $arrListFields, true) && (\in_array(\Input::get('order_by'), $this->arrBaseFields, true) || \in_array(\Input::get('order_by'), $this->arrDetailFields, true))) {
                if (isset($GLOBALS['TL_DCA']['tl_formdata']['fields'][\Input::get('order_by')]['eval']['rgxp']) && 'digit' === $GLOBALS['TL_DCA']['tl_formdata']['fields'][\Input::get('order_by')]['eval']['rgxp']) {
                    $strQuery .= ' ORDER BY CAST(`'.\Input::get('order_by').'` AS DECIMAL(20,5)) '.\Input::get('sort');
                } else {
                    $strQuery .= ' ORDER BY `'.\Input::get('order_by').'` '.\Input::get('sort');
                }
            }
        } elseif ($this->list_sort) {
            $strListSort = $this->list_sort;

            $arrListSort = explode(',', $strListSort);
            $arrSort = [];
            $arrSortSigned = ['digit', 'date', 'datim', 'time'];

            if (!empty($arrListSort)) {
                foreach ($arrListSort as $strSort) {
                    $strSort = trim($strSort);
                    preg_match_all('/^(.*?)(\s|$)/i', $strSort, $arrMatch);

                    if (!\in_array($arrMatch[1][0], $arrListFields, true)) {
                        if (\in_array($arrMatch[1][0], $this->arrDetailFields, true)) {
                            if (\in_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrMatch[1][0]]['eval']['rgxp'], $arrSortSigned, true)) {
                                $arrSort[] = preg_replace('/\b'.$arrMatch[1][0].'\b/i', 'CAST((SELECT value FROM tl_formdata_details WHERE ff_name="'.$arrMatch[1][0].'" AND pid=f.id) AS DECIMAL(20,5))', $strSort);
                            } else {
                                $arrSort[] = preg_replace('/\b'.$arrMatch[1][0].'\b/i', '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$arrMatch[1][0].'" AND pid=f.id)', $strSort);
                            }
                        } else {
                            if (\in_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrMatch[1][0]]['eval']['rgxp'], $arrSortSigned, true)) {
                                $arrSort[] = preg_replace('/\b'.$arrMatch[1][0].'\b/i', 'CAST(`'.$arrMatch[1][0].'` AS DECIMAL(20,5))', $strSort);
                            } else {
                                $arrSort[] = $strSort;
                            }
                        }
                    } else {
                        if (\in_array($GLOBALS['TL_DCA']['tl_formdata']['fields'][$arrMatch[1][0]]['eval']['rgxp'], $arrSortSigned, true)) {
                            $arrSort[] = preg_replace('/\b'.$arrMatch[1][0].'\b/i', 'CAST(`'.$arrMatch[1][0].'` AS DECIMAL(20,5))', $strSort);
                        } else {
                            $arrSort[] = $strSort;
                        }
                    }
                }
            }

            if (!empty($arrSort)) {
                $strListSort = ' ORDER BY '.implode(',', $arrSort);
            } else {
                $strListSort = '';
            }
            $strQuery .= $strListSort;
        }

        $objDataStmt = \Database::getInstance()->prepare($strQuery);

        // Limit
        if (!$blnExport) {
            if ((int) (\Input::get('per_page')) > 0) {
                $objDataStmt->limit(\Input::get('per_page'), (($page - 1) * $per_page));
            } elseif ((int) ($this->perPage) > 0) {
                $objDataStmt->limit($this->perPage, (($page - 1) * $per_page));
            }
        }

        $objData = $objDataStmt->execute($varKeyword);

        /**
         * Prepare URL.
         */
        $strUrl = $this->generateFrontendUrl($objPage->row());
        if ('/' === $strUrl || '//' === $strUrl) {
            $strUrl = '';
        }

        // Correctly handle the "index" alias
        if ('index' === $objPage->alias && ('' === $strUrl || (!$GLOBALS['TL_CONFIG']['rewriteURL'] && preg_match('/index\.php\/?/', $strUrl)))) {
            $strUrl = ($GLOBALS['TL_CONFIG']['rewriteURL'] ? 'index' : 'index.php/index');
        }

        $strUrlParams = '';
        $strUrlSuffix = $GLOBALS['TL_CONFIG']['urlSuffix'];

        if (!$blnExport) {
            $this->Template->url = $strUrl;
        }

        $blnQuery = false;

        foreach (preg_split('/&(amp;)?/', urldecode($_SERVER['QUERY_STRING'])) as $fragment) {
            if (\strlen($fragment)) {
                if (0 !== strncasecmp($fragment, 'file', 5) && 0 !== strncasecmp($fragment, 'act', 3) && 0 !== strncasecmp($fragment, 'order_by', 8) && 0 !== strncasecmp($fragment, 'sort', 4) && 0 !== strncasecmp($fragment, $page_get, \strlen($page_get))) {
                    $strUrlParams .= (!$blnQuery ? '' : '&amp;').$fragment;
                    $blnQuery = true;
                }
            }
        }

        /**
         * Prepare data arrays.
         */
        $arrTh = [];
        $arrTd = [];

        $arrFields = $arrListFields;

        $intRowCounter = -1;
        $intColCounter = 0;

        ++$intRowCounter;

        $ignoreFields = ['id', 'pid'];

        // THEAD
        if (!$blnExport) {
            for ($i = 0; $i < \count($arrFields); ++$i) {
                // do not display some special fields
                if (\in_array($arrFields[$i], $ignoreFields, true) || 'password' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$arrFields[$i]]['inputType']) {
                    continue;
                }

                $class = '';
                $sort = 'asc';
                $strField = (isset($GLOBALS['TL_DCA'][$this->list_table]['fields'][$arrFields[$i]]['label'][0])&&\strlen($label = $GLOBALS['TL_DCA'][$this->list_table]['fields'][$arrFields[$i]]['label'][0])) ? $label : $arrFields[$i];

                if (\Input::get('order_by') === $arrFields[$i]) {
                    $sort = ('asc' === \Input::get('sort')) ? 'desc' : 'asc';
                    $class = ' sorted '.\Input::get('sort');
                }

                // add CSS class defined in form generator
                if (isset($GLOBALS['TL_DCA'][$this->list_table]['fields'][$arrFields[$i]]['ff_class']) && \strlen($GLOBALS['TL_DCA'][$this->list_table]['fields'][$arrFields[$i]]['ff_class'])) {
                    $class .= ' '.$GLOBALS['TL_DCA'][$this->list_table]['fields'][$arrFields[$i]]['ff_class'];
                }

                $arrTh[] = [
                    'link' => htmlspecialchars($strField),
                    'href' => $strUrl.(\strlen($strUrlParams) ? '?'.$strUrlParams.'&amp;' : '?').'order_by='.$arrFields[$i].'&amp;sort='.$sort,
                    'title' => htmlspecialchars(sprintf($GLOBALS['TL_LANG']['MSC']['list_orderBy'], $strField)),
                    'class' => $class.((0 === $i) ? ' col_first' : ''),
                ];
            }
        } else {
            $strExpEncl = '"';
            $strExpSep = '';

            if ('xls' === $strExportMode) {
                if (!$blnCustomXlsExport) {
                    $xls = new xlsexport();
                    $strXlsSheet = 'Export';
                    $xls->addworksheet($strXlsSheet);
                }
            } else { // defaults to csv
                header('Content-Type: appplication/csv; charset='.($this->blnExportUTF8Decode ? $this->strExportConvertToCharset : 'utf-8'));
                header('Content-Transfer-Encoding: binary');
                header('Content-Disposition: attachment; filename="export_'.$this->strFormKey.'_'.date('Ymd_His').'.csv"');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Expires: 0');

                $strExpSep = '';
            }

            $intColCounter = -1;

            for ($i = 0; $i < \count($arrFields); ++$i) {
                $v = $arrFields[$i];

                if (\in_array($v, $ignoreFields, true)) {
                    continue;
                }

                ++$intColCounter;

                if ($useFieldNames) {
                    $strName = $v;
                } elseif (\strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0])) {
                    $strName = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0];
                } elseif (\strlen($GLOBALS['TL_LANG']['tl_formdata'][$v][0])) {
                    $strName = $GLOBALS['TL_LANG']['tl_formdata'][$v][0];
                } else {
                    $strName = strtoupper($v);
                }

                if (\strlen($strName)) {
                    $strName = \StringUtil::decodeEntities($strName);
                }

                if ($this->blnExportUTF8Decode || ('xls' === $strExportMode && !$blnCustomXlsExport)) {
                    $strName = $this->convertEncoding($strName, $GLOBALS['TL_CONFIG']['characterSet'], $this->strExportConvertToCharset);
                }

                if ('csv' === $strExportMode) {
                    $strName = str_replace('"', '""', $strName);
                    echo $strExpSep.$strExpEncl.$strName.$strExpEncl;

                    $strExpSep = ';';
                } elseif ('xls' === $strExportMode) {
                    if (!$blnCustomXlsExport) {
                        $xls->setcell(['sheetname' => $strXlsSheet, 'row' => $intRowCounter, 'col' => $intColCounter, 'data' => $strName, 'fontweight' => XLSFONT_BOLD, 'vallign' => XLSXF_VALLIGN_TOP, 'fontfamily' => XLSFONT_FAMILY_NORMAL]);
                        $xls->setcolwidth($strXlsSheet, $intColCounter, 0x1aff);
                    } else {
                        $arrHookDataColumns[$v] = $strName;
                    }
                }
            }

            ++$intRowCounter;

            if ('csv' === $strExportMode) {
                echo "\n";
            }
        }

        // Data result
        $arrRows = $objData->fetchAllAssoc();

        if (!$blnExport) {
            // also store as list of items
            $arrListItems = [];
            $arrEditAllowed = [];
            $arrDeleteAllowed = [];
            $arrExportAllowed = [];

            // TBODY
            for ($i = 0; $i < \count($arrRows); ++$i) {
                $class = 'row_'.$i.((0 === $i) ? ' row_first' : '').((($i + 1) === \count($arrRows)) ? ' row_last' : '').((($i % 2) === 0) ? ' even' : ' odd');

                // check edit access
                $blnEditAllowed = false;
                if ('none' === $this->efg_fe_edit_access) {
                    $blnEditAllowed = false;
                } elseif ('public' === $this->efg_fe_edit_access) {
                    $blnEditAllowed = true;
                } elseif (\strlen($this->efg_fe_edit_access)) {
                    if (\in_array((int) ($arrRows[$i]['fd_member']), $this->arrAllowedEditOwnerIds, true)) {
                        $blnEditAllowed = true;
                    }
                }

                // check delete access
                $blnDeleteAllowed = false;
                if ('none' === $this->efg_fe_delete_access) {
                    $blnDeleteAllowed = false;
                } elseif ('public' === $this->efg_fe_delete_access) {
                    $blnDeleteAllowed = true;
                } elseif (\strlen($this->efg_fe_delete_access)) {
                    if (\in_array((int) ($arrRows[$i]['fd_member']), $this->arrAllowedDeleteOwnerIds, true)) {
                        $blnDeleteAllowed = true;
                    }
                }

                // check export access
                $blnExportAllowed = false;
                if ('none' === $this->efg_fe_export_access) {
                    $blnExportAllowed = false;
                } elseif ('public' === $this->efg_fe_export_access) {
                    $blnExportAllowed = true;
                } elseif (\strlen($this->efg_fe_export_access)) {
                    if (\in_array((int) ($arrRows[$i]['fd_member']), $this->arrAllowedExportOwnerIds, true)) {
                        $blnExportAllowed = true;
                    }
                }

                $arrEditAllowed[$arrRows[$i]['id']] = $blnEditAllowed;
                $arrDeleteAllowed[$arrRows[$i]['id']] = $blnDeleteAllowed;
                $arrExportAllowed[$arrRows[$i]['id']] = $blnExportAllowed;

                $j = 0;

                foreach ($arrListFields as $intKey => $strVal) {
                    $k = $strVal;
                    $v = $arrRows[$i][$k];

                    // do not display some special fields
                    if (\in_array($k, $ignoreFields, true) || 'password' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']) {
                        continue;
                    }

                    $strLinkDetails = '';
                    if (\strlen($arrRows[$i]['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) {
                        $strLinkDetails = str_replace($strUrlSuffix, '', $strUrl).(\strlen($strUrl) ? '/' : '').$this->strDetailKey.'/'.$arrRows[$i]['alias'].$strUrlSuffix.(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
                    } else {
                        $strLinkDetails = $strUrl.'?'.$this->strDetailKey.'='.$arrRows[$i]['id'].(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
                    }

                    $strLinkEdit = '';
                    if ($arrEditAllowed[$arrRows[$i]['id']]) {
                        if (\strlen($arrRows[$i]['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) {
                            $strLinkEdit = str_replace($strUrlSuffix, '', $strUrl).(\strlen($strUrl) ? '/' : '').$this->strDetailKey.'/'.$arrRows[$i]['alias'].$strUrlSuffix.'?act=edit'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
                        } else {
                            $strLinkEdit = $strUrl.'?'.$this->strDetailKey.'='.$arrRows[$i]['id'].'&amp;act=edit'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
                        }
                    }

                    $strLinkDelete = '';
                    if ($arrDeleteAllowed[$arrRows[$i]['id']]) {
                        if (\strlen($arrRows[$i]['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) {
                            $strLinkDelete = str_replace($strUrlSuffix, '', $strUrl).(\strlen($strUrl) ? '/' : '').$this->strDetailKey.'/'.$arrRows[$i]['alias'].$strUrlSuffix.'?act=delete'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
                        } else {
                            $strLinkDelete = $strUrl.'?'.$this->strDetailKey.'='.$arrRows[$i]['id'].'&amp;act=delete'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
                        }
                    }

                    $strLinkExport = '';
                    if ($arrExportAllowed[$arrRows[$i]['id']]) {
                        if (\strlen($arrRows[$i]['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) {
                            $strLinkExport = str_replace($strUrlSuffix, '', $strUrl).(\strlen($strUrl) ? '/' : '').$this->strDetailKey.'/'.$arrRows[$i]['alias'].$strUrlSuffix.'?act=export'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
                        } else {
                            $strLinkExport = $strUrl.'?'.$this->strDetailKey.'='.$arrRows[$i]['id'].'&amp;act=export'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
                        }
                    }

                    $value = $this->formatValue($k, $v);
                    $v = \StringUtil::decodeEntities($v);

                    if ('fileTree' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType'] && true === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['multiple']) {
                        $strSep = (isset($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['csv'] : '|';
                        $v = (\is_string($v) && false !== strpos($v, $strSep)) ? explode($strSep, $v) : deserialize($v);
                    }

                    // add CSS class defined in form generator
                    $ff_class = '';
                    if (isset($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['ff_class']) && \strlen($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['ff_class'])) {
                        $ff_class = ' '.$GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['ff_class'];
                    }

                    $arrTd[$class][] = [
                        'id' => $arrRows[$i]['id'],
                        'alias' => $arrRows[$i]['alias'],
                        'content' => ('' !== $value) ? $value : '&nbsp;',
                        'raw' => $v,
                        'class' => 'col_'.$j.$ff_class.((0 === $j) ? ' col_first' : '').(($j === $intLastCol) ? ' col_last' : ''),
                        'link_details' => $strLinkDetails,
                        'link_edit' => $strLinkEdit,
                        'link_delete' => $strLinkDelete,
                        'link_export' => $strLinkExport,
                    ];

                    // store also as item
                    $arrListItems[$i][$k] = [
                        'id' => $arrRows[$i]['id'],
                        'alias' => $arrRows[$i]['alias'],
                        'name' => $k,
                        'label' => (isset($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['label'][0])&&\strlen($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['label'][0])) ? htmlspecialchars($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['label'][0]) : htmlspecialchars($k),
                        'content' => ('' !== $value) ? $value : '&nbsp;',
                        'raw' => $v,
                        'class' => 'field_'.$j.$ff_class.((0 === $j) ? ' field_first' : '').(($j === ($intLastCol - 1)) ? ' field_last' : ''),
                        'record_class' => str_replace('row_', 'record_', $class),
                        'link_details' => $strLinkDetails,
                        'link_edit' => $strLinkEdit,
                        'link_delete' => $strLinkDelete,
                        'link_export' => $strLinkExport,
                    ];

                    if ('fileTree' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']) {
                        $value = $arrListItems[$i][$k]['raw'];

                        if (\is_string($value) && \strlen($value) && is_dir(TL_ROOT.'/'.$value)) {
                            $arrTd[$class][\count($arrTd[$class]) - 1]['content'] = '&nbsp;';
                            $arrListItems[$i][$k]['content'] = '&nbsp;';
                        }
                        // single file
                        elseif (\is_string($value) && \strlen($value) && is_file(TL_ROOT.'/'.$value)) {
                            $objFile = new \File($value);
                            if (!\in_array($objFile->extension, $allowedDownload, true)) {
                                $arrTd[$class][\count($arrTd[$class]) - 1]['content'] = '&nbsp;';
                                $arrListItems[$i][$k]['content'] = '&nbsp;';
                            } else {
                                $arrTd[$class][\count($arrTd[$class]) - 1]['type'] = 'file';
                                $arrTd[$class][\count($arrTd[$class]) - 1]['src'] = $this->urlEncode($value);
                                $arrListItems[$i][$k]['type'] = 'file';
                                $arrListItems[$i][$k]['src'] = $this->urlEncode($value);
                                if ('image/' === substr($objFile->mime, 0, 6)) {
                                    $arrTd[$class][\count($arrTd[$class]) - 1]['display'] = 'image';
                                    $arrListItems[$i][$k]['display'] = 'image';
                                } else {
                                    $size = ' ('.number_format(($objFile->filesize / 1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']).' kB)';

                                    $href = preg_replace('@(\?|&amp;)download=.*?(&amp;|$)@si', '', \Environment::get('request'));
                                    $href .= ((strpos($href, '?') >= 1) ? '&amp;' : '?').'download='.$arrRows[$i]['id'].'.'.$k;
                                    $href = ampersand($href);

                                    $arrTd[$class][\count($arrTd[$class]) - 1]['display'] = 'download';
                                    $arrTd[$class][\count($arrTd[$class]) - 1]['size'] = $size;
                                    $arrTd[$class][\count($arrTd[$class]) - 1]['href'] = $href;
                                    $arrTd[$class][\count($arrTd[$class]) - 1]['linkTitle'] = basename($objFile->basename);
                                    $arrTd[$class][\count($arrTd[$class]) - 1]['icon'] = $this->strIconFolder.'/'.$objFile->icon;

                                    $arrListItems[$i][$k]['display'] = 'download';
                                    $arrListItems[$i][$k]['size'] = $size;
                                    $arrListItems[$i][$k]['href'] = $href;
                                    $arrListItems[$i][$k]['linkTitle'] = basename($objFile->basename);
                                    $arrListItems[$i][$k]['icon'] = $this->strIconFolder.'/'.$objFile->icon;
                                }
                            }
                        }
                        // multiple files
                        elseif (\is_array($value)) {
                            $arrTemp = [];
                            $keyTemp = -1;

                            $arrTd[$class][\count($arrTd[$class]) - 1]['type'] = 'file';
                            $arrListItems[$i][$k]['type'] = 'file';

                            foreach ($value as $kF => $strFile) {
                                if (\strlen($strFile) && is_file(TL_ROOT.'/'.$strFile)) {
                                    $objFile = new \File($strFile);

                                    if (!\in_array($objFile->extension, $allowedDownload, true)) {
                                        unset($arrListItems[$i][$k]['raw'][$kF]);
                                        continue;
                                    }

                                    ++$keyTemp;

                                    $arrTemp[$keyTemp]['src'] = $this->urlEncode($strFile);

                                    if ('image/' === substr($objFile->mime, 0, 6)) {
                                        $arrTemp[$keyTemp]['display'] = 'image';
                                    } else {
                                        $size = ' ('.number_format(($objFile->filesize / 1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']).' kB)';

                                        $href = preg_replace('@(\?|&amp;)download=.*?(&amp;|$)@si', '', \Environment::get('request'));
                                        $href .= ((strpos($href, '?') >= 1) ? '&amp;' : '?').'download='.$arrRows[$i]['id'].'.'.$k;
                                        $href = ampersand($href);

                                        $arrTemp[$keyTemp]['display'] = 'download';
                                        $arrTemp[$keyTemp]['size'] = $size;
                                        $arrTemp[$keyTemp]['href'] = $href;
                                        $arrTemp[$keyTemp]['linkTitle'] = basename($objFile->basename);
                                        $arrTemp[$keyTemp]['icon'] = $this->strIconFolder.'/'.$objFile->icon;
                                    }
                                }
                            }

                            $arrTd[$class][\count($arrTd[$class]) - 1]['content'] = $arrTemp;
                            $arrListItems[$i][$k]['content'] = $arrTemp;

                            $arrTd[$class][\count($arrTd[$class]) - 1]['multiple'] = true;
                            $arrTd[$class][\count($arrTd[$class]) - 1]['number_of_items'] = \count($arrTemp);
                            $arrListItems[$i][$k]['multiple'] = true;
                            $arrListItems[$i][$k]['number_of_items'] = \count($arrTemp);

                            unset($arrTemp);
                        }
                    }
                    ++$j;
                }
            }

            $strTotalNumberOfItems = number_format((int) $intTotalcount, 0, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']);
            $this->Template->totalNumberOfItems = [
                'raw' => (int) $intTotalcount,
                'formatted' => $strTotalNumberOfItems,
                'content' => sprintf($GLOBALS['TL_LANG']['MSC']['efgTotalNumberOfItems'], $strTotalNumberOfItems),
            ];

            $this->Template->thead = $arrTh;
            $this->Template->tbody = $arrTd;

            $this->Template->listItems = $arrListItems;

            $this->Template->arrEditAllowed = $arrEditAllowed;
            $this->Template->arrDeleteAllowed = $arrDeleteAllowed;
            $this->Template->arrExportAllowed = $arrExportAllowed;

            /*
             * Pagination
             */
            if ((int) $per_page > 0) {
                $objPagination = new \Pagination($intTotalcount, $per_page, 7, $page_get);
                $this->Template->pagination = $objPagination->generate("\n  ");
            }

            /*
             * Template variables
             */
            $this->Template->action = ampersand(urldecode(\Environment::get('request')));
            $this->Template->per_page_label = specialchars($GLOBALS['TL_LANG']['MSC']['list_perPage']);
            $this->Template->search_label = specialchars($GLOBALS['TL_LANG']['MSC']['search']);
            $this->Template->per_page = \Input::get('per_page');
            if ((int) ($this->perPage) > 0) {
                $this->Template->list_perPage = $this->perPage;
            }
            $this->Template->search = \Input::get('search');
            $this->Template->for = \Input::get('for');
            $this->Template->order_by = \Input::get('order_by');
            $this->Template->sort = \Input::get('sort');
            $this->Template->col_last = 'col_'.$intLastCol;
        } else {
            // Process result and format values
            foreach ($arrRows as $row) {
                $args = [];

                $strExpEncl = '"';
                $strExpSep = '';

                $intColCounter = -1;

                // check export access
                $blnExportAllowed = false;
                if ('none' === $this->efg_fe_export_access) {
                    $blnExportAllowed = false;
                } elseif ('public' === $this->efg_fe_export_access) {
                    $blnExportAllowed = true;
                } elseif (\strlen($this->efg_fe_export_access)) {
                    if (\in_array((int) ($row['fd_member']), $this->arrAllowedExportOwnerIds, true)) {
                        $blnExportAllowed = true;
                    }
                }

                if (false === $blnExportAllowed) {
                    continue;
                }

                // Prepare field value
                foreach ($arrFields as $k => $v) {
                    if (\in_array($v, $ignoreFields, true)) {
                        continue;
                    }

                    ++$intColCounter;

                    $strVal = '';
                    $strVal = $row[$v];

                    if ('date' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '');
                    } elseif ('time' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['timeFormat'], $row[$v]) : '');
                    } elseif ('datim' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '');
                    } elseif ('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                        if (1 === $useFormValues) {
                            // single value checkboxes don't have options
                            if ((\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && !empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']))) {
                                $strVal = \strlen($row[$v]) ? key($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) : '';
                            } else {
                                $strVal = $row[$v];
                            }
                        } else {
                            $strVal = \strlen($row[$v]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0] : '-';
                        }
                    } elseif ('radio' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'efgLookupRadio' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'select' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'efgLookupSelect' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'efgLookupCheckbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']) {
                        $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'] : '|';

                        // take the assigned value instead of the user readable output
                        if (1 === $useFormValues) {
                            if ((false === strpos($row[$v], $strSep)) && (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && !empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']))) {
                                $options = array_flip($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']);
                                $strVal = $options[$row[$v]];
                            } else {
                                if ((\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && !empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']))) {
                                    $options = array_flip($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']);
                                    $tmparr = explode($strSep, $row[$v]);
                                    $fieldvalues = [];
                                    foreach ($tmparr as $valuedesc) {
                                        $fieldvalues[] = $options[$valuedesc];
                                    }
                                    $strVal = implode(",\n", $fieldvalues);
                                } else {
                                    $strVal = \strlen($row[$v]) ? str_replace($strSep, ",\n", $row[$v]) : '';
                                }
                            }
                        } else {
                            $strVal = \strlen($row[$v]) ? str_replace($strSep, ",\n", $row[$v]) : '';
                        }
                    } elseif ('fileTree' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']) {
                        $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'] : '|';

                        if (\is_string($row[$v]) && false !== strpos($row[$v], $strSep)) {
                            $strVal = implode(",\n", explode($strSep, $row[$v]));
                        } else {
                            $strVal = implode(",\n", deserialize($row[$v], true));
                        }
                    } else {
                        $row_v = deserialize($row[$v]);

                        if (\is_array($row_v)) {
                            $args_k = [];

                            foreach ($row_v as $option) {
                                $args_k[] = \strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$option]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$option] : $option;
                            }

                            $args[$k] = implode(",\n", $args_k);
                        } elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]])) {
                            $args[$k] = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]];
                        } else {
                            $args[$k] = $row[$v];
                        }
                        $strVal = null === $args[$k] ? $args[$k] : vsprintf('%s', $args[$k]);
                    }

                    if (\in_array($v, $this->arrBaseFields, true) || \in_array($v, $this->arrOwnerFields, true)) {
                        if ('fd_member' === $v) {
                            $strVal = $this->arrMembers[(int) ($row[$v])];
                        } elseif ('fd_user' === $v) {
                            $strVal = $this->arrUsers[(int) ($row[$v])];
                        } elseif ('fd_member_group' === $v) {
                            $strVal = $this->arrMemberGroups[(int) ($row[$v])];
                        } elseif ('fd_user_group' === $v) {
                            $strVal = $this->arrUserGroups[(int) ($row[$v])];
                        }
                    }

                    if (\strlen($strVal)) {
                        $strVal = \StringUtil::decodeEntities($strVal);
                        $strVal = preg_replace(['/<br.*\/*>/si'], ["\n"], $strVal);

                        if ($this->blnExportUTF8Decode || ('xls' === $strExportMode && !$blnCustomXlsExport)) {
                            $strVal = $this->convertEncoding($strVal, $GLOBALS['TL_CONFIG']['characterSet'], $this->strExportConvertToCharset);
                        }
                    }

                    if ('csv' === $strExportMode) {
                        $strVal = str_replace('"', '""', $strVal);
                        echo $strExpSep.$strExpEncl.$strVal.$strExpEncl;

                        $strExpSep = ';';
                    } elseif ('xls' === $strExportMode) {
                        if (!$blnCustomXlsExport) {
                            $xls->setcell(['sheetname' => $strXlsSheet, 'row' => $intRowCounter, 'col' => $intColCounter, 'data' => $strVal, 'vallign' => XLSXF_VALLIGN_TOP, 'fontfamily' => XLSFONT_FAMILY_NORMAL]);
                        } else {
                            $arrHookData[$intRowCounter][$v] = $strVal;
                        }
                    }
                }

                ++$intRowCounter;

                if ('csv' === $strExportMode) {
                    $strExpSep = '';
                    echo "\n";
                }
            }

            if ('xls' === $strExportMode) {
                if (!$blnCustomXlsExport) {
                    $xls->sendfile('export_'.$this->strFormKey.'_'.date('Ymd_His').'.xls');
                    exit;
                }

                foreach ($GLOBALS['TL_HOOKS']['efgExportXls'] as $key => $callback) {
                    $this->import($callback[0]);
                    $res = $this->{$callback[0]}->{$callback[1]}($arrHookDataColumns, $arrHookData);       //Änderung PBD
                }
            }
            exit;
        }
    }

    /**
     * List a single record.
     */
    protected function listSingleRecord(): void
    {
        global $objPage;

        /**
         * Prepare URL.
         */
        $page_get = 'page_fd'.$this->id;
        $strUrl = preg_replace('/\?.*$/', '', urldecode(\Environment::get('request')));
        $strUrlParams = '';

        $blnQuery = false;
        foreach (preg_split('/&(amp;)?/', urldecode($_SERVER['QUERY_STRING'])) as $fragment) {
            if (\strlen($fragment)) {
                if (0 !== strncasecmp($fragment, 'file', 5) && 0 !== strncasecmp($fragment, $this->strDetailKey, \strlen($this->strDetailKey)) && 0 !== strncasecmp($fragment, 'order_by', 8) && 0 !== strncasecmp($fragment, 'sort', 4) && 0 !== strncasecmp($fragment, $page_get, \strlen($page_get))) {
                    $strUrlParams .= (!$blnQuery ? '' : '&amp;').$fragment;
                    $blnQuery = true;
                }
            }
        }

        // check record
        if ((int) ($this->intRecordId) < 1) {
            $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i'], ['', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
            \Controller::redirect($strRed);
        }

        // check access
        if (\strlen($this->efg_list_access) && 'public' !== $this->efg_list_access) {
            $objOwner = \Database::getInstance()->prepare('SELECT fd_member FROM tl_formdata WHERE id=?')
                ->execute($this->intRecordId)
            ;

            $varOwner = $objOwner->fetchAssoc();
            if (!\in_array((int) ($varOwner['fd_member']), $this->arrAllowedOwnerIds, true)) {
                $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i'], ['', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
                \Controller::redirect($strRed);
            }
        }

        // check edit access
        $blnEditAllowed = false;
        if ('none' === $this->efg_fe_edit_access) {
            $blnEditAllowed = false;
        } elseif ('public' === $this->efg_fe_edit_access) {
            $blnEditAllowed = true;
        } elseif (\strlen($this->efg_fe_edit_access)) {
            $objOwner = \Database::getInstance()->prepare('SELECT fd_member FROM tl_formdata WHERE id=?')
                ->execute($this->intRecordId)
            ;
            $varOwner = $objOwner->fetchAssoc();
            if (\in_array((int) ($varOwner['fd_member']), $this->arrAllowedEditOwnerIds, true)) {
                $blnEditAllowed = true;
            }
        }

        // check delete access
        $blnDeleteAllowed = false;
        if ('none' === $this->efg_fe_delete_access) {
            $blnDeleteAllowed = false;
        } elseif ('public' === $this->efg_fe_delete_access) {
            $blnDeleteAllowed = true;
        } elseif (\strlen($this->efg_fe_delete_access)) {
            $objOwner = \Database::getInstance()->prepare('SELECT fd_member FROM tl_formdata WHERE id=?')
                ->execute($this->intRecordId)
            ;
            $varOwner = $objOwner->fetchAssoc();
            if (\in_array((int) ($varOwner['fd_member']), $this->arrAllowedDeleteOwnerIds, true)) {
                $blnDeleteAllowed = true;
            }
        }

        // check export access
        $blnExportAllowed = false;
        if ('none' === $this->efg_fe_export_access) {
            $blnExportAllowed = false;
        } elseif ('public' === $this->efg_fe_export_access) {
            $blnExportAllowed = true;
        } elseif (\strlen($this->efg_fe_export_access)) {
            $objOwner = \Database::getInstance()->prepare('SELECT fd_member FROM tl_formdata WHERE id=?')
                ->execute($this->intRecordId)
            ;
            $varOwner = $objOwner->fetchAssoc();
            if (\in_array((int) ($varOwner['fd_member']), $this->arrAllowedExportOwnerIds, true)) {
                $blnExportAllowed = true;
            }
        }

        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        // Fallback template
        if ('' === $this->list_info_layout) {
            $this->list_info_layout = 'info_fd_table_default';
        }

        $this->Template = new \FrontendTemplate($this->list_info_layout);

        $this->Template->textlink_details = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_details'];
        $this->Template->textlink_edit = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_edit'];
        $this->Template->textlink_delete = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_delete'];
        $this->Template->text_confirmDelete = $GLOBALS['TL_LANG']['tl_formdata']['fe_deleteConfirm'];
        $this->Template->textlink_export = $GLOBALS['TL_LANG']['tl_formdata']['fe_link_export'];
        $this->Template->iconFolder = $this->strIconFolder;

        $this->Template->editAllowed = $blnEditAllowed;
        $this->Template->deleteAllowed = $blnDeleteAllowed;
        $this->Template->exportAllowed = $blnExportAllowed;

        $this->list_info = deserialize($this->list_info);

        $this->Template->record = [];

        // also store as single item
        $this->Template->listItem = [];

        $arrListFields = explode(',', $this->list_info);
        $strSep = '';

        // wildcards * and -
        if ('*' === $arrListFields[0]) {
            $arrTempFields = array_merge($this->arrBaseFields, $this->arrDetailFields);
            foreach ($arrListFields as $field) {
                if ('-' === substr($field, 0, 1)) {
                    $intKey = array_search(substr($field, 1), $arrTempFields, true);
                    if (!\is_bool($intKey)) {
                        unset($arrTempFields[$intKey]);
                    }
                }
            }
            $arrListFields = $arrTempFields;
        }

        $strQuery = 'SELECT ';
        $strWhere = '';

        foreach ($arrListFields as $field) {
            if (\in_array($field, $this->arrBaseFields, true)) {
                $strQuery .= $strSep.$field;
                $strSep = ', ';
            }
            if (!empty($this->arrDetailFields) && \in_array($field, $this->arrDetailFields, true)) {
                $strQuery .= $strSep.'(SELECT value FROM tl_formdata_details WHERE ff_name="'.$field.'" AND pid=f.id ) AS `'.$field.'`';
                $strSep = ', ';
            }
        }

        $strQuery .= ' FROM '.$this->list_table.' f';
        $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').'id=?';
        $strQuery .= $strWhere;

        $objRecord = \Database::getInstance()->prepare($strQuery)
            ->limit(1)
            ->execute($this->intRecordId)
        ;

        if ($objRecord->numRows < 1) {
            return;
        }

        $arrFields = [];
        $arrRow = $objRecord->fetchAssoc();
        $count = -1;

        $strLinkEdit = '';
        if ($blnEditAllowed) {
            if (\strlen($arrRow['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) {
                $strLinkEdit = $strUrl.'?act=edit'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
            } else {
                $strLinkEdit = $strUrl.'?'.$this->strDetailKey.'='.$this->intRecordId.'&amp;act=edit'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
            }
        }

        $strLinkDelete = '';
        if ($blnDeleteAllowed) {
            if (\strlen($arrRow['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) {
                $strLinkDelete = $strUrl.'?act=delete'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
            } else {
                $strLinkDelete = $strUrl.'?'.$this->strDetailKey.'='.$this->intRecordId.'&amp;act=delete'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
            }
        }

        $strLinkExport = '';
        if ($blnExportAllowed) {
            if (\strlen($arrRow['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) {
                $strLinkExport = $strUrl.'?act=export'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
            } else {
                $strLinkExport = $strUrl.'?'.$this->strDetailKey.'='.$this->intRecordId.'&amp;act=export'.(\strlen($strUrlParams) ? '&amp;'.$strUrlParams : '');
            }
        }

        $arrItem = [];

        foreach ($arrListFields as $intKey => $strVal) {
            $k = $strVal;
            $v = $arrRow[$k];

            $value = $this->formatValue($k, $v);
            $v = deserialize(\StringUtil::decodeEntities($v));

            if ('fileTree' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType'] && true === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['eval']['multiple']) {
                $v = (\is_string($v) && false !== strpos($v, '|')) ? explode('|', $v) : deserialize($v);
            }

            $class = 'row_'.++$count.((0 === $count) ? ' row_first' : '').(($count >= (\count($arrListFields) - 1)) ? ' row_last' : '').((($count % 2) === 0) ? ' even' : ' odd');

            // add CSS class defined in form generator
            if (isset($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['ff_class']) && \strlen($GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['ff_class'])) {
                $class .= ' '.$GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['ff_class'];
            }

            $arrFields[$class] = [
                'label' => (\strlen($label = $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['label'][0]) ? htmlspecialchars($label) : htmlspecialchars($this->arrFF[$k]['label'])),
                'content' => $value,
                'raw' => $v,
            ];

            $arrItem[$k] = [
                'name' => $k,
                'label' => (\strlen($label = $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['label'][0]) ? htmlspecialchars($label) : htmlspecialchars($this->arrFF[$k]['label'])),
                'content' => $value,
                'raw' => $v,
                'class' => str_replace('row_', 'field_', $class),
            ];

            if ('fileTree' === $GLOBALS['TL_DCA'][$this->list_table]['fields'][$k]['inputType']) {
                if (is_dir(TL_ROOT.'/'.$arrFields[$class]['content'])) {
                    $arrFields[$class]['content'] = '&nbsp;';
                    $arrItem[$k]['content'] = '&nbsp;';
                }

                // single file
                elseif (!\is_array($arrFields[$class]['raw']) && \strlen($arrFields[$class]['raw']) && is_file(TL_ROOT.'/'.$arrFields[$class]['raw'])) {
                    $objFile = new \File($arrFields[$class]['content']);

                    if (!\in_array($objFile->extension, $allowedDownload, true)) {
                        $arrFields[$class]['content'] = '&nbsp;';
                        $arrItem[$k]['content'] = '&nbsp;';
                    } else {
                        $arrFields[$class]['type'] = 'file';
                        $arrFields[$class]['src'] = $this->urlEncode($arrFields[$class]['content']);
                        $arrItem[$k]['type'] = 'file';
                        $arrItem[$k]['src'] = $this->urlEncode($arrFields[$class]['content']);
                        if ('image/' === substr($objFile->mime, 0, 6)) {
                            $arrFields[$class]['display'] = 'image';
                            $arrItem[$k]['display'] = 'image';
                        } else {
                            $size = ' ('.number_format(($objFile->filesize / 1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']).' kB)';

                            $href = preg_replace('@(\?|&amp;)download=.*?(&amp;|$)@si', '', \Environment::get('request'));
                            $href .= ((strpos($href, '?') >= 1) ? '&amp;' : '?').'download='.$this->intRecordId.'.'.$k;
                            $href = ampersand($href);

                            $arrFields[$class]['display'] = 'download';
                            $arrFields[$class]['size'] = $size;
                            $arrFields[$class]['href'] = $href;
                            $arrFields[$class]['linkTitle'] = basename($objFile->basename);
                            $arrFields[$class]['icon'] = $this->strIconFolder.'/'.$objFile->icon;

                            $arrItem[$k]['display'] = 'download';
                            $arrItem[$k]['size'] = $size;
                            $arrItem[$k]['href'] = $href;
                            $arrItem[$k]['linkTitle'] = basename($objFile->basename);
                            $arrItem[$k]['icon'] = $this->strIconFolder.'/'.$objFile->icon;
                        }
                    }
                }

                // multiple files
                elseif (\is_array($arrFields[$class]['raw'])) {
                    $arrTemp = [];
                    $keyTemp = -1;

                    $arrFields[$class]['type'] = 'file';
                    $arrItem[$k]['type'] = 'file';

                    foreach ($arrFields[$class]['raw'] as $kF => $strFile) {
                        if (\strlen($strFile) && is_file(TL_ROOT.'/'.$strFile)) {
                            $objFile = new \File($strFile);

                            if (!\in_array($objFile->extension, $allowedDownload, true)) {
                                unset($arrFields[$class]['raw'][$kF]);
                                continue;
                            }

                            ++$keyTemp;

                            $arrTemp[$keyTemp]['src'] = $this->urlEncode($strFile);

                            if ('image/' === substr($objFile->mime, 0, 6)) {
                                $arrTemp[$keyTemp]['display'] = 'image';
                            } else {
                                $size = ' ('.number_format(($objFile->filesize / 1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']).' kB)';

                                $href = preg_replace('@(\?|&amp;)download=.*?(&amp;|$)@si', '', \Environment::get('request'));
                                $href .= ((strpos($href, '?') >= 1) ? '&amp;' : '?').'download='.$this->intRecordId.'.'.$k;
                                $href = ampersand($href);

                                $arrTemp[$keyTemp]['display'] = 'download';
                                $arrTemp[$keyTemp]['size'] = $size;
                                $arrTemp[$keyTemp]['href'] = $href;
                                $arrTemp[$keyTemp]['linkTitle'] = basename($objFile->basename);
                                $arrTemp[$keyTemp]['icon'] = $this->strIconFolder.'/'.$objFile->icon;
                            }
                        }
                    }

                    $arrFields[$class]['content'] = $arrTemp;
                    $arrItem[$k]['content'] = $arrTemp;

                    $arrFields[$class]['multiple'] = true;
                    $arrFields[$class]['number_of_items'] = \count($arrTemp);
                    $arrItem[$k]['multiple'] = true;
                    $arrItem[$k]['number_of_items'] = \count($arrTemp);

                    unset($arrTemp);
                }
            }
        }

        /**
         * Prepare URL.
         */
        $strUrl = preg_replace('/\?.*$/', '', urldecode(\Environment::get('request')));
        $this->Template->url = $strUrl;
        $this->Template->listItem = $arrItem;
        $this->Template->record = $arrFields;
        $this->Template->recordID = $this->intRecordId;

        $this->Template->link_edit = $strLinkEdit;

        $this->Template->link_delete = $strLinkDelete;
        $this->Template->link_export = $strLinkExport;

        /*
         * Comments
         */
        if (!$this->efg_com_allow_comments || !\in_array('comments', \ModuleLoader::getActive(), true)) {
            $this->Template->allowComments = false;

            return;
        }

        $this->Template->allowComments = true;

        // Adjust the comments headline level
        $intHl = min((int) (str_replace('h', '', $this->hl)), 5);
        $this->Template->hlc = 'h'.($intHl + 1);

        $this->import('Comments');
        $arrNotifies = [];

        // Notify system administrator
        if ('notify_author' !== $this->efg_com_notify) {
            $arrNotifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
        }

        // Notify author
        if ('notify_admin' !== $this->efg_com_notify) {
            if ((int) ($objRecord->fd_user) > 0) {
                $objUser = \UserModel::findByPk($objRecord->fd_user);

                if (null !== $objUser && !empty($objUser->email)) {
                    $arrNotifies[] = $objUser->email;
                }
            }
            if ((int) ($objRecord->fd_member) > 0) {
                $objMember = \MemberModel::findByPk($objRecord->fd_member);

                if (null !== $objMember && !empty($objMember->email)) {
                    $arrNotifies[] = $objMember->email;
                }
            }
        }

        $objConfig = new \stdClass();

        $objConfig->perPage = $this->efg_com_per_page;
        $objConfig->order = $this->com_order;
        $objConfig->template = $this->com_template;
        $objConfig->requireLogin = $this->com_requireLogin;
        $objConfig->disableCaptcha = $this->com_disableCaptcha;
        $objConfig->bbcode = $this->com_bbcode;
        $objConfig->moderate = $this->com_moderate;

        $this->Comments->addCommentsToTemplate($this->Template, $objConfig, 'tl_formdata', $this->intRecordId, $arrNotifies);
    }

    /**
     * Export a single record.
     */
    protected function exportSingleRecord($strExportMode = 'csv'): void
    {
        /**
         * Prepare URL.
         */
        $page_get = 'page_fd'.$this->id;
        $strUrl = preg_replace('/\?.*$/', '', urldecode(\Environment::get('request')));
        $strUrlParams = '';

        $blnQuery = false;
        foreach (preg_split('/&(amp;)?/', urldecode($_SERVER['QUERY_STRING'])) as $fragment) {
            if (\strlen($fragment)) {
                if (0 !== strncasecmp($fragment, 'file', 5) && 0 !== strncasecmp($fragment, $this->strDetailKey, \strlen($this->strDetailKey)) && 0 !== strncasecmp($fragment, 'order_by', 8) && 0 !== strncasecmp($fragment, 'sort', 4) && 0 !== strncasecmp($fragment, $page_get, \strlen($page_get))) {
                    $strUrlParams .= (!$blnQuery ? '' : '&amp;').$fragment;
                    $blnQuery = true;
                }
            }
        }

        // Check record
        if (null === $this->intRecordId || (int) ($this->intRecordId) < 1) {
            return;
        }

        // Check access
        if (\strlen($this->efg_list_access) && 'public' !== $this->efg_list_access) {
            $objOwner = \Database::getInstance()->prepare('SELECT fd_member FROM tl_formdata WHERE id=?')
                ->execute($this->intRecordId)
            ;

            $varOwner = $objOwner->fetchAssoc();
            if (!\in_array((int) ($varOwner['fd_member']), $this->arrAllowedOwnerIds, true)) {
                $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i'], ['', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
                \Controller::redirect($strRed);
            }
        }

        // Check export access
        $blnExportAllowed = false;
        if ('none' === $this->efg_fe_export_access) {
            $blnExportAllowed = false;
        } elseif ('public' === $this->efg_fe_export_access) {
            $blnExportAllowed = true;
        } elseif (\strlen($this->efg_fe_export_access)) {
            $objOwner = \Database::getInstance()->prepare('SELECT fd_member FROM tl_formdata WHERE id=?')
                ->execute($this->intRecordId)
            ;

            $varOwner = $objOwner->fetchAssoc();
            if (\in_array((int) ($varOwner['fd_member']), $this->arrAllowedExportOwnerIds, true)) {
                $blnExportAllowed = true;
            }
        }

        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        $this->list_info = deserialize($this->list_info);
        $arrListFields = explode(',', $this->list_info);
        $strSep = '';

        // wildcards * and -
        if ('*' === $arrListFields[0]) {
            $arrTempFields = array_merge($this->arrBaseFields, $this->arrDetailFields);
            foreach ($arrListFields as $field) {
                if ('-' === substr($field, 0, 1)) {
                    $intKey = array_search(substr($field, 1), $arrTempFields, true);
                    if (!\is_bool($intKey)) {
                        unset($arrTempFields[$intKey]);
                    }
                }
            }
            $arrListFields = $arrTempFields;
        }

        $strQuery = 'SELECT ';
        $strWhere = '';

        foreach ($arrListFields as $field) {
            if (\in_array($field, $this->arrBaseFields, true)) {
                $strQuery .= $strSep.$field;
                $strSep = ', ';
            }
            if (!empty($this->arrDetailFields) && \in_array($field, $this->arrDetailFields, true)) {
                $strQuery .= $strSep.'(SELECT value FROM tl_formdata_details WHERE ff_name="'.$field.'" AND pid=f.id ) AS `'.$field.'`';
                $strSep = ', ';
            }
        }

        $strQuery .= ' FROM '.$this->list_table.' f';
        $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').'id=?';
        $strQuery .= $strWhere;

        $objRecords = \Database::getInstance()->prepare($strQuery)
            ->limit(1)
            ->execute($this->intRecordId)
        ;

        if ($objRecords->numRows < 1) {
            return;
        }

        $ignoreFields = ['id', 'alias', 'tstamp', 'sorting', 'ip', 'published'];

        $showFields = array_diff($arrListFields, $ignoreFields);

        $intRowCounter = -1;

        $strExpEncl = '"';
        $strExpSep = '';

        $useFormValues = $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['useFormValues'];
        $useFieldNames = $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['useFieldNames'];

        $blnCustomXlsExport = false;
        $arrHookData = [];
        $arrHookDataColumns = [];

        if ('xls' === $strExportMode) {
            // check for HOOK efgExportXls
            if (\array_key_exists('efgExportXls', $GLOBALS['TL_HOOKS']) && \is_array($GLOBALS['TL_HOOKS']['efgExportXls'])) {
                $blnCustomXlsExport = true;
            } else {
                include TL_ROOT.'/system/modules/efg_co4/plugins/xls_export/xls_export.php';
            }

            if (!$blnCustomXlsExport) {
                $xls = new xlsexport();

                $strXlsSheet = 'Export';
                $xls->addworksheet($strXlsSheet);
            }
        } else { // defaults to csv
            header('Content-Type: appplication/csv; charset='.($this->blnExportUTF8Decode ? $this->strExportConvertToCharset : 'utf-8'));
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="export_'.$this->strFormKey.'_'.date('Ymd_His').'.csv"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Expires: 0');
        }

        // List records
        if ($objRecords->numRows) {
            $result = $objRecords->fetchAllAssoc();

            // Process result and format values
            foreach ($result as $row) {
                ++$intRowCounter;
                $args = [];

                if (0 === $intRowCounter) {
                    if ('xls' === $strExportMode) {
                        if (!$blnCustomXlsExport) {
                            $xls->totalcol = \count($showFields);
                        }
                    }

                    $strExpSep = '';

                    $intColCounter = -1;
                    foreach ($showFields as $k => $v) {
                        if (\in_array($v, $ignoreFields, true)) {
                            continue;
                        }

                        ++$intColCounter;

                        if ($useFieldNames) {
                            $strName = $v;
                        } elseif (\strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0])) {
                            $strName = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0];
                        } elseif (\strlen($GLOBALS['TL_LANG']['tl_formdata'][$v][0])) {
                            $strName = $GLOBALS['TL_LANG']['tl_formdata'][$v][0];
                        } else {
                            $strName = strtoupper($v);
                        }

                        if (\strlen($strName)) {
                            $strName = \StringUtil::decodeEntities($strName);

                            if ($this->blnExportUTF8Decode || ('xls' === $strExportMode && !$blnCustomXlsExport)) {
                                $strName = $this->convertEncoding($strName, $GLOBALS['TL_CONFIG']['characterSet'], $this->strExportConvertToCharset);
                            }
                        }

                        if ('csv' === $strExportMode) {
                            $strName = str_replace('"', '""', $strName);
                            echo $strExpSep.$strExpEncl.str_replace('"', '""', $strName).$strExpEncl;

                            $strExpSep = ';';
                        } elseif ('xls' === $strExportMode) {
                            if (!$blnCustomXlsExport) {
                                $xls->setcell(['sheetname' => $strXlsSheet, 'row' => $intRowCounter, 'col' => $intColCounter, 'data' => $strName, 'fontweight' => XLSFONT_BOLD, 'vallign' => XLSXF_VALLIGN_TOP, 'fontfamily' => XLSFONT_FAMILY_NORMAL]);
                                $xls->setcolwidth($strXlsSheet, $intColCounter, 0x1aff);
                            } else {
                                $arrHookDataColumns[$v] = $strName;
                            }
                        }
                    }

                    ++$intRowCounter;

                    if ('csv' === $strExportMode) {
                        echo "\n";
                    }
                }

                $strExpSep = '';

                $intColCounter = -1;

                // Prepare field value
                foreach ($showFields as $k => $v) {
                    if (\in_array($v, $ignoreFields, true)) {
                        continue;
                    }

                    ++$intColCounter;

                    $strVal = '';
                    $strVal = $row[$v];

                    if ('date' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '');
                    } elseif ('time' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['timeFormat'], $row[$v]) : '');
                    } elseif ('datim' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '');
                    } elseif ('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                        if (1 === $useFormValues) {
                            // single value checkboxes don't have options
                            if ((\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && !empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']))) {
                                $strVal = \strlen($row[$v]) ? key($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) : '';
                            } else {
                                $strVal = $row[$v];
                            }
                        } else {
                            $strVal = \strlen($row[$v]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0] : '-';
                        }
                    } elseif ('radio' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'efgLookupRadio' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'select' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'efgLookupSelect' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        || 'efgLookupCheckbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']) {
                        $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'] : '|';

                        // take the assigned value instead of the user readable output
                        if (1 === $useFormValues) {
                            if ((false === strpos($row[$v], $strSep)) && (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && !empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']))) {
                                $options = array_flip($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']);
                                $strVal = $options[$row[$v]];
                            } else {
                                if ((\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && !empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']))) {
                                    $options = array_flip($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']);
                                    $tmparr = explode($strSep, $row[$v]);
                                    $fieldvalues = [];
                                    foreach ($tmparr as $valuedesc) {
                                        $fieldvalues[] = $options[$valuedesc];
                                    }
                                    $strVal = implode(",\n", $fieldvalues);
                                } else {
                                    $strVal = \strlen($row[$v]) ? str_replace($strSep, ",\n", $row[$v]) : '';
                                }
                            }
                        } else {
                            $strVal = \strlen($row[$v]) ? str_replace($strSep, ",\n", $row[$v]) : '';
                        }
                    } elseif ('fileTree' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']) {
                        $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'] : '|';

                        if (\is_string($row[$v]) && false !== strpos($row[$v], $strSep)) {
                            $strVal = implode(",\n", explode($strSep, $row[$v]));
                        } else {
                            $strVal = implode(",\n", deserialize($row[$v], true));
                        }
                    } else {
                        $row_v = deserialize($row[$v]);

                        if (\is_array($row_v)) {
                            $args_k = [];

                            foreach ($row_v as $option) {
                                $args_k[] = \strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$option]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$option] : $option;
                            }

                            $args[$k] = implode(",\n", $args_k);
                        } elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]])) {
                            $args[$k] = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]];
                        } else {
                            $args[$k] = $row[$v];
                        }
                        $strVal = null === $args[$k] ? $args[$k] : vsprintf('%s', $args[$k]);
                    }

                    if (\in_array($v, $this->arrBaseFields, true) || \in_array($v, $this->arrOwnerFields, true)) {
                        if ('fd_member' === $v) {
                            $strVal = $this->arrMembers[(int) ($row[$v])];
                        } elseif ('fd_user' === $v) {
                            $strVal = $this->arrUsers[(int) ($row[$v])];
                        } elseif ('fd_member_group' === $v) {
                            $strVal = $this->arrMemberGroups[(int) ($row[$v])];
                        } elseif ('fd_user_group' === $v) {
                            $strVal = $this->arrUserGroups[(int) ($row[$v])];
                        }
                    }

                    if (\strlen($strVal)) {
                        $strVal = \StringUtil::decodeEntities($strVal);
                        $strVal = preg_replace(['/<br.*\/*>/si'], ["\n"], $strVal);

                        if ($this->blnExportUTF8Decode || ('xls' === $strExportMode && !$blnCustomXlsExport)) {
                            $strVal = $this->convertEncoding($strVal, $GLOBALS['TL_CONFIG']['characterSet'], $this->strExportConvertToCharset);
                        }
                    }

                    if ('csv' === $strExportMode) {
                        $strVal = str_replace('"', '""', $strVal);
                        echo $strExpSep.$strExpEncl.$strVal.$strExpEncl;

                        $strExpSep = ';';
                    } elseif ('xls' === $strExportMode) {
                        if (!$blnCustomXlsExport) {
                            $xls->setcell(['sheetname' => $strXlsSheet, 'row' => $intRowCounter, 'col' => $intColCounter, 'data' => $strVal, 'vallign' => XLSXF_VALLIGN_TOP, 'fontfamily' => XLSFONT_FAMILY_NORMAL]);
                        } else {
                            $arrHookData[$intRowCounter][$v] = $strVal;
                        }
                    }
                }

                if ('csv' === $strExportMode) {
                    $strExpSep = '';
                    echo "\n";
                }
            }
        }

        if ('xls' === $strExportMode) {
            if (!$blnCustomXlsExport) {
                $xls->sendfile('export_'.$this->strFormKey.'_'.date('Ymd').'.xls');
                exit;
            }

            foreach ($GLOBALS['TL_HOOKS']['efgExportXls'] as $key => $callback) {
                $this->import($callback[0]);
                $res = $this->$callback[0]->$callback[1]($arrHookDataColumns, $arrHookData);
            }
        }
        exit;
    }

    /**
     * Edit a record.
     */
    protected function editSingleRecord(): void
    {
        //EfgLog::setEfgDebugmode('form');
        //EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'TL_MODE '.TL_MODE);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'PBD FormdataProcessor editSingleRecord');

        /**
         * Prepare URL.
         */
        $page_get = 'page_fd'.$this->id;
        $strUrl = preg_replace('/\?.*$/', '', urldecode(\Environment::get('request')));
        $strUrlParams = '';
        $blnQuery = false;

        foreach (preg_split('/&(amp;)?/', $_SERVER['QUERY_STRING']) as $fragment) {
            if (\strlen($fragment)) {
                if (0 !== strncasecmp($fragment, $this->strDetailKey, \strlen($this->strDetailKey)) && 0 !== strncasecmp($fragment, 'act', 3) && 0 !== strncasecmp($fragment, 'order_by', 8) && 0 !== strncasecmp($fragment, 'sort', 4) && 0 !== strncasecmp($fragment, $page_get, \strlen($page_get))) {
                    $strUrlParams .= (!$blnQuery ? '' : '&amp;').$fragment;
                    $blnQuery = true;
                }
            }
        }

        // Check record
        if (null === $this->intRecordId || (int) ($this->intRecordId) < 1) {
            unset($_GET[$this->strDetailKey], $_GET['act']);

            $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i'], ['', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
            \Controller::redirect($strRed);
        }

        // Check Owner and Alias
        $objOwner = \Database::getInstance()->prepare('SELECT fd_member,alias FROM tl_formdata WHERE id=?')
            ->execute($this->intRecordId)
        ;

        $varOwner = $objOwner->fetchAssoc();

        // Check access
        if (!empty($this->efg_list_access) && 'public' !== $this->efg_list_access) {
            if (!\in_array((int) ($varOwner['fd_member']), $this->arrAllowedOwnerIds, true)) {
                $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i', '/act=edit/i'], ['', '', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
                \Controller::redirect($strRed);
            }
        }

        // Check edit access
        $blnEditAllowed = false;
        if ('none' === $this->efg_fe_edit_access) {
            $blnEditAllowed = false;
        } elseif ('public' === $this->efg_fe_edit_access) {
            $blnEditAllowed = true;
        } elseif (!empty($this->efg_fe_edit_access)) {
            if (\in_array((int) ($varOwner['fd_member']), $this->arrAllowedEditOwnerIds, true)) {
                $blnEditAllowed = true;
            }
        }
        if (false === $blnEditAllowed) {
            $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i', '/act=edit/i'], ['', '', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
            \Controller::redirect($strRed);
        }

        $intListingId = 0;
        if ($this->id) {
            $intListingId = (int) ($this->id);
        }

        $strForm = '';
        $intFormId = 0;

        // Fallback template
        //if (isset($this->list_edit_layout)&&!\strlen($this->list_edit_layout)) {
        if (!isset($this->list_edit_layout)||!\strlen($this->list_edit_layout)) {
            $this->list_edit_layout = 'edit_fd_default';
        }
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'list_edit_layout editSingleRecord layouit: '.$this->list_edit_layout);

        // Get the form
        $objCheckRecord = \Database::getInstance()->prepare('SELECT form FROM tl_formdata WHERE id=?')
            ->limit(1)
            ->execute($this->intRecordId)
        ;
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'SELECT form FROM tl_formdata WHERE id=' . $this->intRecordId);

        if (1 === $objCheckRecord->numRows) {
            $strForm = $objCheckRecord->form;
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'found Form "'.$strForm.'"');
        }

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'ContentModel::findOneBy("form", ID) not null ID: ' . $objForm->id);
        // Get the ContentElement holding the form
        if (\strlen($strForm)) {
            $objForm = \FormModel::findOneBy('title', $strForm);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "FormModel::findOneBy('title', $strForm) class ".get_class($objForm));

            if (null !== $objForm) {
                $objFormElement = \ContentModel::findOneBy('form', $objForm->id);
             EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'objFormElement class '.get_class($objFormElement).' title '.$objFormElement->title);
            }
        }

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'list value table "' . $this->list_table.'"');

        if (null === $objFormElement) {
            $this->log('Could not find a ContentElement containing the form "'.$strForm.'"', __METHOD__, 'ERROR');
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'Could not find a ContentElement containing the form "'.$strForm.'"');

            $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i', '/act=edit/i'], ['', '', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
            //$this->log("PBD redirect to $strRed ", __METHOD__, 'ERROR');
            \Controller::redirect($strRed);
        }

        $this->Template = new \FrontendTemplate($this->list_edit_layout);

        $arrRecordFields = array_merge($this->arrBaseFields, $this->arrDetailFields);

        $strQuery = 'SELECT ';
        $strWhere = '';
        $strSep = '';

        foreach ($arrRecordFields as $field) {
            if (\in_array($field, $this->arrBaseFields, true)) {
                $strQuery .= $strSep.$field;
                $strSep = ', ';
            }
            if (!empty($this->arrDetailFields) && \in_array($field, $this->arrDetailFields, true)) {
                $strQuery .= $strSep.'(SELECT value FROM tl_formdata_details WHERE ff_name="'.$field.'" AND pid=f.id ) AS `'.$field.'`';
                $strSep = ', ';
            }
        }

        $strQuery .= ' FROM '.$this->list_table.' f';
        $strWhere .= (\strlen($strWhere) ? ' AND ' : ' WHERE ').'id=?';
        $strQuery .= $strWhere;

        $objRecord = \Database::getInstance()->prepare($strQuery)
            ->limit(1)
            ->execute($this->intRecordId)
        ;
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'search query '.$strQuery.' recordid '.$this->intRecordId.' numRows '.$objRecord->numRows);

        if ((int) $objRecord->numRows < 1) {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'PBD numRows kleiner 1');
            return;
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'PBD search objrecord da');
        $_SESSION['EFP']['LISTING_MOD']['id'] = $intListingId;

        if (null !== $objFormElement) {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'objFormElement != null class '.get_class($objFormElement));
            $this->Template->editform = $this->generateEditForm($objFormElement, $objRecord);
        }
    }

    protected function deleteSingleRecord(): void
    {
        $intDeleteId = 0;

        $this->import('FrontendUser', 'Member');

        /**
         * Prepare URL.
         */
        $page_get = 'page_fd'.$this->id;
        $strUrl = preg_replace('/\?.*$/', '', urldecode(\Environment::get('request')));
        $strUrlParams = '';
        $blnQuery = false;

        foreach (preg_split('/&(amp;)?/', $_SERVER['QUERY_STRING']) as $fragment) {
            if (\strlen($fragment)) {
                if (0 !== strncasecmp($fragment, 'act', 3) && 0 !== strncasecmp($fragment, 'order_by', 8) && 0 !== strncasecmp($fragment, 'sort', 4) && 0 !== strncasecmp($fragment, $page_get, \strlen($page_get))) {
                    $strUrlParams .= (!$blnQuery ? '' : '&amp;').$fragment;
                    $blnQuery = true;
                }
            }
        }

        // check record
        if (null === $this->intRecordId || (int) ($this->intRecordId) < 1) {
            unset($_GET[$this->strDetailKey], $_GET['act']);

            $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i', 'act=delete'], ['', '', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
            \Controller::redirect($strRed);
        }

        // Check Owner and Alias
        $objOwner = \Database::getInstance()->prepare('SELECT fd_member,alias FROM tl_formdata WHERE id=?')
            ->execute($this->intRecordId)
        ;

        $varOwner = $objOwner->fetchAssoc();

        // check list access
        if (!empty($this->efg_list_access) && 'public' !== $this->efg_list_access) {
            if (!\in_array((int) ($varOwner['fd_member']), $this->arrAllowedOwnerIds, true)) {
                $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i', '/act=delete/i'], ['', '', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
                \Controller::redirect($strRed);
            }
        }

        // check delete access
        $blnDeleteAllowed = false;
        if ('none' === $this->efg_fe_delete_access) {
            $blnDeleteAllowed = false;
        } elseif ('public' === $this->efg_fe_delete_access) {
            $blnDeleteAllowed = true;
            $intDeleteId = (int) ($this->intRecordId);
        } elseif (\strlen($this->efg_fe_delete_access)) {
            if ((int) ($varOwner['fd_member']) > 0 && \in_array((int) ($varOwner['fd_member']), $this->arrAllowedDeleteOwnerIds, true)) {
                $blnDeleteAllowed = true;
                $intDeleteId = (int) ($this->intRecordId);
            }
        }

        if (false === $blnDeleteAllowed) {
            $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i', '/act=delete/i'], ['', '', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
            \Controller::redirect($strRed);
        } else {
            \Database::getInstance()->prepare('DELETE FROM tl_formdata_details WHERE pid=?')
                ->execute([$intDeleteId])
            ;
            \Database::getInstance()->prepare('DELETE FROM tl_formdata WHERE id=?')
                ->execute([$intDeleteId])
            ;
        }

        $_SESSION['EFP']['LISTING_MOD']['id'] = $this->id;

        // redirect to list
        $strRed = preg_replace(['/\/'.$this->strDetailKey.'\/'.\Input::get($this->strDetailKey).'/i', '/'.$this->strDetailKey.'='.\Input::get($this->strDetailKey).'/i', '/act=delete/i'], ['', '', ''], $strUrl).(\strlen($strUrlParams) ? '?'.$strUrlParams : '');
        \Controller::redirect($strRed);
    }

    protected function prepareListWhere()
    {
        $strReturn = '';

        if (empty($this->list_where)) {
            return $strReturn;
        }

        $arrListWhere = [];
        $arrListConds = preg_split('/(\sAND\s|\sOR\s)/si', $this->list_where, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($arrListConds as $strListCond) {
            if (preg_match('/\sAND\s|\sOR\s/si', $strListCond)) {
                $arrListWhere[] = $strListCond;
            } else {
                $arrListCond = preg_split('/([\s!=><]+)/', $strListCond, -1, PREG_SPLIT_DELIM_CAPTURE);

                if (\in_array($arrListCond[0], $this->arrDetailFields, true)) {
                    $strCondField = $arrListCond[0];
                    unset($arrListCond[0]);
                    // handle numeric values
                    if (isset($GLOBALS['TL_DCA']['tl_formdata']['fields'][$strCondField]['eval']['rgxp']) && 'digit' === $GLOBALS['TL_DCA']['tl_formdata']['fields'][$strCondField]['eval']['rgxp']) {
                        $arrListWhere[] = '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$strCondField.'" AND pid=f.id)+0.0 '.implode('', $arrListCond);
                    } else {
                        $arrListWhere[] = '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$strCondField.'" AND pid=f.id) '.implode('', $arrListCond);
                    }
                } elseif (\in_array($arrListCond[0], $this->arrBaseFields, true)) {
                    $strCondField = $arrListCond[0];
                    unset($arrListCond[0]);
                    $arrListWhere[] = $strCondField.implode('', $arrListCond);
                } else {
                    $arrListWhere[] = implode('', $arrListCond);
                }
            }
        }

        if (!empty($arrListWhere)) {
            $strReturn = '('.implode('', $arrListWhere).')';
        }

        return $strReturn;
    }

    protected function replaceWhereTags($strBuffer)
    {
        $tags = [];
        preg_match_all('/{{[^{}]+}}/i', $strBuffer, $tags);

        // Replace tags
        foreach ($tags[0] as $tag) {
            $elements = explode('::', trim(str_replace(['{{', '}}'], ['', ''], $tag)));

            switch (strtolower($elements[0])) {
                case 'input':
                    $strKey = $elements[1];
                    $strNewVal = '';

                    $strVal = \Input::get($strKey);
                    if (!\strlen($strVal)) {
                        $strVal = \Input::post($strKey);
                    }
                    if (!\strlen($strVal)) {
                        $strVal = \Input::cookie($strKey);
                    }

                    if (\strlen($strVal)) {
                        $strNewVal = preg_replace(['/\[&\]/i'], ['&'], $strVal);
                    }

                    $strBuffer = str_replace($tag, $strNewVal, $strBuffer);
                    break;

                default:
                    break;
            }
        }

        return $strBuffer;
    }

    private function array_filter_like($arrInput, $varSearch)
    {
        $arrRet = [-1 => '-'];

        if (!\is_array($arrInput) || empty($arrInput)) {
            return $arrRet;
        }

        foreach ($arrInput as $k => $v) {
            if (!\is_bool(mb_stripos($v, $varSearch))) {
                $arrRet[$k] = $v;
            }
        }

        return $arrRet;
    }
}
