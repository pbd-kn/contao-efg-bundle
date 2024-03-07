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

namespace PBDKN\Efgco4\Resources\contao\drivers;

use PBDKN\Efgco4\Resources\contao\classes\EfgLog;
define('ENCODE_AMPERSANDS', true);    #PBD leycom
class DC_Formdata extends \Contao\DataContainer implements \listable, \editable
{
    /**
     * Name of the parent table.
     *
     * @param string
     */
    protected $ptable;

    /**
     * Names of the child tables.
     *
     * @param array
     */
    protected $ctable;

    /**
     * ID of the current record.
     *
     * @param int
     */
    protected $id;

    /**
     * IDs of all root records.
     *
     * @param array
     */
    protected $root;

    /**
     * ID of the button container.
     *
     * @param string
     */
    protected $bid;

    /**
     * Limit (database query).
     *
     * @param string
     */
    protected $limit;

    /**
     * First sorting field.
     *
     * @param string
     */
    protected $firstOrderBy;

    /**
     * Order by (database query).
     *
     * @param array
     */
    protected $orderBy = [];

    /**
     * Fields of a new or duplicated record.
     *
     * @param array
     */
    protected $set = [];

    /**
     * IDs of all records that are currently displayed.
     *
     * @param array
     */
    protected $current = [];

    /**
     * Show the current table as tree.
     *
     * @param bool
     */
    protected $treeView = false;

    /**
     * True if a new version has to be created.
     *
     * @param bool
     */
    protected $blnCreateNewVersion = false;

    /**
     * True if one of the form fields is uploadable.
     *
     * @param bool
     */
    protected $blnUploadable = false;

    /**
     * The current back end module.
     *
     * @param array
     */
    protected $arrModule = [];

    /**
     * Related form key, like 'fd_frm_contact'.
     *
     * @param string
     */
    protected $strFormKey;

    /**
     * Related form filter key, name of field in table tl_formdata holding form-identifier.
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
     * Detail fields names in table tl_formdata_details.
     *
     * @param mixed
     */
    protected $arrDetailFields;

    /**
     * Fields available for import field mapping.
     */
    protected $arrImportableFields;

    /**
     * Fields not available for import.
     */
    protected $arrImportIgnoreFields;

    /**
     * Fields to ignore on export.
     */
    protected $arrExportIgnoreFields;

    /**
     * Sql statements for detail fields.
     *
     * @param mixed
     */
    protected $arrSqlDetails;

    protected $arrMembers;

    protected $arrUsers;

    protected $arrMemberGroups;

    protected $arrUserGroups;

    // Decode UTF8 on CSV-/XLS-Export
    // This can be deactivated by configuration setting: $GLOBALS['efg_co4']['exportUTF8Decode'] = false
    protected $blnExportUTF8Decode = true;

    // Target charset when converting from UTF8 on CSV-/XLS-Export
    // This can be changed by configuration setting: $GLOBALS['efg_co4']['exportConvertToCharset'] = 'TARGET_CHARSET'
    protected $strExportConvertToCharset = 'CP1252';

    /**
     * Initialize the object.
     *
     * @param string
     * @param array
     */
    public function __construct($strTable, $arrModule = [])
    {
        parent::__construct();
        EfgLog::setEfgDebugmode('form');
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "do '".\Input::get('do')."' strTable '$strTable' id '".\Input::get('id')."'");
        // Check the request token (see #4007)
        if (isset($_GET['act'])) {
            if (!isset($_GET['rt']) || !\RequestToken::validate(\Input::get('rt'))) {
                $this->Session->set('INVALID_TOKEN_URL', \Environment::get('request'));
                \Controller::redirect('contao/confirm.php');
            }
        }

        $this->intId = \Input::get('id');

        // Clear the clipboard
        if (isset($_GET['clipboard'])) {
            $this->Session->set('CLIPBOARD', []);
            \Controller::redirect($this->getReferer());
        }

        $this->loadDataContainer('tl_form_field');
        $this->import('Formdata');

        // in Backend: Check BE User, Admin...
        if (TL_MODE === 'BE' || BE_USER_LOGGED_IN) {
            $this->import('BackendUser', 'User');
        }

        // in Frontend:
        if (TL_MODE === 'FE') {
            $this->import('FrontendUser', 'Member');
        }

        if ('export' === \Input::get('key')) {
            $this->strMode = 'export';
        } elseif ('exportxls' === \Input::get('key')) {
            $this->strMode = 'exportxls';
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

        // all field names of table tl_formdata
        foreach (\Database::getInstance()->listFields('tl_formdata') as $arrField) {
            if ('index' !== $arrField['type']) {
                $this->arrBaseFields[] = $arrField['name'];
            }
        }
        $this->arrBaseFields = array_unique($this->arrBaseFields);

        $this->arrOwnerFields = ['fd_member', 'fd_user', 'fd_member_group', 'fd_user_group'];

        $this->arrMembers = $this->Formdata->arrMembers;
        $this->arrMemberGroups = $this->Formdata->arrMemberGroups;
        $this->arrUsers = $this->Formdata->arrUsers;
        $this->arrUserGroups = $this->Formdata->arrUserGroups;

        // Check whether the table is defined
        if ('' === $strTable || !isset($GLOBALS['TL_DCA'][$strTable])) {
            $this->log('Could not load the data container configuration for "'.$strTable.'"', __METHOD__, TL_ERROR);
            trigger_error('Could not load the data container configuration for "'.$strTable.'"', E_USER_ERROR);
        }

        // Set IDs and redirect
        if ('tl_select' === \Input::post('FORM_SUBMIT')) {
            $ids = \Input::post('IDS');

            if (!\is_array($ids) || empty($ids)) {
                \Controller::reload();
            }

            $session = $this->Session->getData();
            $session['CURRENT']['IDS'] = \Input::post('IDS');
            $this->Session->setData($session);

            if (isset($_POST['edit'])) {
                \Controller::redirect(str_replace('act=select', 'act=editAll', \Environment::get('request')));
            } elseif (isset($_POST['delete'])) {
                \Controller::redirect(str_replace('act=select', 'act=deleteAll', \Environment::get('request')));
            } elseif (isset($_POST['override'])) {
                \Controller::redirect(str_replace('act=select', 'act=overrideAll', \Environment::get('request')));
            } elseif (isset($_POST['cut']) || isset($_POST['copy'])) {
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "strTable $strTable Post cut '".$_POST['cut']."'");
                $arrClipboard = $this->Session->get('CLIPBOARD');

                $arrClipboard[$strTable] = [
                    'id' => $ids,
                    'mode' => (isset($_POST['cut']) ? 'cutAll' : 'copyAll'),
                ];

                $this->Session->set('CLIPBOARD', $arrClipboard);
                \Controller::redirect($this->getReferer());
            }
        }

        $this->strTable = $strTable;
        if (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']))  $this->ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']; //PBD
        else $this->ptable=null;
        $this->ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];
        $this->treeView = false;
        $this->root = null;
        $this->arrModule = $arrModule;

        // Key of a form or '' for no specific form
        $this->strFormKey = '';
        $this->strFormFilterKey = '';
        $this->strFormFilterValue = '';

        if (\Input::get('do')) {
            if ('feedback' !== \Input::get('do')) {
                if (\array_key_exists(\Input::get('do'), $GLOBALS['BE_MOD']['formdata'])) {
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "strTable $strTable NE feedback do ".\Input::get('do'));

                    $this->strFormKey = \Input::get('do');
                    $this->strFormFilterKey = 'form';
                    $this->strFormFilterValue = $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['title'];
                    $this->sqlFormFilter = ' AND '.$this->strFormFilterKey.'=\''.$this->strFormFilterValue.'\' ';

                    // add sql where condition 'form'=TITLE_OF_FORM
                    if ('tl_formdata' === $this->strTable) {
                        $this->procedure[] = $this->strFormFilterKey.'=?';
                        $this->values[] = $this->strFormFilterValue;
                    }
                }
            }
        }

        // Call onload_callback (e.g. to check permissions)

        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this);      // PBD Änderung
                } elseif (\is_callable($callback)) {
                    $callback($this);
                }
            }
        }

        // check names of detail fields
        // .. after call to onload_callback we have the form specific dca in $GLOBALS['TL_DCA'][$this->strTable]
        if (isset($this->strFormKey) && \strlen($this->strFormKey)) {
            $arrFFNames = array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']);
        }

        // get all FormField names of forms storing formdata
        else {
            $objFFNames = \Database::getInstance()->prepare("SELECT DISTINCT ff.name FROM tl_form_field ff, tl_form f WHERE (ff.pid=f.id) AND ff.name != '' AND f.storeFormdata=?")
                ->execute('1')
            ;
            if ($objFFNames->numRows) {
                $arrFFNames = $objFFNames->fetchEach('name');
            }
        }

        if (!empty($arrFFNames)) {
            $this->arrDetailFields = array_diff($arrFFNames, $this->arrBaseFields, ['import_source']);
        }

        // store array of sql-stmts for detail fields
        if (!empty($this->arrDetailFields)) {
            $this->arrSqlDetails = [];
            foreach ($this->arrDetailFields as $strFName) {
                $this->arrSqlDetails[$strFName] = '(SELECT value FROM tl_formdata_details WHERE ff_name=\''.$strFName.'\' AND pid=f.id) AS `'.$strFName.'`';
            }
        }

        // Store the current referer
        if (!empty($this->ctable) && !\Input::get('act') && !\Input::get('key') && !\Input::get('token') && 'contao/main.php' === \Environment::get('script') && !\Environment::get('isAjaxRequest')) {
            $session = $this->Session->get('referer');
            $session[TL_REFERER_ID][$this->strTable] = substr(\Environment::get('requestUri'), \strlen(TL_PATH) + 1);
            $this->Session->set('referer', $session);
        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "end do '".\Input::get('do')."' strTable '$strTable' id '".\Input::get('id')."'");
    }

    /**
     * Return an object property.
     *
     * @param string
     */
    public function __get($strKey)
    {
        switch ($strKey) {
            case 'id':
                return $this->intId;
                break;

            case 'parentTable':
                return $this->ptable;
                break;

            case 'childTable':
                return $this->ctable;
                break;

            case 'rootIds':
                return $this->root;
                break;

            case 'createNewVersion':
                return $this->blnCreateNewVersion;
                break;

            case 'strFormFilterValue':
                return $this->strFormFilterValue;
                break;

            case 'arrFieldConfig':
                return $this->arrFieldConfig;
                break;

            case 'arrBaseFields':
            case 'baseFields':
                return $this->arrBaseFields;
                break;

            case 'arrDetailFields':
            case 'detailFields':
                return $this->arrDetailFields;
                break;
        }

        return parent::__get($strKey);
    }

    /**
     * List all records of a particular table.
     *
     * @return string
     */
    public function showAll()
    {
        $return = '';
        $this->limit = '';
        $this->bid = 'tl_buttons';

        $this->reviseTable();

        // Add to clipboard
        if ('paste' === \Input::get('act')) {
            $arrClipboard = $this->Session->get('CLIPBOARD');

            $arrClipboard[$this->strTable] = [
                'id' => \Input::get('id'),
                'childs' => \Input::get('childs'),
                'mode' => \Input::get('mode'),
            ];

            $this->Session->set('CLIPBOARD', $arrClipboard);
        }

        // Custom filter
        if (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']) && \is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $filter) {
                $this->procedure[] = $filter[0];
                $this->values[] = $filter[1];
            }
        }

        if (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) {
            $this->procedure[] = 'pid=?';
            $this->values[] = CURRENT_ID;
        }

        // Render view
        $return .= $this->panel();
        $return .= $this->listView();

        // Add another panel at the end of the page
        if (false !== strpos($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'], 'limit') && ($strLimit = $this->limitMenu(true)) !== false) {
            $return .= '

<form action="'.ampersand(\Environment::get('request'), true).'" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_filters_limit">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_panel_bottom">

<div class="tl_submit_panel tl_subpanel anton">
<input type="image" name="btfilter" id="btfilter" src="'.TL_FILES_URL.'system/themes/'.\Backend::getTheme().'/images/reload.gif" class="tl_img_submit" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['applyTitle']).'" alt="'.specialchars($GLOBALS['TL_LANG']['MSC']['apply']).'">
</div>'.$strLimit.'

<div class="clear"></div>

</div>

</div>
</form>
';
        }

        // Store the current IDs
        $session = $this->Session->getData();
        $session['CURRENT']['IDS'] = $this->current;
        $this->Session->setData($session);

        return $return;
    }

    /**
     * Return all non-excluded fields of a record as HTML table.
     *
     * @return string
     */
    public function show()
    {
        if (!isset($this->intId) || !\strlen($this->intId)) {
            return '';
        }

        $table_alias = ('tl_formdata' === $this->strTable ? ' f' : '');

        $sqlQuery = 'SELECT * '.(!empty($this->arrSqlDetails) ? ', '.implode(',', array_values($this->arrSqlDetails)) : '').' FROM '.$this->strTable.$table_alias;
        $sqlWhere = ' WHERE id=?';
        if ('' !== $sqlWhere) {
            $sqlQuery .= $sqlWhere;
        }

        $objRow = \Database::getInstance()->prepare($sqlQuery)
            ->limit(1)
            ->execute($this->intId)
        ;

        if ($objRow->numRows < 1) {
            return '';
        }

        $count = 1;
        $return = '';
        $row = $objRow->row();

        // Get all fields
        $fields = array_keys($row);
        $allowedFields = ['id', 'pid', 'sorting', 'tstamp'];

        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'])) {
            $allowedFields = array_unique(array_merge($allowedFields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields'])));
        }

        // Use the field order of the DCA file
        $fields = array_intersect($allowedFields, $fields);

        // Show all allowed fields
        foreach ($fields as $i) {
            if (!\in_array($i, $allowedFields, true)
                || 'password' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType']
                || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['doNotShow']
                || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['hideInput']) {
                continue;
            }

            // Special treatment for table tl_undo
            if ('tl_undo' === $this->strTable && 'data' === $i) {
                continue;
            }

            $value = deserialize($row[$i]);
            $class = (($count++ % 2) === 0) ? ' class="tl_bg"' : '';

            // Ignore empty detail fields if this is overall 'feedback'
            if (empty($this->strFormKey) && \in_array($i, $this->arrDetailFields, true) && empty($value)) {
                continue;
            }

            // Get the field value
            if (\is_array($value)) {
                foreach ($value as $kk => $vv) {
                    if (\is_array($vv)) {
                        $vals = array_values($vv);
                        $value[$kk] = $vals[0].' ('.$vals[1].')';
                    }
                }

                $row[$i] = implode(', ', $value);
            } elseif ('date' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['rgxp']) {
                $row[$i] = $value ? \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $value) : '-';
            } elseif ('time' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['rgxp']) {
                $row[$i] = $value ? \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $value) : '-';
            } elseif ('datim' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['rgxp']
                || \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['flag'], [5, 6, 7, 8, 9, 10], true)
                || 'tstamp' === $i) {
                $row[$i] = $value ? \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $value) : '-';
            } elseif ('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType']
                && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['multiple']) {
                if (\in_array($i, $this->arrDetailFields, true)) {
                    $row[$i] = \strlen($value) ? $value : '-';
                } else {
                    $row[$i] = \strlen($value) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label'][0] : '-';
                }
            } elseif ('textarea' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['inputType']
                && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['allowHtml']
                    || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['preserveTags'])) {
                $row[$i] = specialchars($value);
            } elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'])) {
                $row[$i] = isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]]) ? ((\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['reference'][$row[$i]]) : $row[$i];
            }

            if (\in_array($i, $this->arrBaseFields, true) || \in_array($i, $this->arrOwnerFields, true)) {
                if ('fd_member' === $i) {
                    $row[$i] = $this->arrMembers[(int) $value];
                } elseif ('fd_user' === $i) {
                    $row[$i] = $this->arrUsers[(int) $value];
                } elseif ('fd_member_group' === $i) {
                    $row[$i] = $this->arrMemberGroups[(int) $value];
                } elseif ('fd_user_group' === $i) {
                    $row[$i] = $this->arrUserGroups[(int) $value];
                }
            }

            // Replace foreign keys with their values
            // .. but not if foreignKey table is formdata table
            if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['foreignKey']) && \strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['foreignKey'])) {
                $chunks = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['foreignKey']);

                if ('fd_' === substr($chunks[0], 0, 3)) {
                    $row[$i] = $value;
                } else {
                    $objKey = \Database::getInstance()->prepare('SELECT '.$chunks[1].' FROM '.$chunks[0].' WHERE id=?')
                        ->limit(1)
                        ->execute($row[$i])
                    ;
                    if ($objKey->numRows) {
                        $row[$i] = $objKey->$chunks[1];
                    }
                }
            }

            // Handle multiple value fields (CSV or pipe separated)
            if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['multiple']) {
                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['eval']['csv'] : '|';
                $row[$i] = str_replace($strSep, ', ', $row[$i]);
            }

            // Check multiline value
            if (!\is_bool(strpos($row[$i], "\n"))) {
                $strVal = $row[$i];
                $strVal = preg_replace('/(<\/|<)(h\d|p|div|section|ul|ol|li|table|tbody|tr|td|th)([^>]*)(>)(\n)/si', '\\1\\2\\3\\4', $strVal);
                $strVal = nl2br($strVal, false);
                $strVal = preg_replace('/(<\/)(h\d|p|div|section|ul|ol|li|table|tbody|tr|td|th)([^>]*)(>)(\n)/si', "\\1\\2\\3\\4\n", $strVal);
                $row[$i] = $strVal;
                unset($strVal);
            }

            // Label
            if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label'])) {
                $label = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$i]['label'];
            } else {
                $label = \is_array($GLOBALS['TL_LANG']['MSC'][$i]) ? $GLOBALS['TL_LANG']['MSC'][$i][0] : $GLOBALS['TL_LANG']['MSC'][$i];
            }

            if (isset($label) && !\strlen($label)) {
                $label = $i;
            }

            $return .= '
  <tr>
    <td'.$class.'><span class="tl_label">'.$label.': </span></td>
    <td'.$class.'>'.$row[$i].'</td>
  </tr>';
        }

        // Return table
        return '
<div id="tl_buttons">'.(!\Input::get('popup') ? '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' : '').'
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], ($this->intId ? 'ID '.$this->intId : '')).'</h2>

<table class="tl_show">'.$return.'
</table>';
    }

    /**
     * Insert a new row into a database table.
     *
     * @param array
     */
    public function create($set = []): void
    {
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'strTable '.$this->strTable.'strFormKey '.$this->strFormKey);

        if (!empty($this->strFormKey)) {
            $set['form'] = $this->Formdata->arrStoringForms[str_replace('fd_', '', $this->strFormKey)]['title'];
            $set['date'] = time();
            $set['ip'] = \Environment::get('ip');

            if ($this->User && (int) ($this->User->id) > 0) {
                $set['fd_user'] = (int) ($this->User->id);
            }
        }

        // Get all default values for the new entry
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k => $v) {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "create field[$k] ".$this->strFormKey);
            if (\array_key_exists('default', $v)) {
                if (!\in_array($k, $this->arrBaseFields, true)) {
                    continue;
                }
                $this->set[$k] = \is_array($v['default']) ? serialize($v['default']) : $v['default'];
            }
        }

        // Set passed values
        if (!empty($set) && \is_array($set)) {
            $this->set = array_merge($this->set, $set);
        }

        // Empty the clipboard
        $arrClipboard = $this->Session->get('CLIPBOARD');
        $arrClipboard[$this->strTable] = [];
        $this->Session->set('CLIPBOARD', $arrClipboard);

        // Insert the record if the table is not closed and switch to edit mode
        if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed']) {
            $this->set['tstamp'] = 0;
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'create insert into  '.$this->strTable);
            foreach ($this->set as $k => $v) {
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "create insert into tl_formdata this->set[$k]$v ");
            }

            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'create query INSERT INTO '.$this->strTable.' %s');
            $objInsertStmt = \Database::getInstance()->prepare('INSERT INTO '.$this->strTable.' %s')
                ->set($this->set)
                ->execute()
            ;
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'create changed rows in '.$this->strTable.' affectedRows '.$objInsertStmt->affectedRows);

            if ($objInsertStmt->affectedRows) {
                $s2e = $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'] ? '&s2e=1' : '';  // switch to edit
                $insertID = $objInsertStmt->insertId;
                EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'create in '.$this->strTable." insertID $insertID ".$objInsertStmt->insertId);

                foreach ($this->arrDetailFields as $strDetailField) {
                    $strVal = '';
                    $arrDetailSet = [
                        'pid' => $insertID,
                        'tstamp' => time(),
                        'ff_id' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['ff_id'],
                        'ff_name' => $strDetailField,
                    ];

                    // default value
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default'])) {
                        // default value in case of field type checkbox, select, radio
                        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default'])) {
                            $strVal = implode(',', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default']);
                        }
                        // default value in case of other fields
                        else {
                            $strVal = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strDetailField]['default'];
                        }
                    }

                    $arrDetailSet['value'] = $strVal;

                    $objInsertStmt = \Database::getInstance()->prepare('INSERT INTO tl_formdata_details %s')
                        ->set($arrDetailSet)
                        ->execute()
                    ;
                    $insertIDdetail = $objInsertStmt->insertId;
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'tl_formdata_details insertIDdetail '.$objInsertStmt->insertId);
                }

                // Save new record in the session
                /** @var AttributeBagInterface $objSessionBag */
                $objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

                $new_records = $objSessionBag->get('new_records');
                $new_records[$this->strTable][] = $insertID;
                $objSessionBag->set('new_records', $new_records);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "in session insertID $insertID ".$this->strTable.' len '.\count($new_records[$this->strTable]));
                $this->Session->set('new_records', $new_records);

                // Call the oncreate_callback
                if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncreate_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncreate_callback'] as $callback) {
                        if (\is_array($callback)) {
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'call callback'.$callback[0]);
                            $this->import($callback[0]);
                            $this->{$callback[0]}->{$callback[1]}($this->strTable, $insertID, $this->set, $this);    //Änderung PBD
                        } elseif (\is_callable($callback)) {
                            $callback($this->strTable, $insertID, $this->set, $this);
                        }
                    }
                }

                // Add a log entry
                $this->log('A new entry in table "'.$this->strTable.'" has been created (ID: '.$insertID.')', __METHOD__, TL_GENERAL);
                \Controller::redirect($this->switchToEdit($insertID).$s2e);
            } else {
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'keine Aenderungen rows '.$callback[0]);
            }
        }

        \Controller::redirect($this->getReferer());
    }

    /**
     * Do nothing here.
     */
    public function cut(): void
    {
        // Call the oncut_callback
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'cut '.$this->strTable);

        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'])) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'cut callback exist');
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this);
                } elseif (\is_callable($callback)) {
                    $callback($this);
                }
            }
        }
    }

    /**
     * Do nothing here.
     */
    public function copy(): void
    {
    }

    /**
     * Delete a record of the current table table and save it to tl_undo.
     *
     * @param bool
     */
    public function delete($blnDoNotRedirect = false): void
    {
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "do '".\Input::get('do')."'");

        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable']) {
            $this->log('Table "'.$this->strTable.'" is not deletable', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        if (!$this->intId) {
            \Controller::redirect($this->getReferer());
        }

        $data = [];
        $delete = [];

        // Do not save records from tl_undo itself
        if ('tl_undo' === $this->strTable) {
            \Database::getInstance()->prepare('DELETE FROM '.$this->strTable.' WHERE id=?')
                ->limit(1)
                ->execute($this->intId)
        ;

            \Controller::redirect($this->getReferer());
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "los gehts mit löschen '".\Input::get('do')."'");

        // If there is a PID field but no parent table
        if (\Database::getInstance()->fieldExists('pid', $this->strTable) && (!isset($this->ptable) || !\strlen($this->ptable))) {
            $delete[$this->strTable] = \Database::getInstance()->getChildRecords($this->intId, $this->strTable);
            array_unshift($delete[$this->strTable], $this->intId);
        } else {
            $delete[$this->strTable] = [$this->intId];
        }

        // Delete all child records if there is a child table
        if (!empty($this->ctable)) {
            foreach ($delete[$this->strTable] as $id) {
                $this->deleteChilds($this->strTable, $id, $delete);
            }
        }

        $affected = 0;

        // Save each record of each table
        foreach ($delete as $table => $fields) {
            foreach ($fields as $k => $v) {
                $objSave = \Database::getInstance()->prepare('SELECT * FROM '.$table.' WHERE id=?')
                    ->limit(1)
                    ->execute($v)
                ;

                if ($objSave->numRows) {
                    $data[$table][$k] = $objSave->fetchAssoc();

                    // Store the active record
                    if ($table === $this->strTable && $v === $this->intId) {
                        $this->objActiveRecord = $objSave;
                    }
                }

                ++$affected;
            }
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "vor import Backend '".\Input::get('do')."'");

        $this->import('BackendUser', 'User');

        $objUndoStmt = \Database::getInstance()->prepare('INSERT INTO tl_undo (pid, tstamp, fromTable, query, affectedRows, data) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute($this->User->id, time(), $this->strTable, 'DELETE FROM '.$this->strTable.' WHERE id='.$this->intId, $affected, serialize($data))
        ;
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "nach objUndoStmt '".\Input::get('do')."'");
        // Delete the records
        if ($objUndoStmt->affectedRows) {
            // Call ondelete_callback
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['ondelete_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['ondelete_callback'] as $callback) {
                    //$this->log("PBD DC_Formdata call ondelete_callback " . $this->strTable, __METHOD__, TL_GENERAL);
                    if (\is_array($callback)) {       // hier wird u.U haste model delete aufgerufe
                        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'ondelete_callback is array import '.$callback[0]);
                        $this->import($callback[0]);
                        //$this->log("PBD DC_Formdata call ondelete_callback is array call import " . $callback[1], __METHOD__, TL_GENERAL);
                        $this->{$callback[0]}->{$callback[1]}($this, $objUndoStmt->insertId);    //Änderung PBD
                    } elseif (\is_callable($callback)) {
                        $callback($this, $objUndoStmt->insertId);
                    }
                    //$this->log("PBD DC_Formdata call ondelete_callback ok ", __METHOD__, TL_GENERAL);
                }
            }
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "vor delete entry '".\Input::get('do')."'");

            // Delete the records
            foreach ($delete as $table => $fields) {
                foreach ($fields as $k => $v) {
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "DELETE FROM '.$table.' WHERE id=$v");
                    \Database::getInstance()->prepare('DELETE FROM '.$table.' WHERE id=?')
                        ->limit(1)
                        ->execute($v)
                        ;
                }
            }

            // Add a log entry unless we are deleting from tl_log itself
            if ('tl_log' !== $this->strTable) {
                $this->log('DELETE FROM '.$this->strTable.' WHERE id='.$data[$this->strTable][0]['id'], __METHOD__, TL_GENERAL);
            }
        }

        if (!$blnDoNotRedirect) {
            \Controller::redirect($this->getReferer());
        }
    }

    /**
     * Delete all selected records.
     */
    public function deleteAll(): void
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable']) {
            $this->log('Table "'.$this->strTable.'" is not deletable', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $session = $this->Session->getData();
        $ids = $session['CURRENT']['IDS'];

        if (\is_array($ids) && \strlen($ids[0])) {
            foreach ($ids as $id) {
                $this->intId = $id;
                $this->delete(true);
            }
        }

        \Controller::redirect($this->getReferer());
    }

    /**
     * Recursively get all related table names and records.
     *
     * @param string
     * @param int
     * @param array
     */
    public function deleteChilds($table, $id, &$delete): void
    {
        $cctable = [];
        $ctable = $GLOBALS['TL_DCA'][$table]['config']['ctable'];

        if (!\is_array($ctable)) {
            return;
        }

        // Walk through each child table
        foreach ($ctable as $v) {
            $this->loadDataContainer($v);
            $cctable[$v] = $GLOBALS['TL_DCA'][$v]['config']['ctable'];

            $objDelete = \Database::getInstance()->prepare('SELECT id FROM '.$v.' WHERE pid=?')
                ->execute($id)
            ;

            if (!$GLOBALS['TL_DCA'][$v]['config']['doNotDeleteRecords'] && \strlen($v) && $objDelete->numRows) {
                foreach ($objDelete->fetchAllAssoc() as $row) {
                    $delete[$v][] = $row['id'];

                    if (!empty($cctable[$v])) {
                        $this->deleteChilds($v, $row['id'], $delete);
                    }
                }
            }
        }
    }

    /**
     * Restore one or more deleted records.
     */
    public function undo(): void
    {
        $objRecords = \Database::getInstance()->prepare('SELECT * FROM '.$this->strTable.' WHERE id=?')
            ->limit(1)
            ->execute($this->intId)
        ;

        // Check whether there is a record
        if ($objRecords->numRows < 1) {
            \Controller::redirect($this->getReferer());
        }

        $error = false;
        $query = $objRecords->query;
        $data = deserialize($objRecords->data);

        if (!\is_array($data)) {
            \Controller::redirect($this->getReferer());
        }

        // Restore the data
        foreach ($data as $table => $fields) {
            foreach ($fields as $row) {
                $restore = [];

                foreach ($row as $k => $v) {
                    $restore[$k] = $v;
                }

                $objInsertStmt = \Database::getInstance()->prepare('INSERT INTO '.$table.' %s')
                    ->set($restore)
                    ->execute()
                ;

                // Do not delete record from tl_undo if there is an error
                if ($objInsertStmt->affectedRows < 1) {
                    $error = true;
                }
            }
        }

        // Add log entry and delete record from tl_undo if there was no error
        if (!$error) {
            $this->log('Undone '.$query, __METHOD__, TL_GENERAL);

            \Database::getInstance()->prepare('DELETE FROM '.$this->strTable.' WHERE id=?')
                ->limit(1)
                ->execute($this->intId)
            ;
        }

        \Controller::redirect($this->getReferer());
    }

    /**
     * Do nothing here.
     */
    public function move(): void
    {
    }

    /**
     * Autogenerate a form to edit the current database record.
     *
     * @param int
     * @param int
     * @param mixed|null $intID
     * @param mixed|null $ajaxId
     *
     * @return string
     */
    public function edit($intID = null, $ajaxId = null)
    {
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "intID '$intID' this->intId '".$this->intId."'");

        $table_alias = ('tl_formdata' === $this->strTable ? ' f' : '');

        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
            $this->log('Table "'.$this->strTable.'" is not editable', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        if (isset($intID) && ('' !== $intID)) {
            $this->intId = $intID;
        }

        $return = '';
        $this->values[] = $this->intId;
        $this->procedure[] = 'id=?';
        $this->blnCreateNewVersion = false;

        // Get current record
        $sqlQuery = 'SELECT * '.(!empty($this->arrSqlDetails) ? ', '.implode(',', array_values($this->arrSqlDetails)) : '').' FROM '.$this->strTable.$table_alias;
        $sqlWhere = ' WHERE id=?';
        if ('' !== $sqlWhere) {
            $sqlQuery .= $sqlWhere;
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "sqlQuery $sqlQuery this->intId ".$this->intId);

        $objRow = \Database::getInstance()->prepare($sqlQuery)
            ->limit(1)
            ->execute($this->intId)
        ;

        // Redirect if there is no record with the given ID
        if ($objRow->numRows < 1) {
            $this->log('a Could not load record "'.$this->strTable.'.id='.$this->intId.'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $this->objActiveRecord = $objRow;
        $this->checkForTinyMce();

        // Build an array from boxes and rows
        $this->strPalette = $this->getPalette();
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'this->strPalette '.$this->strPalette);
        $boxes = trimsplit(';', $this->strPalette);
        $legends = [];

        if (!empty($boxes)) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'boxes exists');
            foreach ($boxes as $k => $v) {
                $eCount = 1;
                $boxes[$k] = trimsplit(',', $v);

                foreach ($boxes[$k] as $kk => $vv) {
                    if (preg_match('/^\[.*\]$/', $vv)) {
                        ++$eCount;
                        continue;
                    }

                    if (preg_match('/^\{.*\}$/', $vv)) {
                        $legends[$k] = substr($vv, 1, -1);
                        unset($boxes[$k][$kk]);
                    } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] || !\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv])) {
                        unset($boxes[$k][$kk]);
                    }
                }

                // Unset a box if it does not contain any fields
                if (\count($boxes[$k]) < $eCount) {
                    unset($boxes[$k]);
                }
            }

            $class = 'tl_tbox';
            $fs = $this->Session->get('fieldset_states');
            $blnIsFirst = true;

            // Render boxes
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Render boxes len '.count($boxes));
            foreach ($boxes as $k => $v) {
                $strAjax = '';
                $blnAjax = false;
                $key = '';
                $cls = '';
                $legend = '';

                if (isset($legends[$k])) {
                    [$key, $cls] = explode(':', $legends[$k]);
                    $legend = "\n".'<legend onclick="AjaxRequest.toggleFieldset(this,\''.$key.'\',\''.$this->strTable.'\')">'.($GLOBALS['TL_LANG'][$this->strTable][$key] ?? $key).'</legend>';
                }

                if (isset($fs[$this->strTable][$key])) {
                    $class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
                } else {
                    $class .= (($cls && $legend) ? ' '.$cls : '');
                }

                $return .= "\n\n".'<fieldset'.($key ? ' id="pal_'.$key.'"' : '').' class="'.$class.($legend ? '' : ' nolegend').'">'.$legend;

                // Build rows of the current box
                foreach ($v as $kk => $vv) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Build rows of the current box kk '.$kk.' vv '.$vv);
                    if ('[EOF]' === $vv) {
                        if ($blnAjax && \Environment::get('isAjaxRequest')) {
                            return $strAjax.'<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'">';
                        }

                        $blnAjax = false;
                        $return .= "\n".'</div>';

                        continue;
                    }

                    if (preg_match('/^\[.*\]$/', $vv)) {
                        $thisId = 'sub_'.substr($vv, 1, -1);
                        $blnAjax = ($ajaxId === $thisId && \Environment::get('isAjaxRequest')) ? true : false;
                        $return .= "\n".'<div id="'.$thisId.'">';

                        continue;
                    }

                    $this->strField = $vv;
                    $this->strInputName = $vv;
                    $this->varValue = $objRow->$vv;
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "this->strField '".$this->strField."' this->strInputName '".$this->strInputName."' this->varValue '".$this->varValue."'");

                    // Autofocus the first text field
                    if ($blnIsFirst && 'text' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType']) {
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['autofocus'] = 'autofocus';
                        $blnIsFirst = false;
                    }

                    // Call options_callback
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'])) {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'][1];

                        $this->import($strClass);
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $this->$strClass->$strMethod($this);
                    }

                    // Convert CSV fields
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple']
                        && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) {
                        $this->varValue = trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'], $this->varValue);
                    }

                    // Call load_callback
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback) {
                            if (\is_array($callback)) {
                                $this->import($callback[0]);
                                $this->varValue = $this->{$callback[0]}->$callback[1]($this->varValue, $this);     //Änderung PBD
                            } elseif (\is_callable($callback)) {
                                $this->varValue = $callback($this->varValue, $this);
                            }
                        }

                        $this->objActiveRecord->{$this->strField} = $this->varValue;
                    }

                    // prepare values of special fields like radio, select and checkbox
                    $strInputType = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'];
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strInputType '.$strInputType);

                    // field types radio, select, multi checkbox
                    if (\in_array($strInputType, ['radio', 'select', 'conditionalselect', 'countryselect'], true)
                        || ('checkbox' === $strInputType && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'])) {
                        if (\in_array($this->strField, $this->arrBaseFields, true) && \in_array($this->strField, $this->arrOwnerFields, true)) {
                            if ('fd_user' === $this->strField) {
                                if ($this->User && $this->User->id) {
                                    $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] = $this->User->id;
                                }
                            }
                        } else {
                            // foreignKey fields
                            if ('select' === $strInputType && (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']) && \strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']))) {
                                // include blank Option
                                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['includeBlankOption'] = true;

                                $arrKey = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
                                $strForeignTable = $arrKey[0];
                                $strForeignField = $arrKey[1];

                                // WHERE condition for foreignKey
                                $strForeignKeyCond = '';
                                if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere']) && \strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere'])) {
                                    $strForeignKeyCond = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere'];
                                }

                                // check if foreignKey table is a formdata table
                                if ('fd_' === substr($strForeignTable, 0, 3)) {
                                    $strFormKey = substr($strForeignTable, 3);
                                    $strForeignDcaKey = $strForeignTable;
                                    $strForeignTable = 'tl_formdata';

                                    // backup current dca and load dca for foreign formdata
                                    $BAK_DCA = $GLOBALS['TL_DCA'][$this->strTable];
                                    $this->loadDataContainer($strForeignDcaKey);

                                    $strForeignField = $arrKey[1];
                                    $strForeignSqlField = '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$strForeignField.'" AND pid=f.id ) AS `'.$strForeignField.'`';

                                    $sqlForeignFd = 'SELECT f.id,'.$strForeignSqlField.' FROM tl_formdata f, tl_formdata_details fd ';
                                    $sqlForeignFd .= 'WHERE (f.id=fd.pid) AND f.'.$GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterKey']."='".$GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterValue']."' AND fd.ff_name='".$strForeignField."'";

                                    if (!empty($strForeignKeyCond)) {
                                        $arrForeignKeyCond = preg_split('/([\s!=><]+)/', $strForeignKeyCond, -1, PREG_SPLIT_DELIM_CAPTURE);
                                        $strForeignCondField = $arrForeignKeyCond[0];
                                        unset($arrForeignKeyCond[0]);
                                        if (\in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['baseFields'], true)) {
                                            $sqlForeignFd .= ' AND f.'.$strForeignCondField.implode('', $arrForeignKeyCond);
                                        }
                                        if (\in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['detailFields'], true)) {
                                            $sqlForeignFd .= ' AND (SELECT value FROM tl_formdata_details WHERE ff_name="'.$strForeignCondField.'" AND pid=f.id ) '.implode('', $arrForeignKeyCond);
                                        }
                                    }

                                    $objForeignFd = \Database::getInstance()->prepare($sqlForeignFd)->execute();

                                    // reset current dca
                                    $GLOBALS['TL_DCA'][$this->strTable] = $BAK_DCA;
                                    unset($BAK_DCA);

                                    if ($objForeignFd->numRows) {
                                        $arrForeignRecords = $objForeignFd->fetchAllAssoc();
                                        if (!empty($arrForeignRecords)) {
                                            foreach ($arrForeignRecords as $arrForeignRecord) {
                                                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField].' [~'.$arrForeignRecord['id'].'~]';
                                            }
                                        }
                                        unset($arrForeignRecords);
                                    }

                                    // unset dca 'foreignKey': prevents \Widget::getAttributesFromDca to read options from table instead handle as normal select
                                    unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey'], $objForeignFd);
                                }
                                // foreignKey table is 'normal' table
                                elseif (\Database::getInstance()->fieldExists($strForeignField, $strForeignTable)) {
                                    $blnAlias = \Database::getInstance()->fieldExists('alias', $strForeignTable);

                                    $sqlForeign = 'SELECT id,'.($blnAlias ? 'alias,' : '').$strForeignField.' FROM '.$strForeignTable.(\strlen($strForeignKeyCond) ? ' WHERE '.$strForeignKeyCond : '').' ORDER BY '.$strForeignField;

                                    $objForeign = \Database::getInstance()->prepare($sqlForeign)->execute();

                                    if ($objForeign->numRows) {
                                        $arrForeignRecords = $objForeign->fetchAllAssoc();
                                        if (!empty($arrForeignRecords)) {
                                            foreach ($arrForeignRecords as $arrForeignRecord) {
                                                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField].' [~'.(($blnAlias && \strlen($arrForeignRecord['alias'])) ? $arrForeignRecord['alias'] : $arrForeignRecord['id']).'~]';
                                            }
                                        }
                                        unset($arrForeignRecords);
                                    }

                                    // unset dca 'foreignKey': prevents \Widget::getAttributesFromDca to read options from table instead handle as normal select
                                    unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey'], $objForeign);
                                }
                                // sort options on label
                                asort($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
                            }

                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                $arrValues = explode($strSep, (string)$this->varValue);    # PBD leycom
                            } else {
                                $arrValues = $this->varValue;
                            }

                            if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['efgStoreValues']) {
                                $this->varValue = $arrValues;
                            } else {
                                // prepare values
                                $arrNewValues = [];

                                foreach ($arrValues as $kVal => $vVal) {
                                    $vVal = trim($vVal);
                                    $strK = false;
                                    if (\strlen($vVal) && false === $strK) {
                                        // handle grouped options
                                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] as $strOptsKey => $varOpts) {
                                            if (\is_array($varOpts)) {
                                                $strK = array_search($vVal, $varOpts, true);
                                            } else {
                                                $strK = array_search($vVal, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'], true);
                                            }

                                            if (false !== $strK) {
                                                $arrNewValues[] = $strK;
                                                break;
                                            }
                                        }

                                        // add saved option to available options if it does not exist
                                        if (false === $strK) {
                                            $strK = preg_replace('/(.*?\[)(.*?)(\])/si', '$2', $vVal);
                                            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$strK] = $vVal;
                                            $arrNewValues[] = $strK;
                                        }
                                    }
                                }

                                $this->varValue = $arrNewValues;
                            }
                        }
                    }

                    // field type single checkbox
                    elseif ('checkbox' === $strInputType && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple']) {
                        // Modify options to handle Contao 3 new validation in Widget::isValidOption()
                        if (\in_array($this->strField, $this->arrDetailFields, true)) {
                            if (!\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'])) {
                                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = [
                                    '1' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['label'][0],
                                ];
                            }
                            $strFirstOpt = array_pop($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);

                            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][1] = $strFirstOpt;
                            if (!empty($this->varValue)) {
                                $this->varValue = '1';
                            }
                        }
                    }

                    // field type efgLookupSelect
                    elseif ('efgLookupSelect' === $strInputType) {
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupSelect ['TL_DCA'][".$this->strTable."]['fields'][".$this->strField.']');
                        $arrFieldOptions = $this->Formdata->prepareWidgetOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'arrFieldOptions len '.count($arrFieldOptions));
                        $arrNewOptions = [];

                        // prepare options array and value
                        if (\is_array($arrFieldOptions)) {
                            // prepare options array
                            foreach ($arrFieldOptions as $k => $v) {
                                $arrNewOptions[$v['value']] = $v['label'];
                            }
                        }
                        foreach ($arrNewOptions as $k => $v) {
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "gesetzt arrNewOptions[$k]: $v");
                        }
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;
                        // prepare varValue
                        if (!empty($this->varValue)) {
                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "varValue kein Array strSep $strSep varValue ".$this->varValue);
                                $this->varValue = explode($strSep, $this->varValue);
                            }
                            foreach ($this->varValue as $k => $v) {
                                $sNewVal = array_search($v, $arrNewOptions, true);   // suche $v in arrNewOptions der Key wird zurückgeliefert
                                // er entspricht dem darzustellenden Wert Nicht dem Optionvalue
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupSelect 1 this->varValue[$k]: $v in arrNewOptions ");
                                if ($sNewVal) {
                                    //$this->varValue[$v] = $sNewVal;              // PBD  so war es Orig
                                    // Warum wird varValue hier mit $v indiziert
                                    // das gibt doch einen neuen Value mit falschem Index
                                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupSelect 1 this->varValue[$k] alter Wert ".$this->varValue[$k]." neu gesetzt auf $sNewVal ");
                                    $this->varValue[$k] = $sNewVal;
                                }
                            }
                        }

                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'render type efgLookupSelect as SelectMenu this->strTable '.$this->strTable.' this->strField '.$this->strField);
                        // render type efgLookupSelect as SelectMenu
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'select';
                    }

                    // field type efgLookupCheckbox
                    elseif ('efgLookupCheckbox' === $strInputType) {
                        $arrFieldOptions = $this->Formdata->prepareWidgetOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
                        $arrNewOptions = [];

                        // prepare options array and value
                        if (\is_array($arrFieldOptions)) {
                            // prepare options array
                            foreach ($arrFieldOptions as $k => $v) {
                                $arrNewOptions[$v['value']] = $v['label'];
                            }
                        }

                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

                        // prepare varValue
                        if (!empty($this->varValue)) {
                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                $this->varValue = explode($strSep, $this->varValue);
                            }
                            foreach ($this->varValue as $k => $v) {
                                $sNewVal = array_search($v, $arrNewOptions, true);
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupCheckbox 1 this->varValue[$k]: $v in arrNewOptions ");
                                if ($sNewVal) {
                                    //$this->varValue[$v] = $sNewVal;              // PBD  so war es Orig
                                    // Warum wird varValue hier mit $v indiziert
                                    // das gibt doch einen neuen Value mit falschem Index
                                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupCheckbox 1 this->varValue[$k] alter Wert ".$this->varValue[$k]." neu gesetzt auf $sNewVal ");
                                    $this->varValue[$k] = $sNewVal;
                                }
                            }
                        }

                        // render type efgLookupCheckbox as CheckboxMenu
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'checkbox';
                    }

                    // field type efgLookupRadio
                    elseif ('efgLookupRadio' === $strInputType) {
                        $arrFieldOptions = $this->Formdata->prepareWidgetOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
                        $arrNewOptions = [];

                        // prepare options array and value
                        if (\is_array($arrFieldOptions)) {
                            // prepare options array
                            foreach ($arrFieldOptions as $k => $v) {
                                $arrNewOptions[$v['value']] = $v['label'];
                            }
                        }

                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

                        // prepare varValue
                        if (!empty($this->varValue)) {
                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                $this->varValue = explode($strSep, $this->varValue);
                            }
                            foreach ($this->varValue as $k => $v) {
                                $sNewVal = array_search($v, $arrNewOptions, true);
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupRadio 1 this->varValue[$k]: $v in arrNewOptions ");
                                if ($sNewVal) {
                                    //$this->varValue[$v] = $sNewVal;              // PBD  so war es Orig
                                    // Warum wird varValue hier mit $v indiziert
                                    // das gibt doch einen neuen Value mit falschem Index
                                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupRadio 1 this->varValue[$k] alter Wert ".$this->varValue[$k]." neu gesetzt auf $sNewVal ");
                                    $this->varValue[$k] = $sNewVal;
                                }
                            }
                        }

                        // render type efgLookupRadio as RadioMenu
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'radio';
                    } else {
                        $this->varValue = $this->Formdata->prepareDatabaseValueForWidget($this->varValue, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
                    }

                    $this->objActiveRecord->{$this->strField} = $this->varValue;

                    // Call load_callback
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback) {
                            if (\is_array($callback)) {
                                $this->import($callback[0]);
                                $this->varValue = $this->{$callback[0]}->{$callback[1]}($this->varValue, $this);  //Änderung PBD
                            } elseif (\is_callable($callback)) {
                                $this->varValue = $callback($this->varValue, $this);
                            }
                        }

                        $this->objActiveRecord->{$this->strField} = $this->varValue;
                    }

                    // Build the row and pass the current palette string
                    $blnAjax ? $strAjax .= $this->row($this->strPalette) : $return .= $this->row($this->strPalette);
                }

                $class = 'tl_box';
                $return .= "\n".'</fieldset>';
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'render result '.$return);
            }
        }

        $version = '';

        // Submit buttons
        $arrButtons = [];
        $arrButtons['save'] = '<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'">';

        if (!\Input::get('nb')) {
            $arrButtons['saveNclose'] = '<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'">';
        }

        if (!\Input::get('popup') && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) {
            $arrButtons['saveNcreate'] = '<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNcreate']).'">';
        }

        if (\Input::get('s2e')) {
            $arrButtons['saveNedit'] = '<input type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNedit']).'">';
        } elseif (!\Input::get('popup') && (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] || (isset($this->ptable) && \strlen($this->ptable)) || $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'])) {
            $arrButtons['saveNback'] = '<input type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNback']).'">';
        }

        // Call the buttons_callback (see #4691)
        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);          //Änderung PBD
                } elseif (\is_callable($callback)) {
                    $arrButtons = $callback($arrButtons, $this);
                }
            }
        }

        // Add some buttons and end the form
        $return .= '
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  '.implode(' ', $arrButtons).'
</div>

</div>
</form>

<script>
  window.addEvent(\'domready\', function() {
    Theme.focusInput("'.$this->strTable.'");
  });
</script>';

        // TODO: find a better solution to handle toggleSubpalette ...
        $return .= $this->getSubpaletteJavascript();
        $return .= $this->getFilepickerJavascript('reloadEfgFiletree');
        // Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
        $return = $version.'
<div id="tl_buttons">'.(\Input::get('nb') ? '&nbsp;' : '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>').'
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], ($this->intId ? 'ID '.$this->intId : '')).'</h2>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="'.($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded').'"'.(!empty($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.specialchars($this->strTable).'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return;

        // Reload the page to prevent _POST variables from being sent twice
        if (\Input::post('FORM_SUBMIT') === $this->strTable && !$this->noReload) {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'FORM_SUBMIT this->strTable '.$this->strTable);
            $arrValues = $this->values;
            array_unshift($arrValues, time());

            // Trigger the onsubmit_callback
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
                    if (\is_array($callback)) {
                        $this->import($callback[0]);
                        $this->{$callback[0]}->{$callback[1]}($this);      //Änderung PBD
                    } elseif (\is_callable($callback)) {
                        $callback($this);
                    }
                }
            }

            // Set the current timestamp
            \Database::getInstance()->prepare('UPDATE '.$this->strTable.' SET tstamp=? WHERE '.implode(' AND ', $this->procedure))
                ->execute($arrValues)
                        ;

            // Redirect
            if (isset($_POST['saveNclose'])) {
                \Message::reset();
                \System::setCookie('BE_PAGE_OFFSET', 0, 0);
                \Controller::redirect($this->getReferer());
            } elseif (isset($_POST['saveNedit'])) {
                \Message::reset();
                \System::setCookie('BE_PAGE_OFFSET', 0, 0);
                $strUrl = \Backend::addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href']);

                $strUrl = preg_replace('/(&amp;)?s2e=[^&]*/i', '', $strUrl);
                $strUrl = preg_replace('/(&amp;)?act=[^&]*/i', '', $strUrl);

                \Controller::redirect($strUrl);
            } elseif (isset($_POST['saveNback'])) {
                \Message::reset();
                \System::setCookie('BE_PAGE_OFFSET', 0, 0);

                \Controller::redirect(\Environment::get('script').'?do='.\Input::get('do'));
            } elseif (isset($_POST['saveNcreate'])) {
                \Message::reset();
                \System::setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                //$strUrl = \Environment::get('script') . '?do=' . \Input::get('do');
                $strUrl = 'contao'.'?do='.\Input::get('do');    // PBD redirekt auf contao geht jetzt so !!

                if (isset($_GET['table'])) {
                    $strUrl .= '&amp;table='.\Input::get('table');
                }

                $strUrl .= (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) && \strlen($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'])) ? '&amp;act=create&amp;mode=2&amp;pid='.CURRENT_ID : '&amp;act=create';
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "controler redirekt strUrl $strUrl referrer ".$this->getReferer());
                \Controller::redirect($strUrl.'&amp;rt='.REQUEST_TOKEN);
            }

            \Controller::reload();
        }

        // Set the focus if there is an error
        if ($this->noReload) {
            $return .= '

<script>
  window.addEvent(\'domready\', function() {
    Backend.vScrollTo(($(\''.$this->strTable.'\').getElement(\'label.error\').getPosition().y - 20));
  });
</script>';
        }

        return $return;
    }

    /**
     * Auto-generate a form to edit all records that are currently shown.
     *
     * @param int
     * @param int
     * @param mixed|null $intId
     * @param mixed|null $ajaxId
     *
     * @return string
     */
    public function editAll($intId = null, $ajaxId = null)
    {
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, '..');

        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
            $this->log('Table "'.$this->strTable.'" is not editable', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $return = '';
        $this->import('BackendUser', 'User');

        // Get current IDs from session
        $session = $this->Session->getData();
        $ids = $session['CURRENT']['IDS'];

        if ('' !== $intId && \Environment::get('isAjaxRequest')) {
            $ids = [$intId];
        }

        // Save field selection in session
        if (\Input::post('FORM_SUBMIT') === $this->strTable.'_all' && \Input::get('fields')) {
            $session['CURRENT'][$this->strTable] = \Input::post('all_fields');
            $this->Session->setData($session);
        }

        // Add fields
        $fields = $session['CURRENT'][$this->strTable];

        if (!empty($fields) && \is_array($fields) && \Input::get('fields')) {
            $class = 'tl_tbox';
            $this->checkForTinyMce();

            // Walk through each record
            foreach ($ids as $id) {
                $this->intId = $id;
                $this->procedure = ['id=?'];
                $this->values = [$this->intId];
                $this->blnCreateNewVersion = false;
                $this->strPalette = trimsplit('[;,]', $this->getPalette());

                // Add meta fields if the current user is an administrator
                if ($this->User->isAdmin) {
                    if (\Database::getInstance()->fieldExists('sorting', $this->strTable)) {
                        array_unshift($this->strPalette, 'sorting');
                    }

                    if (\Database::getInstance()->fieldExists('pid', $this->strTable)) {
                        array_unshift($this->strPalette, 'pid');
                    }

                    $GLOBALS['TL_DCA'][$this->strTable]['fields']['pid'] = ['label' => &$GLOBALS['TL_LANG']['MSC']['pid'], 'inputType' => 'text', 'eval' => ['rgxp' => 'digit']];
                    $GLOBALS['TL_DCA'][$this->strTable]['fields']['sorting'] = ['label' => &$GLOBALS['TL_LANG']['MSC']['sorting'], 'inputType' => 'text', 'eval' => ['rgxp' => 'digit']];
                }

                // Begin current row
                $strAjax = '';
                $blnAjax = false;
                $return .= '
<div class="'.$class.'">';

                $class = 'tl_box';
                $formFields = [];

                $arrBaseFields = [];
                $arrDetailFields = [];
                $arrSqlDetails = [];

                foreach ($fields as $strField) {
                    if (\in_array($strField, $this->arrBaseFields, true)) {
                        $arrBaseFields[] = $strField;
                    } elseif (\in_array($strField, $this->arrDetailFields, true)) {
                        $arrDetailFields[] = $strField;
                        $arrSqlDetails[] = '(SELECT value FROM tl_formdata_details WHERE ff_name=\''.$strField.'\' AND pid=f.id) AS `'.$strField.'`';
                    }
                }

                $strSqlFields = (!empty($arrBaseFields) ? implode(', ', $arrBaseFields) : '');
                $strSqlFields .= (!empty($arrSqlDetails) ? (\strlen($strSqlFields) ? ', ' : '').implode(', ', $arrSqlDetails) : '');

                // Get the field values
                $objRow = \Database::getInstance()->prepare('SELECT '.$strSqlFields.' FROM '.$this->strTable.' f WHERE id=?')
                    ->limit(1)
                    ->execute($this->intId)
                ;

                // Store the active record
                $this->objActiveRecord = $objRow;
                $blnIsFirst = true;

                foreach ($this->strPalette as $v) {
                    // Check whether field is excluded
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude']) {
                        continue;
                    }

                    if ('[EOF]' === $v) {
                        if ($blnAjax && \Environment::get('isAjaxRequest')) {
                            return $strAjax.'<input type="hidden" name="FORM_FIELDS_'.$id.'[]" value="'.specialchars(implode(',', $formFields)).'">';
                        }

                        $blnAjax = false;
                        $return .= "\n  ".'</div>';

                        continue;
                    }

                    if (preg_match('/^\[.*\]$/', $v)) {
                        $thisId = 'sub_'.substr($v, 1, -1).'_'.$id;
                        $blnAjax = ($ajaxId === $thisId && \Environment::get('isAjaxRequest')) ? true : false;
                        $return .= "\n  ".'<div id="'.$thisId.'">';

                        continue;
                    }

                    if (!\in_array($v, $fields, true)) {
                        continue;
                    }

                    $this->strField = $v;
                    $this->strInputName = $v.'_'.$this->intId;
                    $formFields[] = $v.'_'.$this->intId;

                    // Set the default value and try to load the current value from DB
                    $this->varValue = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] ?: '';

                    if ($objRow->$v !== false) {
                        $this->varValue = $objRow->$v;
                    }

                    // Autofocus the first field
                    if ($blnIsFirst && 'text' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType']) {
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['autofocus'] = 'autofocus';
                        $blnIsFirst = false;
                    }

                    // Call options_callback
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'])) {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options_callback'][1];

                        $this->import($strClass);
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $this->$strClass->$strMethod($this);
                    }

                    // Convert CSV fields
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple']
                        && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) {
                        $this->varValue = trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'], $this->varValue);
                    }

                    // prepare values of special fields like radio, select and checkbox
                    $strInputType = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'];

                    // field types radio, select, multi checkbox
                    if (\in_array($strInputType, ['radio', 'select', 'conditionalselect', 'countryselect'], true)
                        || ('checkbox' === $strInputType && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'])) {
                        if (\in_array($this->strField, $this->arrBaseFields, true) && \in_array($this->strField, $this->arrOwnerFields, true)) {
                            if ('fd_user' === $this->strField) {
                                if ($this->User && $this->User->id) {
                                    $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] = $this->User->id;
                                }
                            }
                        } else {
                            // foreignKey fields
                            if ('select' === $strInputType && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey'])&&\strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey'])) {
                                // include blank Option
                                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['includeBlankOption'] = true;

                                $arrKey = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey']);
                                $strForeignTable = $arrKey[0];
                                $strForeignField = $arrKey[1];

                                // WHERE condition for foreignKey
                                $strForeignKeyCond = '';
                                if (\strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere'])) {
                                    $strForeignKeyCond = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKeyWhere'];
                                }

                                // check if foreignKey table is a formdata table
                                if ('fd_' === substr($strForeignTable, 0, 3)) {
                                    $strFormKey = substr($strForeignTable, 3);
                                    $strForeignDcaKey = $strForeignTable;
                                    $strForeignTable = 'tl_formdata';

                                    // backup current dca and load dca for foreign formdata
                                    $BAK_DCA = $GLOBALS['TL_DCA'][$this->strTable];
                                    $this->loadDataContainer($strForeignDcaKey);

                                    $strForeignField = $arrKey[1];
                                    $strForeignSqlField = '(SELECT value FROM tl_formdata_details WHERE ff_name="'.$strForeignField.'" AND pid=f.id ) AS `'.$strForeignField.'`';

                                    $sqlForeignFd = 'SELECT f.id,'.$strForeignSqlField.' FROM tl_formdata f, tl_formdata_details fd ';
                                    $sqlForeignFd .= 'WHERE (f.id=fd.pid) AND f.'.$GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterKey']."='".$GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['formFilterValue']."' AND fd.ff_name='".$strForeignField."'";

                                    if (!empty($strForeignKeyCond)) {
                                        $arrForeignKeyCond = preg_split('/([\s!=><]+)/', $strForeignKeyCond, -1, PREG_SPLIT_DELIM_CAPTURE);
                                        $strForeignCondField = $arrForeignKeyCond[0];
                                        unset($arrForeignKeyCond[0]);
                                        if (\in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['baseFields'], true)) {
                                            $sqlForeignFd .= ' AND f.'.$strForeignCondField.implode('', $arrForeignKeyCond);
                                        }
                                        if (\in_array($strForeignCondField, $GLOBALS['TL_DCA'][$strForeignTable]['tl_formdata']['detailFields'], true)) {
                                            $sqlForeignFd .= ' AND (SELECT value FROM tl_formdata_details WHERE ff_name="'.$strForeignCondField.'" AND pid=f.id ) '.implode('', $arrForeignKeyCond);
                                        }
                                    }

                                    $objForeignFd = \Database::getInstance()->prepare($sqlForeignFd)->execute();

                                    // reset current dca
                                    $GLOBALS['TL_DCA'][$this->strTable] = $BAK_DCA;
                                    unset($BAK_DCA);

                                    if ($objForeignFd->numRows) {
                                        $arrForeignRecords = $objForeignFd->fetchAllAssoc();
                                        if (!empty($arrForeignRecords)) {
                                            foreach ($arrForeignRecords as $arrForeignRecord) {
                                                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField].' [~'.$arrForeignRecord['id'].'~]';
                                            }
                                        }
                                        unset($arrForeignRecords);
                                    }

                                    // unset dca 'foreignKey': prevents \Widget::getAttributesFromDca to read options from table instead handle as normal select
                                    unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey'], $objForeignFd);
                                }
                                // foreignKey table is 'normal' table
                                elseif (\Database::getInstance()->fieldExists($strForeignField, $strForeignTable)) {
                                    $blnAlias = \Database::getInstance()->fieldExists('alias', $strForeignTable);

                                    $sqlForeign = 'SELECT id,'.($blnAlias ? 'alias,' : '').$strForeignField.' FROM '.$strForeignTable.(\strlen($strForeignKeyCond) ? ' WHERE '.$strForeignKeyCond : '').' ORDER BY '.$strForeignField;

                                    $objForeign = \Database::getInstance()->prepare($sqlForeign)->execute();

                                    if ($objForeign->numRows) {
                                        $arrForeignRecords = $objForeign->fetchAllAssoc();
                                        if (!empty($arrForeignRecords)) {
                                            foreach ($arrForeignRecords as $arrForeignRecord) {
                                                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$arrForeignRecord['id']] = $arrForeignRecord[$strForeignField].' [~'.(($blnAlias && \strlen($arrForeignRecord['alias'])) ? $arrForeignRecord['alias'] : $arrForeignRecord['id']).'~]';
                                            }
                                        }
                                        unset($arrForeignRecords);
                                    }

                                    // unset dca 'foreignKey': prevents \Widget::getAttributesFromDca to read options from table instead handle as normal select
                                    unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['foreignKey'], $objForeign);
                                }
                                // sort options on label
                                asort($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);
                            }

                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                $arrValues = explode($strSep, $this->varValue);
                            } else {
                                $arrValues = $this->varValue;
                            }

                            if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['efgStoreValues']) {
                                $this->varValue = $arrValues;
                            } else {
                                // prepare values
                                $arrNewValues = [];

                                foreach ($arrValues as $kVal => $vVal) {
                                    $vVal = trim($vVal);
                                    $strK = false;
                                    if (\strlen($vVal) && false === $strK) {
                                        // handle grouped options
                                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] as $strOptsKey => $varOpts) {
                                            if (\is_array($varOpts)) {
                                                $strK = array_search($vVal, $varOpts, true);
                                            } else {
                                                $strK = array_search($vVal, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'], true);
                                            }

                                            if (false !== $strK) {
                                                $arrNewValues[] = $strK;
                                                break;
                                            }
                                        }

                                        // add saved option to available options if it does not exist
                                        if (false === $strK) {
                                            $strK = preg_replace('/(.*?\[)(.*?)(\])/si', '$2', $vVal);
                                            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][$strK] = $vVal;
                                            $arrNewValues[] = $strK;
                                        }
                                    }
                                }

                                $this->varValue = $arrNewValues;
                            }
                        }
                    }

                    // field type single checkbox
                    elseif ('checkbox' === $strInputType && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple']) {
                        // Modify options to handle Contao 3 new validation in Widget::isValidOption()
                        if (\in_array($this->strField, $this->arrDetailFields, true)) {
                            $strFirstOpt = array_pop($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options']);

                            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'][1] = $strFirstOpt;
                            if (!empty($this->varValue)) {
                                $this->varValue = '1';
                            }
                        }
                    }

                    // field type efgLookupSelect
                    elseif ('efgLookupSelect' === $strInputType) {
                        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, '657 prepareWidgetOptions strType'.$strInputType.'this->strTable'.$this->strTable.'this->strField'.$this->strField.' val '.$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
                        $arrFieldOptions = $this->Formdata->prepareWidgetOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

                        // prepare options array and value
                        if (\is_array($arrFieldOptions)) {
                            // prepare options array
                            $arrNewOptions = [];
                            foreach ($arrFieldOptions as $k => $v) {
                                $arrNewOptions[$v['value']] = $v['label'];
                            }
                        }

                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

                        // prepare varValue
                        if (!empty($this->varValue)) {
                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                $this->varValue = explode($strSep, $this->varValue);
                            }
                            foreach ($this->varValue as $k => $v) {
                                $sNewVal = array_search($v, $arrNewOptions, true);
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupSelect 2 this->varValue[$k]: $v in arrNewOptions ");
                                if ($sNewVal) {
                                    //$this->varValue[$v] = $sNewVal;              // PBD  so war es Orig
                                    // Warum wird varValue hier mit $v indiziert
                                    // das gibt doch einen neuen Value mit falschem Index
                                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupSelect 2 this->varValue[$k] alter Wert ".$this->varValue[$k]." neu gesetzt auf $sNewVal ");
                                    $this->varValue[$k] = $sNewVal;
                                }
                            }
                        }

                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'render type efgLookupSelect as SelectMenu this->strTable '.$this->strTable.' this->strField '.$this->strField);
                        // render type efgLookupSelect as SelectMenu
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'select';
                    }

                    // field type efgLookupCheckbox
                    elseif ('efgLookupCheckbox' === $strInputType) {
                        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, '657 prepareWidgetOptions strType'.$strInputType.'this->strTable'.$this->strTable.'this->strField'.$this->strField.' val '.$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
                        $arrFieldOptions = $this->Formdata->prepareWidgetOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

                        // prepare options array and value
                        if (\is_array($arrFieldOptions)) {
                            // prepare options array
                            $arrNewOptions = [];
                            foreach ($arrFieldOptions as $k => $v) {
                                $arrNewOptions[$v['value']] = $v['label'];
                            }
                        }

                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

                        // prepare varValue
                        if (!empty($this->varValue)) {
                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                $this->varValue = explode($strSep, $this->varValue);
                            }
                            foreach ($this->varValue as $k => $v) {
                                $sNewVal = array_search($v, $arrNewOptions, true);
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupCheckbox 2 this->varValue[$k]: $v in arrNewOptions ");
                                if ($sNewVal) {
                                    //$this->varValue[$v] = $sNewVal;              // PBD  so war es Orig
                                    // Warum wird varValue hier mit $v indiziert
                                    // das gibt doch einen neuen Value mit falschem Index
                                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupCheckbox 2 this->varValue[$k] alter Wert ".$this->varValue[$k]." neu gesetzt auf $sNewVal ");
                                    $this->varValue[$k] = $sNewVal;
                                }
                            }
                        }

                        // render type efgLookupCheckbox as CheckboxMenu
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'checkbox';
                    }

                    // field type efgLookupRadio
                    elseif ('efgLookupRadio' === $strInputType) {
                        $arrFieldOptions = $this->Formdata->prepareWidgetOptions($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);

                        // prepare options array and value
                        if (\is_array($arrFieldOptions)) {
                            // prepare options array
                            $arrNewOptions = [];
                            foreach ($arrFieldOptions as $k => $v) {
                                $arrNewOptions[$v['value']] = $v['label'];
                            }
                        }

                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['options'] = $arrNewOptions;

                        // prepare varValue
                        if (!empty($this->varValue)) {
                            if (!\is_array($this->varValue)) {
                                $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'] : '|';
                                $this->varValue = explode($strSep, $this->varValue);
                            }
                            foreach ($this->varValue as $k => $v) {
                                $sNewVal = array_search($v, $arrNewOptions, true);
                                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupRadio 2 this->varValue[$k]: $v in arrNewOptions ");
                                if ($sNewVal) {
                                    //$this->varValue[$v] = $sNewVal;              // PBD  so war es Orig
                                    // Warum wird varValue hier mit $v indiziert
                                    // das gibt doch einen neuen Value mit falschem Index
                                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "efgLookupRadio 2 this->varValue[$k] alter Wert ".$this->varValue[$k]." neu gesetzt auf $sNewVal ");
                                    $this->varValue[$k] = $sNewVal;
                                }
                            }
                        }

                        // render type efgLookupRadio as RadioMenu
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] = 'radio';
                    } else {
                        $this->varValue = $this->Formdata->prepareDatabaseValueForWidget($this->varValue, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]);
                    }

                    $this->objActiveRecord->{$this->strField} = $this->varValue;

                    // Call load_callback
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback) {
                            if (\is_array($callback)) {
                                $this->import($callback[0]);
                                $this->varValue = $this->{$callback[0]}->{$callback[1]}($this->varValue, $this);        //Änderung PBD
                            } elseif (\is_callable($callback)) {
                                $this->varValue = $callback($this->varValue, $this);
                            }
                        }

                        $this->objActiveRecord->{$this->strField} = $this->varValue;
                    }

                    // Build the current row
                    $blnAjax ? $strAjax .= $this->row() : $return .= $this->row();
                }

                // Close box
                $return .= '
 <input type="hidden" name="FORM_FIELDS_'.$this->intId.'[]" value="'.specialchars(implode(',', $formFields)).'">
</div>';

                // Save record
                if (\Input::post('FORM_SUBMIT') === $this->strTable && !$this->noReload) {
                    // Call onsubmit_callback
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
                            if (\is_array($callback)) {
                                $this->import($callback[0]);
                                $this->{$callback[0]}->{$callback[1]}($this);                 //Änderung PBD
                            } elseif (\is_callable($callback)) {
                                $callback($this);
                            }
                        }
                    }

                    // Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
                    \Database::getInstance()->prepare('UPDATE '.$this->strTable.' SET tstamp=? WHERE id=?')
                        ->execute(time(), $this->intId)
                                ;
                }
            }

            // Submit buttons
            $arrButtons = [];
            $arrButtons['save'] = '<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'">';
            $arrButtons['saveNclose'] = '<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'">';

            // Call the buttons_callback (see #4691)
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback) {
                    if (\is_array($callback)) {
                        $this->import($callback[0]);
                        $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);   //Änderung PBD
                    } elseif (\is_callable($callback)) {
                        $arrButtons = $callback($arrButtons, $this);
                    }
                }
            }

            // Add the form
            $return = '

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="'.($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded').'">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return.'

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  '.implode(' ', $arrButtons).'
</div>

</div>
</form>';

            // TODO: find a better solution to handle toggleSubpalette ...
            $return .= $this->getSubpaletteJavascript();
            $return .= $this->getFilepickerJavascript('reloadEfgFiletree');
            // Set the focus if there is an error
            if ($this->noReload) {
                $return .= '

<script>
  window.addEvent(\'domready\', function() {
    Backend.vScrollTo(($(\''.$this->strTable.'\').getElement(\'label.error\').getPosition().y - 20));
  });
</script>';
            }

            // Reload the page to prevent _POST variables from being sent twice
            if (\Input::post('FORM_SUBMIT') === $this->strTable && !$this->noReload) {
                if (\Input::post('saveNclose')) {
                    \System::setCookie('BE_PAGE_OFFSET', 0, 0);
                    \Controller::redirect($this->getReferer());
                }

                \Controller::reload();
            }
        }

        // Else show a form to select the fields
        else {
            $options = '';
            $fields = [];

            // Add fields of the current table
            $fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));

            // Add meta fields if the current user is an administrator
            if ($this->User->isAdmin) {
                if (\Database::getInstance()->fieldExists('sorting', $this->strTable) && !\in_array('sorting', $fields, true)) {
                    array_unshift($fields, 'sorting');
                }

                if (\Database::getInstance()->fieldExists('pid', $this->strTable) && !\in_array('pid', $fields, true)) {
                    array_unshift($fields, 'pid');
                }
            }

            // Show all non-excluded fields
            foreach ($fields as $field) {
                if ('pid' === $field || 'sorting' === $field || (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && ((isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'])&&\strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'])) || \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'])))) {
                    $options .= '
  <input type="checkbox" name="all_fields[]" id="all_'.$field.'" class="tl_checkbox" value="'.specialchars($field).'"> <label for="all_'.$field.'" class="tl_checkbox_label">'.($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] ?: $GLOBALS['TL_LANG']['MSC'][$field][0]).'</label><br>';
                }
            }

            $blnIsError = ($_POST && empty($_POST['all_fields']));

            // Return the select menu
            $return .= '

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand(\Environment::get('request'), true).'&amp;fields=1" id="'.$this->strTable.'_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'_all">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($blnIsError ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').'

<div class="tl_tbox">
<fieldset class="tl_checkbox_container">
  <legend'.($blnIsError ? ' class="error"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['all_fields'][0].'</legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6"><em>'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</em></label><br>'.$options.'
</fieldset>'.($blnIsError ? '
<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['all_fields'].'</p>' : (($GLOBALS['TL_CONFIG']['showHelp'] && \strlen($GLOBALS['TL_LANG']['MSC']['all_fields'][1])) ? '
<p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['all_fields'][1].'</p>' : '')).'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['continue']).'">
</div>

</div>
</form>';
        }

        // Return
        return '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>'.$return;
    }

    /**
     * Return the name of the current palette.
     *
     * @return string
     */
    public function getPalette()
    {
        $palette = 'default';
        $strPalette = $GLOBALS['TL_DCA'][$this->strTable]['palettes'][$palette];
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strTable '.$this->strTable.' default strPalettes  '.$strPalette);

        // Check whether there are selector fields
        if (!empty($GLOBALS['TL_DCA'][$this->strTable]['palettes']['__selector__'])) {
            $sValues = [];
            $subpalettes = [];

            $table_alias = ('tl_formdata' === $this->strTable ? ' f' : '');
            $sqlQuery = 'SELECT * '.(!empty($this->arrSqlDetails) ? ', '.implode(',', array_values($this->arrSqlDetails)) : '').' FROM '.$this->strTable.$table_alias.' WHERE id=?';
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "sqlquery $sqlquery this->intId ".$this->intId);

            $objFields = \Database::getInstance()->prepare($sqlQuery)
                ->limit(1)
                ->execute($this->intId)
            ;

            // Get selector values from DB
            if ($objFields->numRows > 0) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['palettes']['__selector__'] as $name) {
                    $trigger = $objFields->$name;
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "trigger $trigger FORM_SUBMIT '".\Input::post('FORM_SUBMIT')."'");

                    // Overwrite the trigger
                    if (\Input::post('FORM_SUBMIT') === $this->strTable) {
                        $key = ('editAll' === \Input::get('act')) ? $name.'_'.$this->intId : $name;

                        if (isset($_POST[$key])) {
                            $arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$name];
                            if (!isset($arrField['type'])) {
                                $arrField['type'] = $arrField['inputType'];
                            }
                            $trigger = $this->Formdata->preparePostValueForDatabase(\Input::post($key), $arrField);
                        }
                    }

                    if ('' !== $trigger) {
                        if (('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$name]['inputType'] || 'condition' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$name]['inputType']) && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$name]['eval']['multiple']) {
                            $sValues[] = $name;

                            // Look for a subpalette
                            if (\strlen($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$name])) {
                                $subpalettes[$name] = $GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$name];
                            }
                        } else {
                            $sValues[] = $trigger;

                            $key = $name.'_'.array_search($trigger, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$name]['options'], true);

                            // Look for a subpalette
                            if (\strlen($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$key])) {
                                $subpalettes[$name] = $GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$key];
                            }
                        }
                    }
                }
            }

            // Build possible palette names from the selector values
            if (!\count($sValues)) {
                $names = ['default'];
            } elseif (\count($sValues) > 1) {
                foreach ($sValues as $k => $v) {
                    // Unset selectors that just trigger subpalettes (see #3738)
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'][$v])) {
                        unset($sValues[$k]);
                    }
                }

                $names = $this->combiner($sValues);
            } else {
                $names = [$sValues[0]];
            }

            // Get an existing palette
            foreach ($names as $paletteName) {
                if (\strlen($GLOBALS['TL_DCA'][$this->strTable]['palettes'][$paletteName])) {
                    $strPalette = $GLOBALS['TL_DCA'][$this->strTable]['palettes'][$paletteName];
                    break;
                }
            }

            // Include subpalettes
            foreach ($subpalettes as $k => $v) {
                $strPalette = preg_replace('/\b'.preg_quote($k, '/').'\b/i', $k.',['.$k.'],'.$v.',[EOF]', $strPalette);
            }
        } else {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'no __selector__ ');
        }

        return $strPalette;
    }

    /**
     * Format a value.
     *
     * @param mixed
     * @param mixed
     */
    public function formatValue($k, $value)
    {
        $value = deserialize($value);

        $rgxp = '';
        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['rgxp']) {
            $rgxp = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['rgxp'];
        } else {
            $rgxp = $this->arrFF[$k]['rgxp'];
        }

        // Array
        if (\is_array($value)) {
            $value = implode(', ', $value);
        }

        // Date and time
        if ($value && 'date' === $rgxp) {
            $value = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $value);
        } elseif ($value && 'time' === $rgxp) {
            $value = \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $value);
        } elseif ($value && 'datim' === $rgxp) {
            $value = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $value);
        } elseif ($value && \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType'],
                ['checkbox', 'efgLookupCheckbox', 'select', 'conditionalselect', 'efgLookupSelect', 'radio'], true)) {
            $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['csv'] : '|';
            $value = str_replace($strSep, ', ', $value);
        }

        // owner fields fd_member, fd_user
        if (\in_array($k, $this->arrBaseFields, true) && \in_array($k, $this->arrOwnerFields, true)) {
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

        return $value;
    }

    /**
     * Send confirmation mail.
     *
     * @param int $intID ID of record
     *
     * @return string
     */
    public function mail($intID = false)
    {
        $blnSend = false;

        if (\strlen((string)\Input::get('token')) && \Input::get('token') === $this->Session->get('fd_mail_send')) {          # PBD leycom
            $blnSend = true;
        }

        $strFormFilter = ('tl_formdata' === $this->strTable && \strlen($this->strFormKey) ? $this->sqlFormFilter : '');
        $table_alias = ('tl_formdata' === $this->strTable ? ' f' : '');

        if ($intID) {
            $this->intId = $intID;
        }

        $return = '';
        $this->values[] = $this->intId;
        $this->procedure[] = 'id=?';
        $this->blnCreateNewVersion = false;

        // Get current record
        $sqlQuery = 'SELECT * '.(!empty($this->arrSqlDetails) ? ', '.implode(',', array_values($this->arrSqlDetails)) : '').' FROM '.$this->strTable.$table_alias;
        $sqlWhere = ' WHERE id=?';
        if ('' !== $sqlWhere) {
            $sqlQuery .= $sqlWhere;
        }

        $objRow = \Database::getInstance()->prepare($sqlQuery)
            ->limit(1)
            ->execute($this->intId)
        ;

        // Redirect if there is no record with the given ID
        if ($objRow->numRows < 1) {
            $this->log('b Could not load record "'.$this->strTable.'.id='.$this->intId.'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrSubmitted = $objRow->fetchAssoc();
        $arrFiles = [];

        // Form
        $objForm = null;
        $intFormId = 0;

        if (!empty($GLOBALS['TL_DCA'][$this->strTable]['tl_formdata']['detailFields'])) {
            // Try to get the form
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['tl_formdata']['detailFields'] as $strField) {
                if (null !== $objForm) {
                    break;
                }

                if (!empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strField]['f_id'])) {
                    $intFormId = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strField]['f_id'];
                    $objForm = \FormModel::findByPk($intFormId);
                }
            }
        }

        if (null === $objForm) {
            $objForm = \FormModel::findOneBy('title', $arrSubmitted['form']);
        }

        if (null === $objForm) {
            $this->log('c Could not load record "tl_form.id='.$intFormId.'" / "tl_form.title='.$arrSubmitted['form'].'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrForm = $objForm->row();
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, ' e) bearbeite FORM id:  '.$arrForm['id'].' title: '.$arrForm['title']);
        $arrFormFields = $this->Formdata->getFormfieldsAsArray($arrForm['id']);

        if (empty($arrForm['confirmationMailSubject']) || (empty($arrForm['confirmationMailText']) && empty($arrForm['confirmationMailTemplate']))) {
            return '<p class="tl_error">Can not send this form data record.<br>Missing "Subject", "Text of confirmation mail" or "HTML-template for confirmation mail"<br>Please check configuration of form in form generator.</p>';
        }

        $this->loadDataContainer('tl_files');

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

        $blnStoreOptionsValues = ($arrForm['efgStoreValues']) ? true : false;

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

        if (!empty($recipientFieldName) && !empty($arrSubmitted[$recipientFieldName])) {
            $varRecipient = $arrSubmitted[$recipientFieldName];
            // handle efg option 'save options of values' for field types radio, select, checkbox
            if (\in_array($arrFormFields[$recipientFieldName]['type'], ['radio', 'select', 'checkbox'], true)) {
                if (!$blnStoreOptionsValues) {
                    $arrRecipient = $this->Formdata->prepareDatabaseValueForWidget($varRecipient, $arrFormFields[$recipientFieldName], false);
                    if (!empty($arrRecipient)) {
                        $varRecipient = implode(', ', $arrRecipient);
                    }
                    unset($arrRecipient);
                }
            }
            $strSep = (isset($arrFormFields[$recipientFieldName]['eval']['csv'])) ? $arrFormFields[$recipientFieldName]['eval']['csv'] : '|';
            $varRecipient = str_replace($strSep, ',', $varRecipient);
        }

        if (\strlen($varRecipient) || \strlen($arrForm['confirmationMailRecipient'])) {
            $arrRecipient = array_merge(trimsplit(',', $varRecipient), trimsplit(',', $arrForm['confirmationMailRecipient']));
        }

        if (\Input::get('recipient')) {
            $arrRecipient = trimsplit(',', \Input::get('recipient'));
        }

        if (\is_array($arrRecipient)) {
            $strRecipient = implode(', ', $arrRecipient);

            // handle insert tag {{user::email}} in recipient fields
            if (!\is_bool(strpos($strRecipient, '{{user::email}}')) && $arrSubmitted['fd_member'] > 0) {
                $objUser = \Database::getInstance()->prepare('SELECT `email` FROM `tl_member` WHERE id=?')
                    ->limit(1)
                    ->execute($arrSubmitted['fd_member'])
                ;

                $arrRecipient = array_map('str_replace', array_fill(0, \count($arrRecipient), '{{user::email}}'), array_fill(0, \count($arrRecipient), $objUser->email), $arrRecipient);
            }
            $arrRecipient = array_filter(array_unique($arrRecipient));
        }
        $objMailProperties->recipients = $arrRecipient;

        // Check if we want custom attachments... (Thanks to Torben Schwellnus)
        if ($arrForm['addConfirmationMailAttachments']) {
            if ($arrForm['confirmationMailAttachments']) {
                $arrCustomAttachments = deserialize($arrForm['confirmationMailAttachments'], true);

                if (!empty($arrCustomAttachments)) {
                    foreach ($arrCustomAttachments as $varFile) {
                        $objFileModel = \FilesModel::findById($varFile);

                        if (null !== $objFileModel) {
                            $objFile = new \File($objFileModel->path, true);
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

        $objMailProperties->subject = \StringUtil::decodeEntities($arrForm['confirmationMailSubject']);
        $objMailProperties->messageText = \StringUtil::decodeEntities($arrForm['confirmationMailText']);
        $objMailProperties->messageHtmlTmpl = $arrForm['confirmationMailTemplate'];

        // Replace Insert tags and conditional tags
        $objMailProperties = $this->Formdata->prepareMailData($objMailProperties, $arrSubmitted, $arrFiles, $arrForm, $arrFormFields);

        $objEmail = new \Email();
        $objEmail->from = $objMailProperties->sender;

        if (!empty($objMailProperties->senderName)) {
            $objEmail->fromName = $objMailProperties->senderName;
        }
        $objEmail->subject = $objMailProperties->subject;

        if (!empty($objMailProperties->attachments)) {
            foreach ($objMailProperties->attachments as $strFile => $varParams) {
                $strContent = file_get_contents($varParams['file'], false);
                $objEmail->attachFileFromString($strContent, $varParams['name'], $varParams['mime']);
            }
        }

        if (!empty($objMailProperties->messageText)) {
            $objEmail->text = $objMailProperties->messageText;
        }

        if (!empty($objMailProperties->messageHtml)) {
            $objEmail->html = $objMailProperties->messageHtml;
        }

        // Send Mail
        if (\strlen((string)\Input::get('token')) && \Input::get('token') === $this->Session->get('fd_mail_send')) {   # PBD leycom
            $this->Session->set('fd_mail_send', null);
            $blnSend = true;

            $blnConfirmationSent = false;
            if ($blnSend) {
                // Send e-mail
                if (!empty($objMailProperties->recipients)) {
                    foreach ($objMailProperties->recipients as $recipient) {
                        if (\strlen($recipient)) {
                            $recipient = str_replace(['[', ']'], ['<', '>'], $recipient);
                            $recipientName = '';
                            if (strpos($recipient, '<') > 0) {
                                preg_match('/(.*)?<(\S*)>/si', $recipient, $parts);
                                $recipientName = trim($parts[1]);
                                $recipient = (\strlen($recipientName) ? $recipientName.' <'.$parts[2].'>' : $parts[2]);
                            }
                        }

                        $objEmail->sendTo($recipient);
                        $blnConfirmationSent = true;

                        \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_formdata']['mail_sent'], str_replace(['<', '>'], ['[', ']'], $recipient)));
                    }
                }

                if ($blnConfirmationSent && isset($this->intId) && (int) ($this->intId) > 0) {
                    $arrUpd = ['confirmationSent' => '1', 'confirmationDate' => time()];
                    $res = \Database::getInstance()->prepare('UPDATE tl_formdata %s WHERE id=?')
                        ->set($arrUpd)
                        ->execute($this->intId)
                    ;
                }
            }
        }

        $strToken = md5(uniqid('', true));
        $this->Session->set('fd_mail_send', $strToken);

        $strHint = '';

        if (\strlen($objRow->confirmationSent)) {
            if (!$blnSend) {
                if (\strlen($objRow->confirmationDate)) {
                    $dateConfirmation = new \Date($objRow->confirmationDate);
                    $strHint .= '<div class="tl_message"><p class="tl_info">'.sprintf($GLOBALS['TL_LANG']['tl_formdata']['confirmation_sent'], $dateConfirmation->date, $dateConfirmation->time).'</p></div>';
                } else {
                    $strHint .= '<div class="tl_message"><p class="tl_info">'.sprintf($GLOBALS['TL_LANG']['tl_formdata']['confirmation_sent'], '-n/a-', '-n/a-').'</p></div>';
                }
            }
        }

        // Preview Mail
        $return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(ENCODE_AMPERSANDS).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_formdata']['mail'][0].'</h2>'.\Message::generate().$strHint.'

<form action="'.ampersand(\Environment::get('script'), ENCODE_AMPERSANDS).'" id="tl_formdata_send" class="tl_form" method="get">
<div class="tl_formbody_edit fd_mail_send">
<input type="hidden" name="do" value="'.\Input::get('do').'">
<input type="hidden" name="table" value="'.\Input::get('table').'">
<input type="hidden" name="act" value="'.\Input::get('act').'">
<input type="hidden" name="id" value="'.\Input::get('id').'">
<input type="hidden" name="rt" value="'.REQUEST_TOKEN.'">
<input type="hidden" name="token" value="'.$strToken.'">

<table cellpadding="0" cellspacing="0" class="prev_header" summary="">
  <tr class="row_0">
    <td class="col_0">'.$GLOBALS['TL_LANG']['tl_formdata']['mail_sender'][0].'</td>
    <td class="col_1">'.$objMailProperties->sender.'</td>
  </tr>

  <tr class="row_1">
    <td class="col_0"><label for="ctrl_formdata_recipient">'.$GLOBALS['TL_LANG']['tl_formdata']['mail_recipient'][0].'</label></td>
    <td class="col_1"><input name="recipient" type="ctrl_recipient" class="tl_text" value="'.implode(',', $objMailProperties->recipients).'" '.($blnSend ? 'disabled="disabled"' : '').'></td>
  </tr>

  <tr class="row_2">
    <td class="col_0">'.$GLOBALS['TL_LANG']['tl_formdata']['mail_subject'][0].'</td>
    <td class="col_1">'.$objMailProperties->subject.'</td>
  </tr>';

        if (!empty($objMailProperties->attachments)) {
            $attachments = [];
            foreach ($objMailProperties->attachments as $strFile => $arr) {
                $attachments[] = str_replace(\System::getContainer()->getParameter('kernel.project_dir').'/', '', $strFile);
            }

            $return .= '
  <tr class="row_3">
    <td class="col_0" style="vertical-align:top">'.$GLOBALS['TL_LANG']['tl_formdata']['attachments'].'</td>
    <td class="col_1">'.implode(',<br> ', $attachments).'</td>
  </tr>';
        }

        $return .= '
</table>

<h3>'.$GLOBALS['TL_LANG']['tl_formdata']['mail_body_plaintext'][0].'</h3>
<div class="preview_plaintext">
'.nl2br($objMailProperties->messageText).'
</div>';

        if (!empty($objMailProperties->messageHtml)) {
            $return .= '
<h3>'.$GLOBALS['TL_LANG']['tl_formdata']['mail_body_html'][0].'</h3>
<div class="preview_html">
'.preg_replace(['/.*?<body.*?>/si', '/<\/body>.*$/si'], ['', ''], $objMailProperties->messageHtml).'
</div>';
        }

        $return .= '
</div>';

        if (!$blnSend) {
            $return .= '
<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" id="send" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_formdata']['mail'][0]).'">
</div>

</div>';
        }

        $return .= '
</form>';

        return $return;
    }

    public function importFile()
    {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'importFile import_source '.\Input::post('import_source'));

        if ('import' !== \Input::get('key')) {
            return '';
        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'key '.\Input::get('key'));

        if (null === $this->arrImportIgnoreFields) {
            $this->arrImportIgnoreFields = ['id', 'pid', 'tstamp', 'form', 'ip', 'date', 'confirmationSent', 'confirmationDate', 'import_source'];
        }

        if (null === $this->arrImportableFields) {
            $arrFdFields = array_merge($this->arrBaseFields, $this->arrDetailFields);
            $arrFdFields = array_diff($arrFdFields, $this->arrImportIgnoreFields);
            foreach ($arrFdFields as $strFdField) {
                $this->arrImportableFields[$strFdField] = $GLOBALS['TL_DCA']['tl_formdata']['fields'][$strFdField]['label'][0];
            }
        }

        $arrSessionData = $this->Session->get('EFG_CO4');
        if (null === $arrSessionData) {
            $arrSessionData = [];
        }
        $this->Session->set('EFG_CO4', $arrSessionData);

        // Import CSV
        if ('tl_formdata_import' === $_POST['FORM_SUBMIT']) {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'tl_formdata_import ');

            $this->loadDataContainer('tl_files');

            $strMode = 'preview';
            $arrSessionData['import'][$this->strFormKey]['separator'] = $_POST['separator'];
            $arrSessionData['import'][$this->strFormKey]['csv_has_header'] = ('1' === $_POST['csv_has_header'] ? '1' : '');
            $this->Session->set('EFG_CO4', $arrSessionData);

            $this->log('PBD DC_Formdata importFile import_source '.\Input::post('import_source'), __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'importFile import_source '.\Input::post('import_source'));

            $objFileModel = \FilesModel::findById(\Input::post('import_source'));      // PBD  Umstellung auf uuid

            //if (intval(\Input::post('import_source')) == 0)    PBD Umstellung auf UID
            if (!$objFileModel) {
                $errstr = $GLOBALS['TL_LANG']['tl_formdata']['error_select_source'].' '.\Input::post('import_source').' not found';
                \Message::addError($errstr);
                \Controller::reload();
            }

            $objFile = new \File($objFileModel->path, true);

            if ('csv' !== $objFile->extension) {
                \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
                \System::setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                \Controller::reload();
            }

            // Get separator
            switch (\Input::post('separator')) {
                case 'semicolon':
                    $strSeparator = ';';
                    break;

                case 'tabulator':
                    $strSeparator = '\t';
                    break;

                case 'comma':
                default:
                    $strSeparator = ',';
                    break;
            }

            if ('import' === $_POST['FORM_MODE']) {
                $strMode = 'import';

                $time = time();
                $intTotal = null;
                $intInvalid = 0;
                $intValid = 0;

                $arrImportCols = \Input::post('import_cols');
                $arrSessionData['import'][$this->strFormKey]['import_cols'] = $arrImportCols;
                $this->Session->set('EFG_CO4', $arrSessionData);

                $arrMapFields = array_flip($arrImportCols);
                if (isset($arrMapFields['__IGNORE__'])) {
                    unset($arrMapFields['__IGNORE__']);
                }

                $blnUseCsvHeader = ('1' === $arrSessionData['import'][$this->strFormKey]['csv_has_header'] ? true : false);

                $arrEntries = [];
                $resFile = $objFile->handle;

                $timeNow = time();
                $strFormTitle = $this->Formdata->arrFormsDcaKey[substr($this->strFormKey, 3)];

                $strAliasField = (\strlen($this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['efgAliasField']) ? $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['efgAliasField'] : '');

                $objForm = \FormModel::findOneBy('title', $strFormTitle);

                if (null !== $objForm) {
                    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, ' f) bearbeite FORM id:  '.$objForm->id);
                    $arrFormFields = $this->Formdata->getFormfieldsAsArray($objForm->id);
                }

                while (($arrRow = @fgetcsv($resFile, null, $strSeparator)) !== false) {
                    if (null === $intTotal) {
                        $intTotal = 0;
                        if ($blnUseCsvHeader) {
                            continue;
                        }
                    }

                    $strAlias = '';
                    if (isset($arrRow[$arrMapFields['alias']]) && \strlen($arrRow[$arrMapFields['alias']])) {
                        $strAlias = $arrRow[$arrMapFields['alias']];
                    } elseif (isset($arrRow[$arrMapFields[$strAliasField]]) && \strlen($arrRow[$arrMapFields[$strAliasField]])) {
                        \Input::setPost($strAliasField, $arrRow[$arrMapFields[$strAliasField]]);
                    }

                    $arrDetailSets = [];

                    // prepare base data
                    $arrSet = [
                        'tstamp' => $timeNow,
                        'fd_member' => 0,
                        'fd_user' => (int) ($this->User->id),
                        'form' => $strFormTitle,
                        'ip' => \Environment::get('ip'),
                        'date' => $timeNow,
                        'published' => ('1' === $GLOBALS['TL_DCA']['tl_formdata']['fields']['published']['default'] ? '1' : ''),
                    ];

                    foreach ($arrMapFields as $strField => $intCol) {
                        if (\in_array($strField, $this->arrImportIgnoreFields, true)) {
                            continue;
                        }

                        if (\in_array($strField, $this->arrBaseFields, true)) {
                            $arrField = $GLOBALS['TL_DCA']['tl_formdata']['fields'][$strField];

                            if (\in_array($strField, $this->arrOwnerFields, true)) {
                                switch ($strField) {
                                    case 'fd_user':
                                        $array = 'arrUsers';
                                        break;

                                    case 'fd_member':
                                        $array = 'arrMembers';
                                        break;

                                    case 'fd_user_group':
                                        $array = 'arrUserGroups';
                                        break;

                                    case 'fd_member_group':
                                        $array = 'arrMemberGroups';
                                        break;
                                }

                                if (is_numeric($arrRow[$intCol]) && \array_key_exists($arrRow[$intCol], $this->{$array})) {
                                    $varValue = $arrRow[$intCol];
                                } elseif (\is_string($arrRow[$intCol])) {
                                    $varValue = (int) (array_search($arrRow[$intCol], $this->{$array}, true));
                                }
                            } elseif ('published' === $strField) {
                                if ($arrRow[$intCol] === $arrField['label'][0] || 1 === (int) ($arrRow[$intCol])) {
                                    $varValue = '1';
                                } else {
                                    $varValue = '';
                                }
                            } elseif ('alias' === $strField) {
                                continue;
                            } else {
                                $varValue = $arrRow[$intCol];
                            }
                            $arrSet[$strField] = $varValue;
                        }
                    }

                    // prepare details data
                    foreach ($arrMapFields as $strField => $intCol) {
                        if (\in_array($strField, $this->arrImportIgnoreFields, true)) {
                            continue;
                        }

                        if (\in_array($strField, $this->arrDetailFields, true)) {
                            // $arrField = array_merge($arrFormFields[$strField], $GLOBALS['TL_DCA']['tl_formdata']['fields'][$strField]);
                            $arrField = $GLOBALS['TL_DCA']['tl_formdata']['fields'][$strField];

                            $varValue = $this->Formdata->prepareImportValueForDatabase($arrRow[$intCol], $arrField);

                            // prepare details data
                            $arrDetailSet = [
                                // 'pid' => $intNewId,
                                'sorting' => $arrFormFields[$strField]['sorting'],
                                'tstamp' => $timeNow,
                                'ff_id' => $arrField['ff_id'],
                                'ff_name' => $strField,
                                'value' => $varValue,
                            ];

                            $arrDetailSets[] = $arrDetailSet;
                        }
                    }

                    $intNewId = 0;
                    $blnSaved = true;

                    if (!empty($arrDetailSets)) {
                        $objNewFormdata = \Database::getInstance()->prepare('INSERT INTO tl_formdata %s')->set($arrSet)->execute();
                        $intNewId = $objNewFormdata->insertId;

                        $strAlias = $this->Formdata->generateAlias($strAlias, $this->strFormFilterValue, $intNewId);
                        if (\strlen($strAlias)) {
                            \Database::getInstance()->prepare('UPDATE tl_formdata %s WHERE id=?')->set(['alias' => $strAlias])->execute($intNewId);
                        }

                        foreach ($arrDetailSets as $kD => $arrDetailSet) {
                            $arrDetailSet['pid'] = $intNewId;
                            try {
                                $objNewFormdataDetails = \Database::getInstance()->prepare('INSERT INTO tl_formdata_details %s')
                                    ->set($arrDetailSet)
                                    ->execute()
                                ;
                            } catch (\Exception $ee) {
                                $blnSaved = false;
                            }
                        }

                        if (false === $blnSaved && $intNewId > 0) {
                            \Database::getInstance()->prepare('DELETE FROM tl_formdata WHERE id=?')->execute($intNewId);
                        }
                    } else {
                        $blnSaved = false;
                    }

                    if ($blnSaved) {
                        ++$intValid;
                    } else {
                        ++$intInvalid;
                    }

                    ++$intTotal;
                }

                \Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_formdata']['import_confirm'], $intValid));

                if ($intInvalid > 0) {
                    \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_formdata']['import_invalid'], $intInvalid));
                }

                // Add a log entry
                $this->log('Imported file "'.$objFile->filename.'" into form data "'.$strFormTitle.'", created '.$intValid.' new records', __METHOD__, TL_GENERAL);

                \System::setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                \Controller::reload();
            }

            // Generate preview and form to select import fields
            if ('preview' === $strMode) {
                return $this->formImportPreview($objFile, $strSeparator);
            }
        }

        return $this->formImportSource();
    }

    public function export($strMode = 'csv'): void
    {
        $expmode = \Input::get('expmode');
        if (isset($expmode) && \strlen(\Input::get('expmode'))) {
            $strMode = \Input::get('expmode');
        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "strMode $strMode");

        $return = '';

        $blnCustomXlsExport = false;
        $blnCustomExport = false;
        $arrHookData = [];
        $arrHookDataColumns = [];

        if ('xls' === $strMode) {
            // Check for HOOK efgExportXls
            if (\array_key_exists('efgExportXls', $GLOBALS['TL_HOOKS']) && \is_array($GLOBALS['TL_HOOKS']['efgExportXls'])) {
                $blnCustomXlsExport = true;
            }
        } elseif ('csv' !== $strMode) {
            $blnCustomExport = true;
        }

        // filter or search for values
        $session = $this->Session->getData();

        $showFields = array_merge($this->arrBaseFields, $this->arrDetailFields);
        $ignoreFields = ['tstamp'];

        if (!empty($this->arrExportIgnoreFields)) {
            $ignoreFields = array_unique(array_merge($ignoreFields, $this->arrExportIgnoreFields));
        }

        $table = (6 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->ptable : $this->strTable;
        $table_alias = ('tl_formdata' === $table ? ' f' : '');

        $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

        if (\is_array($this->orderBy) && (isset($this->orderBy[0]) && \strlen($this->orderBy[0]))) {
            $orderBy = $this->orderBy;
            $firstOrderBy = $this->firstOrderBy;
        }

        if (\Input::get('table') && $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && \Database::getInstance()->fieldExists('pid', $this->strTable)) {
            $this->procedure[] = 'pid=?';
            $this->values[] = \Input::get('id');
        }

        $query = 'SELECT * '.(!empty($this->arrSqlDetails) ? ', '.implode(',', array_values($this->arrSqlDetails)) : '').' FROM '.$this->strTable.$table_alias;

        $sqlWhere = '';

        // Set search value from session
        // PBD invalid in php8 $strSessionKey = (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;
        $strSessionKey = ((4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ) ? $this->strFormKey : $this->strTable;
        if (isset($session['search'][$strSessionKey]['value']) && \strlen($session['search'][$strSessionKey]['value'])) {
            $sqlSearchField = $session['search'][$strSessionKey]['field'];
            if (\in_array($sqlSearchField, $this->arrDetailFields, true)) {
                $sqlSearchField = '(SELECT value FROM tl_formdata_details WHERE ff_name=\''.$session['search'][$strSessionKey]['field'].'\' AND pid=f.id)';
            }
            $this->procedure[] = 'CAST('.$sqlSearchField.' AS CHAR) REGEXP ?';
            $this->values[] = $session['search'][$strSessionKey]['value'];
        }

        // Set filter from session
        $arrFilterFields = [];
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k => $v) {
            if ($v['filter']) {
                $arrFilterFields[] = $k;
            }
        }
        if (!empty($arrFilterFields)) {
            foreach ($arrFilterFields as $field) {
                if (isset($session['filter'][$strSessionKey][$field])) {
                    $sqlFilterField = $field;
                    if (\in_array($field, $this->arrDetailFields, true)) {
                        $sqlFilterField = '(SELECT value FROM tl_formdata_details WHERE ff_name=\''.$field.'\' AND pid=f.id)';
                    }

                    // Sort by day
                    if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [5, 6], true)) {
                        if ('' === $session['filter'][$strSessionKey][$field]) {
                            $this->procedure[] = $sqlFilterField."=''";
                        } else {
                            $objDate = new \Date($session['filter'][$strSessionKey][$field]);
                            $this->procedure[] = $sqlFilterField.' BETWEEN ? AND ?';
                            $this->values[] = $objDate->dayBegin;
                            $this->values[] = $objDate->dayEnd;
                        }
                    }

                    // Sort by month
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [7, 8], true)) {
                        if ('' === $session['filter'][$strSessionKey][$field]) {
                            $this->procedure[] = $sqlFilterField."=''";
                        } else {
                            $objDate = new \Date($session['filter'][$strSessionKey][$field]);
                            $this->procedure[] = $sqlFilterField.' BETWEEN ? AND ?';
                            $this->values[] = $objDate->monthBegin;
                            $this->values[] = $objDate->monthEnd;
                        }
                    }

                    // Sort by year
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [9, 10], true)) {
                        if ('' === $session['filter'][$strSessionKey][$field]) {
                            $this->procedure[] = $sqlFilterField."=''";
                        } else {
                            $objDate = new \Date($session['filter'][$strSessionKey][$field]);
                            $this->procedure[] = $sqlFilterField.' BETWEEN ? AND ?';
                            $this->values[] = $objDate->yearBegin;
                            $this->values[] = $objDate->yearEnd;
                        }
                    }

                    // Manual filter
                    elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple']) {
                        $this->procedure[] = $sqlFilterField.' LIKE ?';
                        $this->values[] = '%"'.$session['filter'][$strSessionKey][$field].'"%';
                    }

                    // Other sort algorithm
                    else {
                        $this->procedure[] = $sqlFilterField.'=?';
                        $this->values[] = $session['filter'][$strSessionKey][$field];
                    }
                }
            }
        }

        if (!empty($this->procedure)) {
            $arrProcedure = $this->procedure;

            foreach ($arrProcedure as $kProc => $vProc) {
                $strProcField = substr($vProc, 0, strpos($vProc, '='));
                if (\in_array($strProcField, $this->arrDetailFields, true)) {
                    $arrProcedure[$kProc] = "(SELECT value FROM tl_formdata_details WHERE ff_name='".$strProcField."' AND pid=f.id)=?";
                }
            }
            $sqlWhere .= ('' !== $sqlWhere ? ' AND ' : ' WHERE ').implode(' AND ', $arrProcedure);
        }

        if ('' !== $sqlWhere) {
            $query .= $sqlWhere;
        }

        if (\is_array($orderBy) && isset($orderBy[0]) && \strlen($orderBy[0])) {
            foreach ($orderBy as $o => $strVal) {
                $arrOrderField = explode(' ', $strVal);
                $strOrderField = $arrOrderField[0];
                unset($arrOrderField);
                if (!\in_array($strOrderField, $this->arrBaseFields, true)) {
                    $orderBy[$o] = "(SELECT value FROM tl_formdata_details WHERE ff_name='".$strOrderField."' AND pid=f.id)";
                }
            }
            $query .= ' ORDER BY '.implode(', ', $orderBy);
        }
        if (1 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) === 0) {
            $query .= ' DESC';
        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "query $query");

        $objRowStmt = \Database::getInstance()->prepare($query);
        $objRow = $objRowStmt->execute($this->values);

        $intRowCounter = -1;

        $strExpEncl = '';
        $strExpSep = ';';

        $useFormValues = $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['useFormValues'];
        $useFieldNames = $this->Formdata->arrStoringForms[substr($this->strFormKey, 3)]['useFieldNames'];

        if ('csv' === $strMode) {
            header('Content-Type: appplication/csv; charset='.($this->blnExportUTF8Decode ? $this->strExportConvertToCharset : 'utf-8'));
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="export_'.$this->strFormKey.'_'.date('Ymd_His').'.csv"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Expires: 0');
        } elseif ('xls' === $strMode) {
            if (!$blnCustomXlsExport) {
                $xls = new \xlsexport();
                $strXlsSheet = 'Export';
                $xls->addworksheet($strXlsSheet);
            }
        }

        // List records
        if ($objRow->numRows) {
            $result = $objRow->fetchAllAssoc();

            // Rename each pid to its label and resort the result (sort by parent table)
            if (3 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] && \Database::getInstance()->fieldExists('pid', $this->strTable)) {
                $firstOrderBy = 'pid';

                foreach ($result as $k => $v) {
                    $objField = \Database::getInstance()->prepare('SELECT '.$showFields[0].' FROM '.$this->ptable.' WHERE id=?')
                        ->limit(1)
                        ->execute($v['pid'])
                    ;
                    $result[$k]['pid'] = $objField->$showFields[0];
                }

                $aux = [];
                foreach ($result as $row) {
                    $aux[] = $row['pid'];
                }
                array_multisort($aux, SORT_ASC, $result);
            }

            // Process result and format values
            foreach ($result as $row) {
                ++$intRowCounter;

                $args = [];
                $this->current[] = $row['id'];

                if (0 === $intRowCounter) {
                    if ('xls' === $strMode) {
                        if (!$blnCustomXlsExport) {
                            $xls->totalcol = \count($showFields);
                        }
                    }

                    $strExpEncl = '"';
                    $strExpSep = '';

                    $intColCounter = -1;
                    foreach ($showFields as $k => $v) {
                        if (\in_array($v, $ignoreFields, true)) {
                            continue;
                        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "showFields[$k]: $v");

                        ++$intColCounter;

                        if ($useFieldNames) {
                            $strName = $v;
                        } elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0]) && \strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0])) {
                            $strName = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0];
                        } elseif (isset($GLOBALS['TL_LANG']['tl_formdata'][$v][0]) && \strlen($GLOBALS['TL_LANG']['tl_formdata'][$v][0])) {
                            $strName = $GLOBALS['TL_LANG']['tl_formdata'][$v][0];
                        } else {
                            $strName = strtoupper($v);
                        }

                        if (\strlen($strName)) {
                            $strName = \StringUtil::decodeEntities($strName);
                        }

                        if ($this->blnExportUTF8Decode || ('xls' === $strMode && !$blnCustomXlsExport)) {
                            $strName = $this->convertEncoding($strName, $GLOBALS['TL_CONFIG']['characterSet'], $this->strExportConvertToCharset);
                        }

                        if ('csv' === $strMode) {
                            $strName = str_replace('"', '""', $strName);
                            echo $strExpSep.$strExpEncl.$strName.$strExpEncl;
                            $strExpSep = ';';
                        } elseif ('xls' === $strMode) {
                            if (!$blnCustomXlsExport) {
                                $xls->setcell(['sheetname' => $strXlsSheet, 'row' => $intRowCounter, 'col' => $intColCounter, 'data' => $strName, 'fontweight' => XLSFONT_BOLD, 'vallign' => XLSXF_VALLIGN_TOP, 'fontfamily' => XLSFONT_FAMILY_NORMAL]);
                                $xls->setcolwidth($strXlsSheet, $intColCounter, 0x1aff);
                            } else {
                                $arrHookDataColumns[$v] = $strName;
                            }
                        } elseif ($blnCustomExport) {
                            $arrHookDataColumns[$v] = $strName;
                        }
                    }

                    ++$intRowCounter;

                    if ('csv' === $strMode) {
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
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "row[$k]: $v");
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "inputType ".$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']);

                    if ('date' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        && \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], [5, 6, 7, 8, 9, 10], true)) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '');
                    } elseif ('datim' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        && \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], [5, 6, 7, 8, 9, 10], true)) {
                        $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '');
                    } elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], [5, 6, 7, 8, 9, 10], true)) {
                        if (null !== [$GLOBALS['TL_CONFIG']['datimFormat']]) {
                            $strVal = ($row[$v] ? date($GLOBALS['TL_CONFIG']['datimFormat'], (int) $row[$v]) : '');
                        } else {
                            $strval = '';
                        }
                    } elseif ('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && \count($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) > 0) {
                            if ('' !== $row[$v]) {
                                if (!$useFormValues) {
                                    $strVal = $row[$v];
                                } else {
                                    $arrCbOpt = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'];
                                    $arrCbOpt = array_flip($arrCbOpt);
                                    $strVal = $arrCbOpt[$row[$v]];
                                }
                            } else {
                                $strVal = '';
                            }
                        } else {
                            $strVal = ('' !== $row[$v]) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                        }
                    } elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'],
                        ['radio', 'efgLookupRadio', 'select', 'conditionalselect', 'efgLookupSelect', 'checkbox', 'efgLookupCheckbox'], true)) {
                        $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'] : '|';

                        // take the assigned value instead of the user readable output
                        if (1 === $useFormValues) {
                            if ((false === strpos($row[$v], $strSep))
                                && (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && \count($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) > 0)) {
                                // handle grouped options
                                $arrOptions = [];
                                foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'] as $o => $mxVal) {
                                    if (!\is_array($mxVal)) {
                                        $arrOptions[$o] = $mxVal;
                                    } else {
                                        foreach ($mxVal as $ov => $mxOVal) {
                                            $arrOptions[$ov] = $mxOVal;
                                        }
                                    }
                                }

                                $options = array_flip($arrOptions);
                                $strVal = $options[$row[$v]];
                            } else {
                                if ((\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) && \count($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options']) > 0)) {
                                    // handle grouped options
                                    $arrOptions = [];
                                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'] as $o => $mxVal) {
                                        if ((!\is_array($mxVal))) {
                                            $arrOptions[$o] = $mxVal;
                                        } else {
                                            foreach ($mxVal as $ov => $mxOVal) {
                                                $arrOptions[$ov] = $mxOVal;
                                            }
                                        }
                                    }

                                    $options = array_flip($arrOptions);

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
                            $strVal = \strlen((string)$row[$v]) ? str_replace($strSep, ",\n", (string)$row[$v]) : ''; //PBD
                        }
                    } else {
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "v $v row ".$row[$v]);

                        $row_v = deserialize($row[$v]);
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "row_v $row_v");
                        if (!isset($row_v))$row[$v]="";             //PBD                         

                        if (!empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'])) {
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "csv ".$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv']);
                            $row_v = explode($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'], $row[$v]);
                        }

                        if (\is_array($row_v)) {
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "row_v is array");
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
                        $strVal = isset($args[$k]) ? (string)$args[$k] : vsprintf('%s', $args[$k]);        //PBD
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "strVal $strVal");
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

                    if (isset($strVal) && \strlen($strVal)) {
                        $strVal = \StringUtil::decodeEntities($strVal);
                        $strVal = preg_replace(['/<br.*\/*>/si'], ["\n"], $strVal);

                        if ($this->blnExportUTF8Decode || ('xls' === $strMode && !$blnCustomXlsExport)) {
                            $strVal = $this->convertEncoding($strVal, $GLOBALS['TL_CONFIG']['characterSet'], $this->strExportConvertToCharset);
                        }
                    }

                    if ('csv' === $strMode) {
                        $strVal = str_replace('"', '""', $strVal);
                        echo $strExpSep.$strExpEncl.$strVal.$strExpEncl;

                        $strExpSep = ';';
                    } elseif ('xls' === $strMode) {
                        if (!$blnCustomXlsExport) {
                            $xls->setcell(['sheetname' => $strXlsSheet, 'row' => $intRowCounter, 'col' => $intColCounter, 'data' => $strVal, 'vallign' => XLSXF_VALLIGN_TOP, 'fontfamily' => XLSFONT_FAMILY_NORMAL]);
                        } else {
                            $arrHookData[$intRowCounter][$v] = $strVal;
                        }
                    } elseif ($blnCustomExport) {
                        $arrHookData[$intRowCounter][$v] = $strVal;
                    }
                }

                if ('csv' === $strMode) {
                    $strExpSep = '';
                    echo "\n";
                }
            }
        }

        if ('xls' === $strMode) {
            if (!$blnCustomXlsExport) {
                $xls->sendfile('export_'.$this->strFormKey.'_'.date('Ymd_His').'.xls');
                exit;
            }

            foreach ($GLOBALS['TL_HOOKS']['efgExportXls'] as $key => $callback) {
                $this->import($callback[0]);
                $res = $this->{$callback[0]}->{$callback[1]}($arrHookDataColumns, $arrHookData);             //Änderung PBD
            }
        } elseif ($blnCustomExport) {
            foreach ($GLOBALS['TL_HOOKS']['efgExport'] as $key => $callback) {
                $this->import($callback[0]);
                $res = $this->{$callback[0]}->{$callback[1]}($arrHookDataColumns, $arrHookData, $strMode);
            }
        }
        exit;
    }

    public function exportxls(): void
    {
        $this->export('xls');
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
        if ($from === $to) {
            return $strString;
        }
//define("USE_MBSTRING", "!");
        if (defined("USE_MBSTRING")) {        
            //if (USE_MBSTRING) {
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
     * Save the current value.
     *
     * @param mixed
     *
     * @throws \Exception
     */
    protected function save($varValue): void
    {
        if (\Input::post('FORM_SUBMIT') !== $this->strTable) {
            return;
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "varValue $varValue");

        $arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField];

        // table to write to: tl_formdata (base fields) or tl_formdata_details (detail fields)
        $strTargetTable = $this->strTable;
        $strTargetField = $this->strField;
        $blnDetailField = false;

        // if field is one of detail fields
        if (\in_array($strTargetField, $this->arrDetailFields, true)) {
            $strTargetTable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'][0];
            $blnDetailField = true;
        }

        // Convert date formats into timestamps
        if ('' !== $varValue && \in_array($arrField['eval']['rgxp'], ['date', 'time', 'datim'], true)) {
            $objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrField['eval']['rgxp'].'Format']);
            $varValue = $objDate->tstamp;
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "timestamp varValue $varValue");
        }

        if (!\in_array($this->strField, $this->arrOwnerFields, true) && !\in_array($this->strField, $this->arrBaseFields, true)) {
            // Convert checkbox, radio, select, conditionalselect to store the values instead of keys
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'Abfrage true');
            if (('checkbox' === $arrField['inputType'] && $arrField['eval']['multiple'])
                || \in_array($arrField['inputType'], ['radio', 'select', 'conditionalselect'], true)) {
                EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'checkbox ....');
                $arrOpts = $arrField['options'];
                // OptGroups can not be saved so flatten grouped options array
                $arrNewOpts = [];

                foreach ($arrOpts as $strKey => $varOpt) {
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "arrOpts[$strKey]: $varOpt");
                    if (\is_array($varOpt) && !empty($varOpt)) {
                        foreach ($varOpt as $keyOpt => $valOpt) {
                            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "varOpt[$keyOpt]: $valOpt");
                            $arrNewOpts[$keyOpt] = $valOpt;
                        }
                    } else {
                        $arrNewOpts[$strKey] = $varOpt;
                    }
                }
                $arrOpts = $arrNewOpts;
                unset($arrNewOpts);
                EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'arroptss neu gesetzt');

                $arrSel = deserialize($varValue, true);
                if (\is_array($arrSel) && !empty($arrSel)) {
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'arrSel is array');
                    foreach ($arrSel as $k => $v) {
                        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "arrSel[$k]: $v");
                    }
                    $arrSel = array_flip($arrSel);             // vertauscht key and Value
                    foreach ($arrSel as $k => $v) {
                        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "flip arrSel[$k]: $v");
                    }
                    // use options value or options labels
                    foreach (array_intersect_key($arrOpts, $arrSel) as $k => $v) {
                        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "intersect[$k]: $v");
                    }
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'efgStoreValues "'.$arrField['eval']['efgStoreValues'].'"');
                    if ($arrField['eval']['efgStoreValues']) {
                        $arrVals = array_keys(array_intersect_key($arrOpts, $arrSel));
                        //$arrVals = array_values(array_intersect_key($arrOpts, $arrSel));
                        foreach ($arrVals as $k => $v) {
                            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "Keys arrVals[$k]: $v");
                        }
                    } else {
                        $arrVals = array_values(array_intersect_key($arrOpts, $arrSel));    // PBD das ist doh eigentlich anders
                        //$arrVals = array_keys(array_intersect_key($arrOpts, $arrSel));
                        foreach ($arrVals as $k => $v) {
                            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "Values arrVals[$k]: $v");
                        }
                    }
                }

                if (\is_array($arrVals) && !$arrField['eval']['multiple']) {
                    $varValue = $arrVals[0];
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'varValue neu '.$varValue);
                } else {
                    $varValue = (\is_array($arrVals) && !empty($arrVals)) ? $arrVals : '';
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'varValue neu '.$varValue);
                }
            }

            if ('checkbox' === $arrField['inputType'] && !$arrField['eval']['multiple']) {
                EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'is a checkbox '.$varValue);
                if (\is_array($arrField['options'])) {
                    $arrVals = ($arrField['eval']['efgStoreValues'] ? array_keys($arrField['options']) : array_values($arrField['options']));
                } else {
                    $arrVals = ['1'];
                }

                if (\strlen($varValue)) {
                    $varValue = $arrVals[0];
                } else {
                    $varValue = '';
                }
            }
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'vor convertFiletree ');

        // Convert fileTree IDs or UUIDs to file paths
        if ('fileTree' === $arrField['inputType']) {
            $varValue = deserialize($varValue);

            if (\is_array($varValue) && !empty($varValue)) {
                foreach ($varValue as $key => $varFile) {
                    if (\Validator::isUuid($varFile) || is_numeric($varFile)) {
                        $objFileModel = \FilesModel::findById($varFile);

                        if (null !== $objFileModel) {
                            $varValue[$key] = $objFileModel->path;
                        }
                    }
                }

                $varValue = array_values($varValue);
            } elseif (!empty($varValue)) {
                if (\Validator::isUuid($varValue) || is_numeric($varValue)) {
                    $objFileModel = \FilesModel::findById($varValue);

                    if (null !== $objFileModel) {
                        $varValue = $objFileModel->path;
                    }
                }
            }
        }

        if ('cm_alternative' === $arrField['inputType']) {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'cm_alternative ');
            if (\is_array($arrField['options']) && isset($arrField['options'][$varValue])) {
                $varValue = $arrField['options'][$varValue];
            }
        }

        // Make sure unique fields are unique
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "unique varValue $varValue");

        if (!\is_array($varValue) && (isset($varValue) && \strlen((string) $varValue)) && $arrField['eval']['unique']) {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'query '.'SELECT * FROM '.$this->strTable.' WHERE '.$this->strField.'='.$varValue.' AND id!='.$this->intId);
            $objUnique = \Database::getInstance()->prepare('SELECT * FROM '.$this->strTable.' WHERE '.$this->strField.'=? AND id!=?')
                ->execute($varValue, $this->intId)
            ;

            if ($objUnique->numRows) {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], (\strlen($arrField['label'][0]) ? $arrField['label'][0] : $this->strField)));
            }
        }

        if (\is_array($varValue)) {
            $varValue = serialize($varValue);
        }

        // Convert arrays (see #2890)
        if ($arrField['eval']['multiple'] && isset($arrField['eval']['csv'])) {
            $varValue = implode($arrField['eval']['csv'], deserialize($varValue, true));
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'vor trigger save callback ');

        // Trigger the save_callback
        if (\is_array($arrField['save_callback'])) {
            foreach ($arrField['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);    //Änderung PBD
                } elseif (\is_callable($callback)) {
                    $varValue = $callback($varValue, $this);
                }
            }
        }

        // Save the value if there was no error
        if (('' !== $varValue || !$arrField['eval']['doNotSaveEmpty']) && ($this->varValue !== $varValue || $arrField['eval']['alwaysSave'])) {
            // Set the correct empty value (see #6284, #6373)
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'vor save the value ');
            if ('' === $varValue) {
                $varValue = \Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strTargetField]['sql']);
            }

            $arrValues = $this->values;
            $arrProcedures = $this->procedure;

            if ($blnDetailField) {
                // add condition ff_name
                $arrProcedures[] = 'ff_name=?';
                $arrValues[] = $strTargetField;

                foreach ($arrProcedures as $kP => $kV) {
                    if ('id=?' === $kV) {
                        $arrProcedures[$kP] = 'pid=?';
                        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'arrProcedures['.$kp.']');
                    } elseif ('form=?' === $kV) {
                        $arrProcedures[$kP] = 'ff_name=?';
                        $arrValues[$kP] = $strTargetField;
                        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'arrProcedures['.$kp.']:'. $arrValues[$kP]);
                    }
                }
            }
            array_unshift($arrValues, $varValue);

            $sqlUpd = 'UPDATE '.$strTargetTable.' SET '.$strTargetField.'=? WHERE '.implode(' AND ', $arrProcedures);
            //EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, '1. sqlUpd '.$sqlUpd);
            if ($blnDetailField) {
                // if record does not exist insert an empty record
                $objExist = \Database::getInstance()->prepare('SELECT id FROM tl_formdata_details WHERE pid=? AND ff_name=?')
                    ->execute([$this->intId, $strTargetField])
                ;

                if (0 === $objExist->numRows) {
                    $arrSetInsert = [
                        'pid' => $this->intId,
                        'tstamp' => time(),
                        'ff_id' => $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strTargetField]['ff_id'],
                        'ff_name' => $strTargetField,
                    ];
                    $objInsertStmt = \Database::getInstance()->prepare('INSERT INTO '.$strTargetTable.' %s')
                        ->set($arrSetInsert)
                        ->execute()
                    ;
                }

                $sqlUpd = 'UPDATE '.$strTargetTable.' SET value=? WHERE '.implode(' AND ', $arrProcedures);
            }
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, '2. sqlUpd '.$sqlUpd);
            foreach ($arrValues as $k=>$v) {EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "arrValues[$k]:$v");}
            $objUpdateStmt = \Database::getInstance()->prepare($sqlUpd)
                ->execute($arrValues)
            ;

            if ($objUpdateStmt->affectedRows) {
                if ($varValue !== $this->varValue) {
                    if (!$arrField['eval']['submitOnChange']) {
                        $this->blnCreateNewVersion = true;
                    }
                }

                $this->varValue = deserialize($varValue);

                if (\is_object($this->objActiveRecord)) {
                    $this->objActiveRecord->{$this->strField} = $this->varValue;
                }
            }
        }
    }

    /**
     * Delete all incomplete and unrelated records.
     */
    protected function reviseTable(): void
    {
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'revisetabel '.$this->strTable);
        $reload = false;
        if (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'])) $ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'];
        else  $ptable=null;
        $ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];
        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

        // Fix warnings and search panel in formdata driver #4 Fritz Michael Gschwantner
        /*
                $new_records = $objSessionBag->get('new_records');
                EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'in session new_records this->strTable '.$this->strTable.' len new_records '.\count($new_records[$this->strTable]));
        */
        $new_records = $objSessionBag->get('new_records') ?: [];

        if (isset($new_records[$this->strTable])) {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'in session new_records this->strTable '.$this->strTable.' len new_records '.\count($new_records[$this->strTable]));
        } else {
            $new_records[$this->strTable]=null;                  // PBD
        }
        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['reviseTable']) && \is_array($GLOBALS['TL_HOOKS']['reviseTable'])) {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'hook da ');
            foreach ($GLOBALS['TL_HOOKS']['reviseTable'] as $callback) {
                $status = null;

                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $status = $this->{$callback[0]}->{$callback[1]}
                    ($this->strTable, 
                    $new_records[$this->strTable], 
                    $ptable, 
                    $ctable);      //Änderung PBD
                } elseif (\is_callable($callback)) {
                    $status = $callback($this->strTable, $new_records[$this->strTable], $ptable, $ctable);
                }

                if (true === $status) {
                    $reload = true;
                }
            }
        }

        // Delete all new but incomplete records (tstamp=0)
        if (!empty($new_records[$this->strTable]) && \is_array($new_records[$this->strTable])) {
            $intPreserved = null;

            // Unset the preserved record (see #1129)
            if ($this->intPreserveRecord && ($index = array_search($this->intPreserveRecord, $new_records[$this->strTable], true)) !== false) {
                $intPreserved = $new_records[$this->strTable][$index];
                unset($new_records[$this->strTable][$index]);
            }

            // Remove the entries from the database
            if (!empty($new_records[$this->strTable])) {
                $origId = $this->id;
                $origActiveRecord = $this->activeRecord;
                $ids = array_map('\intval', $new_records[$this->strTable]);

                foreach ($ids as $id) {
                    // Get the current record
                    $objRow = $this->Database->prepare('SELECT * FROM '.$this->strTable.' WHERE id=?')
                                             ->limit(1)
                                             ->execute($id)
                    ;

                    $this->id = $id;
                    $this->activeRecord = $objRow;

                    // Invalidate cache tags (no need to invalidate the parent)
                    $this->invalidateCacheTags();
                }

                $this->id = $origId;
                $this->activeRecord = $origActiveRecord;
                EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'DELETE FROM '.$this->strTable.' WHERE id IN('.implode(',', $ids).') AND tstamp=0');

                $objStmt = $this->Database->execute('DELETE FROM '.$this->strTable.' WHERE id IN('.implode(',', $ids).') AND tstamp=0');

                if ($objStmt->affectedRows > 0) {
                    $reload = true;
                }
            }

            // Remove the entries from the session
            if (null !== $intPreserved) {
                $new_records[$this->strTable] = [$intPreserved];
            } else {
                unset($new_records[$this->strTable]);
            }

            $objSessionBag->set('new_records', $new_records);
        }

        // Delete all records of the current table that are not related to the parent table
        if ($ptable) {
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable']) {
                $objIds = $this->Database->execute('SELECT c.id FROM '.$this->strTable.' c LEFT JOIN '.$ptable." p ON c.pid=p.id WHERE c.ptable='".$ptable."' AND p.id IS NULL");
            } else {
                $objIds = $this->Database->execute('SELECT c.id FROM '.$this->strTable.' c LEFT JOIN '.$ptable.' p ON c.pid=p.id WHERE p.id IS NULL');
            }

            if ($objIds->numRows) {
                $objStmt = $this->Database->execute('DELETE FROM '.$this->strTable.' WHERE id IN('.implode(',', array_map('\intval', $objIds->fetchEach('id'))).')');

                if ($objStmt->affectedRows > 0) {
                    $reload = true;
                }
            }
        }

        // Delete all records of the child table that are not related to the current table
        if (!empty($ctable) && \is_array($ctable)) {
            foreach ($ctable as $v) {
                if ($v) {
                    // Load the DCA configuration so we can check for "dynamicPtable"
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "loadDataContainer  $v");

                    $this->loadDataContainer($v);
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "geladen loadDataContainer  $v");

                    if (isset($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable']) && $GLOBALS['TL_DCA'][$v]['config']['dynamicPtable']) {
                        $objIds = $this->Database->execute('SELECT c.id FROM '.$v.' c LEFT JOIN '.$this->strTable." p ON c.pid=p.id WHERE c.ptable='".$this->strTable."' AND p.id IS NULL");
                    } else {
                        $objIds = $this->Database->execute('SELECT c.id FROM '.$v.' c LEFT JOIN '.$this->strTable.' p ON c.pid=p.id WHERE p.id IS NULL');
                    }

                    if ($objIds->numRows) {
                        $objStmt = $this->Database->execute('DELETE FROM '.$v.' WHERE id IN('.implode(',', array_map('\intval', $objIds->fetchEach('id'))).')');

                        if ($objStmt->affectedRows > 0) {
                            $reload = true;
                        }
                    }
                }
            }
        }

        // Reload the page
        if ($reload) {
            \Controller::reload();
        }
    }

    /**
     * List all records of the current table and return them as HTML string.
     *
     * @return string
     */
    protected function listView()
    {
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'strTable '.$this->strTable);

        $return = '';
        $table = (6 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->ptable : $this->strTable;
        $table_alias = ('tl_formdata' === $table ? ' f' : '');
        $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

        if (\is_array($this->orderBy) && '' !== $this->orderBy[0]) {
            $orderBy = $this->orderBy;
            $firstOrderBy = $this->firstOrderBy;
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'table: '.\Input::get('table').' id: '.\Input::get('id'));

        if (\Input::get('table') && $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && \Database::getInstance()->fieldExists('pid', $this->strTable)) {
            $this->procedure[] = 'pid=?';
            $this->values[] = \Input::get('id');
        }

        $query = 'SELECT * '.(!empty($this->arrSqlDetails) ? ', '.implode(',', array_values($this->arrSqlDetails)) : '').' FROM '.$this->strTable.$table_alias;

        $sqlWhere = '';

        if (!empty($this->procedure)) {
            $arrProcedure = $this->procedure;

            foreach ($arrProcedure as $kProc => $vProc) {
                $arrParts = preg_split('/[\s=><\!]/si', $vProc);
                $strProcField = $arrParts[0];
                if (\in_array($strProcField, $this->arrDetailFields, true)) {
                    $arrProcedure[$kProc] = "(SELECT value FROM tl_formdata_details WHERE ff_name='".$strProcField."' AND pid=f.id)=?";
                }
            }
            $sqlWhere = ' WHERE '.implode(' AND ', $arrProcedure);
        }

        if ('' !== $sqlWhere) {
            $query .= $sqlWhere;
        }

        if (\is_array($orderBy) && (isset($orderBy[0]) && '' !== $orderBy[0])) {
            foreach ($orderBy as $o => $strVal) {
                $arrOrderField = explode(' ', $strVal);
                $strOrderField = $arrOrderField[0];
                unset($arrOrderField);
                if (!\in_array($strOrderField, $this->arrBaseFields, true)) {
                    $orderBy[$o] = "(SELECT value FROM tl_formdata_details WHERE ff_name='".$strOrderField."' AND pid=f.id)";
                }
            }

            $query .= ' ORDER BY '.implode(', ', $orderBy);
        }

        if (1 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) === 0) {
            $query .= ' DESC';
        }

        $objRowStmt = \Database::getInstance()->prepare($query);

        if ( !empty($this->limit)&& !isset($this->limit)&&'' !== trim($this->limit)) {
            $arrLimit = explode(',', $this->limit);
            $objRowStmt->limit($arrLimit[1], $arrLimit[0]);
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "query $query values '".implode(', ', $this->values)."'");

        $objRow = $objRowStmt->execute($this->values);
        $this->bid = ('' !== $return) ? $this->bid : 'tl_buttons';
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'closed '.' global_operations '.$GLOBALS['TL_DCA'][$this->strTable]['list']['global_operations']);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "Display !closed '".!($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'])."'");

        // Display buttons
        if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] || !empty($GLOBALS['TL_DCA'][$this->strTable]['list']['global_operations'])) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "Display buttons act '".\Input::get('act')."'");
            $return .= '

<div id="'.$this->bid.'">'.(('select' === \Input::get('act') || $this->ptable) ? '
<a href="'.$this->getReferer(true, $this->ptable).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="contao/main.php?'.$GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'].'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a> ' : '')).(('select' !== \Input::get('act')) ? '
'.(!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '<a href="'.(('' !== $this->ptable) ? \Backend::addToUrl('act=create'.(($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '').'&amp;pid='.$this->intId) : \Backend::addToUrl('act=create')).'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a> ' : '').$this->generateGlobalButtons() : '').'
</div>'.\Message::generate(true);
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'Display buttons numRows'.$objRow->numRows);

        // Return "no records found" message
        if ($objRow->numRows < 1) {
            $return .= '
<p class="tl_empty">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
        }

        // List records
        else {
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'list records');
            $result = $objRow->fetchAllAssoc();
            $return .= (('select' === \Input::get('act')) ? '

<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">' : '').'

<div class="tl_listing_container list_view">'.(('select' === \Input::get('act')) ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '').'

<table class="tl_listing'.($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ? ' showColumns' : '').'">';

            // Automatically add the "order by" field as last column if we do not have group headers
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] && !\in_array($firstOrderBy, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'], true)) {
                $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][] = $firstOrderBy;
            }

            // Rename each pid to its label and resort the result (sort by parent table)
            if (3 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) {
                $firstOrderBy = 'pid';
                $showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

                foreach ($result as $k => $v) {
                    $objField = \Database::getInstance()->prepare('SELECT '.$showFields[0].' FROM '.$this->ptable.' WHERE id=?')
                        ->limit(1)
                        ->execute($v['pid'])
                    ;

                    $result[$k]['pid'] = $objField->$showFields[0];
                }

                $aux = [];

                foreach ($result as $row) {
                    $aux[] = $row['pid'];
                }

                array_multisort($aux, SORT_ASC, $result);
            }

            // Generate the table header if the "show columns" option is active
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                $return .= '
  <tr>';

                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f) {
                    $return .= '
    <th class="tl_folder_tlist col_'.$f.(($f === $firstOrderBy) ? ' ordered_by' : '').'">'.$GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0].'</th>';
                }

                $return .= '
    <th class="tl_folder_tlist tl_right_nowrap">&nbsp;</th>
  </tr>';
            }

            // Process result and add label and buttons
            $remoteCur = false;
            $groupclass = 'tl_folder_tlist';
            $eoCount = -1;
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'list records len result '.count($result));      # PBD leycom

            foreach ($result as $row) {
                $rowFormatted = [];
                $args = [];
                $this->current[] = $row['id'];
                $showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

                // Label
                foreach ($showFields as $k => $v) {
                    if (\in_array($v, $this->arrDetailFields, true)
                        && \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'], ['radio', 'efgLookupRadio', 'select', 'efgLookupSelect', 'checkbox', 'efgLookupCheckbox', 'efgImageSelect', 'fileTree'], true)) {
                        $strSep = (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'])) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'] : '|';
                        $row[$v] = str_replace($strSep, ', ', (string) $row[$v]);     # PBD leycom
                    }

                    if (\in_array($v, $this->arrDetailFields, true) && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                        $strSep = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['csv'] ?? '|';
                        $row[$v] = str_replace($strSep, ', ', $row[$v]);
                    }

                    if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], [5, 6, 7, 8, 9, 10], true)) {
                        if ('date' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                            $args[$k] = !empty($row[$v]) ? \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '-';
                            $rowFormatted[$v] = $args[$k];
                        } elseif ('time' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp']) {
                            $args[$k] = !empty($row[$v]) ? \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $row[$v]) : '-';
                            $rowFormatted[$v] = $args[$k];
                        } else {
                            $args[$k] = !empty($row[$v]) ? \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '-';
                            $rowFormatted[$v] = $args[$k];
                        }
                    } elseif ('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType']
                        && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                        if (\in_array($v, $this->arrDetailFields, true)) {
                            $args[$k] = !empty($row[$v]) ? $row[$v] : '-';
                            $rowFormatted[$v] = $args[$k];
                        } else {
                            $args[$k] = !empty($row[$v]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : '-';
                            $rowFormatted[$v] = $args[$k];
                        }
                    } elseif (\in_array($v, $this->arrBaseFields, true) && \in_array($v, $this->arrOwnerFields, true)) {
                        if ('fd_member' === $v) {
                            $args[$k] = $this->arrMembers[$row[$v]];
                            $rowFormatted[$v] = $args[$k];
                        } elseif ('fd_user' === $v) {
                            $args[$k] = $this->arrUsers[$row[$v]];
                            $rowFormatted[$v] = $args[$k];
                        } elseif ('fd_member_group' === $v) {
                            $args[$k] = $this->arrMemberGroups[$row[$v]];
                            $rowFormatted[$v] = $args[$k];
                        } elseif ('fd_user_group' === $v) {
                            $args[$k] = $this->arrUserGroups[$row[$v]];
                            $rowFormatted[$v] = $args[$k];
                        }
                    } else {
                        $row_v = deserialize($row[$v]);

                        if (\is_array($row_v)) {
                            $args_k = [];

                            foreach ($row_v as $option) {
                                if (isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option])) {
                                    $args_k[] = \strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option] : $option;
                                }
                            }

                            $args[$k] = implode(', ', $args_k);
                            $rowFormatted[$v] = $args[$k];
                        } elseif (isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]])) {
                            $args[$k] = \is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
                        } elseif (($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'])) && isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]])) {
                            $args[$k] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]];
                        } else {
                            // check multiline value
                            if (isset($row[$v]) && !\is_bool(strpos($row[$v], "\n"))) {
                                $row[$v] = $this->Formdata->formatMultilineValue($row[$v]);
                            }
                            $args[$k] = $row[$v];
                            $rowFormatted[$v] = $args[$k];
                        }
                    }
                }

                // Shorten the label it if it is too long
                $label = vsprintf(((isset($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format']) && \strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'])) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] : '%s'), $args);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'sortlabel '.$label);           # PBD leycom
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < \strlen(strip_tags($label))) {
                    $label = trim(\StringUtil::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])).' …';
                }

                // Build the sorting groups
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] > 0) {
                    $current = $row[$firstOrderBy];
                    $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                    $sortingMode = (1 === \count($orderBy) && $firstOrderBy === $orderBy[0] && '' !== $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] && '' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag']) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];
                    $remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

                    // Add the group header
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] && !$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] && ($remoteNew !== $remoteCur || false === $remoteCur)) {
                        $eoCount = -1;
                        $group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);
                        $remoteCur = $remoteNew;

                        $return .= '
  <tr>
    <td colspan="2" class="'.$groupclass.'">'.$group.'</td>
  </tr>';
                        $groupclass = 'tl_folder_list';
                    }
                }

                $return .= '
  <tr class="'.((0 === ++$eoCount % 2) ? 'even' : 'odd').' click2edit" onmouseover="Theme.hoverRow(this,1)" onmouseout="Theme.hoverRow(this,0)" onclick="Theme.toggleSelect(this)">
    ';

                $colspan = 1;

                // Call the label_callback ($row, $label, $this)
                if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

                        $this->import($strClass);
                        $args = $this->$strClass->$strMethod($row, $label, $this, $args);
                    } elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                        $args = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']($row, $label, $this, $args);
                    }

                    // Handle strings and arrays (backwards compatibility)
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                        $label = \is_array($args) ? implode(' ', $args) : $args;
                    } elseif (!\is_array($args)) {
                        $args = [$args];
                        $colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields']);
                    }
                }

                // Show columns
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                    foreach ($args as $j => $arg) {
                        $return .= '<td colspan="'.$colspan.'" class="tl_file_list col_'.$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j].(($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] === $firstOrderBy) ? ' ordered_by' : '').'">'.($arg ?: '-').'</td>';
                    }
                } else {
                    $return .= '<td class="tl_file_list">'.$label.'</td>';
                }

                // Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
                $return .= (('select' === \Input::get('act')) ? '
    <td class="tl_file_list tl_right_nowrap"><input type="checkbox" name="IDS[]" id="ids_'.$row['id'].'" class="tl_tree_checkbox" value="'.$row['id'].'"></td>' : '
    <td class="tl_file_list tl_right_nowrap">'.$this->generateButtons($row, $this->strTable, $this->root).'</td>').'
  </tr>';
            }

            // Close the table
            $return .= '
</table>

</div>';
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'list records len return string '.strlen($return));      # PBD leycom
            // Close the form
            if ('select' === \Input::get('act')) {
                $callbacks = '';

                // Call the buttons_callback
                if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback) {
                        $this->import($callback[0]);
                        $callbacks .= $this->$callback[0]->$callback[1]($this);
                    }
                }

                $return .= '

<div class="tl_formbody_submit" style="text-align:right">

<div class="tl_submit_container">'.(!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ? '
  <input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\')" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'"> ' : '').(!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'"> ' : '').'
</div>

</div>
</div>
</form>';
            }
        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'return');
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "return HTML $return");

        return $return;
    }

    /**
     * Build the sort panel and return it as string.
     *
     * @return string
     */
    protected function panel()
    {
        if ('' === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout']) {
            return '';
        }

        $intFilterPanel = 0;
        $arrPanels = [];

        foreach (trimsplit(';', $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout']) as $strPanel) {
            $panels = '';
            $arrSubPanels = trimsplit(',', $strPanel);

            foreach ($arrSubPanels as $strSubPanel) {
                $panel = '';

                // Regular panels
                if ('search' === $strSubPanel || 'limit' === $strSubPanel || 'sort' === $strSubPanel) {
                    $panel = $this->{$strSubPanel.'Menu'}();
                }

                // Multiple filter subpanels can be defined to split the fields across panels
                elseif ('filter' === $strSubPanel) {
                    $panel = $this->{$strSubPanel.'Menu'}(++$intFilterPanel);
                }

                // Call the panel_callback
                else {
                    $arrCallback = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panel_callback'][$strSubPanel];

                    if (\is_array($arrCallback)) {
                        $this->import($arrCallback[0]);
                        $panel = $this->{$arrCallback[0]}->{$arrCallback}[1]($this);            //Änderung PBD
                    } elseif (\is_callable($arrCallback)) {
                        $panel = $arrCallback($this);
                    }
                }

                // Add the panel if it is not empty
                if ('' !== $panel) {
                    $panels = $panel.$panels;
                }
            }

            // Add the group if it is not empty
            if ('' !== $panels) {
                $arrPanels[] = $panels;
            }
        }

        if (empty($arrPanels)) {
            return '';
        }

        if ('tl_filters' === \Input::post('FORM_SUBMIT')) {
            \Controller::reload();
        }

        $return = '';
        $intTotal = \count($arrPanels);
        $intLast = $intTotal - 1;

        for ($i = 0; $i < $intTotal; ++$i) {
            $submit = '';

            if ($i === $intLast) {
                $submit = '

<div class="tl_submit_panel tl_subpanel berta">
<input type="image" name="filter" id="filter" src="'.TL_FILES_URL.'system/themes/'.\Backend::getTheme().'/images/reload.gif" class="tl_img_submit" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['applyTitle']).'" alt="'.specialchars($GLOBALS['TL_LANG']['MSC']['apply']).'">
</div>';
            }

            $return .= '
<div class="tl_panel">'.$submit.$arrPanels[$i].'

<div class="clear"></div>

</div>';
        }

        $return = '
<form action="'.ampersand(\Environment::get('request'), true).'" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_filters">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
'.$return.'
</div>
</form>
';

        return $return;
    }

    /**
     * Return a search form that allows to search results using regular expressions.
     *
     * @return string
     */
    protected function searchMenu()
    {
        $searchFields = [];
        $session = $this->Session->getData();

        // Get search fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k => $v) {
            if (isset($v['search'])&&    //PBD
                $v['search']) {
                $searchFields[] = $k;
            }
        }

        // Return if there are no search fields
        if (empty($searchFields)) {
            return '';
        }

        // PBD invalid in php8 $strSessionKey = (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;
        $strSessionKey = ((4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ) ? $this->strFormKey : $this->strTable;

        // Store search value in the current session
        if ('tl_filters' === \Input::post('FORM_SUBMIT')) {
            $session['search'][$strSessionKey]['value'] = '';
            $session['search'][$strSessionKey]['field'] = \Input::post('tl_field', true);

            // Make sure the regular expression is valid
            if ('' !== \Input::postRaw('tl_value')) {
                $sqlSearchField = '(SELECT value FROM tl_formdata_details WHERE ff_name=\''.\Input::post('tl_field', true).'\' AND pid=f.id)';
                try {
                    \Database::getInstance()->prepare('SELECT * '.(!empty($this->arrSqlDetails) ? ','.implode(', ', array_values($this->arrSqlDetails)) : '').' FROM '.$this->strTable.' f WHERE '.$sqlSearchField.' REGEXP ?')
                        ->limit(1)
                        ->execute(\Input::postRaw('tl_value'))
                ;

                    $session['search'][$strSessionKey]['value'] = \Input::postRaw('tl_value');
                } catch (\Exception $e) {
                }
            }

            $this->Session->setData($session);
        }

        // Set search value from session
        elseif (isset($session['search'][$strSessionKey]['value']) && ('' !== $session['search'][$strSessionKey]['value'])) {
            $sqlSearchField = $session['search'][$strSessionKey]['field'];
            if (\in_array($sqlSearchField, $this->arrDetailFields, true)) {
                $sqlSearchField = '(SELECT value FROM tl_formdata_details WHERE ff_name=\''.$session['search'][$strSessionKey]['field'].'\' AND pid=f.id)';
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "sqlSearchField $sqlSearchField gesetzt ");
            }

            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "sqlSearchField $sqlSearchField dbCollation ".$GLOBALS['TL_CONFIG']['dbCollation']);

            if ('_ci' === substr($GLOBALS['TL_CONFIG']['dbCollation'], -3)) {
                $this->procedure[] = 'LOWER(CAST('.$sqlSearchField.' AS CHAR)) REGEXP LOWER(?)';
            } else {
                $this->procedure[] = 'CAST('.$sqlSearchField.' AS CHAR) REGEXP ?';
            }

            $this->values[] = $session['search'][$strSessionKey]['value'];
        }

        $options_sorter = [];

        foreach ($searchFields as $field) {
            $option_label = \strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field];
            $options_sorter[utf8_romanize($option_label).'_'.$field] = '  <option value="'.specialchars($field).'"'.(($field === $session['search'][$strSessionKey]['field']) ? ' selected="selected"' : '').'>'.$option_label.'</option>';
        }

        // Sort by option values
        $options_sorter = natcaseksort($options_sorter);
        $active = ('' !== $session['search'][$strSessionKey]['value']) ? true : false;

        return '

<div class="tl_search tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['search'].':</strong>
<select name="tl_field" class="tl_select'.($active ? ' active' : '').'">
'.implode("\n", $options_sorter).'
</select>
<span> = </span>
<input type="search" name="tl_value" class="tl_text'.($active ? ' active' : '').'" value="'.specialchars($session['search'][$strSessionKey]['value']).'">
</div>';
    }

    /**
     * Return a select menu that allows to sort results by a particular field.
     *
     * @return string
     */
    protected function sortMenu()
    {
        if (2 !== $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] && 4 !== $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) {
            return '';
        }

        $sortingFields = [];

        // Get sorting fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k => $v) {
            if ($v['sorting']) {
                $sortingFields[] = $k;
            }
        }

        // Return if there are no sorting fields
        if (empty($sortingFields)) {
            return '';
        }

        $this->bid = 'tl_buttons_a';
        $session = $this->Session->getData();
        $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

        // PBD invalid in php8 $strSessionKey = (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;
        $strSessionKey = ((4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ) ? $this->strFormKey : $this->strTable;

        // Add PID to order fields
        if (3 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] && \Database::getInstance()->fieldExists('pid', $this->strTable)) {
            array_unshift($orderBy, 'pid');
        }

        // Set sorting from user input
        if ('tl_filters' === \Input::post('FORM_SUBMIT')) {
            $session['sorting'][$strSessionKey] = \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][\Input::post('tl_sort')]['flag'], [2, 4, 6, 8, 10, 12], true) ? \Input::post('tl_sort').' DESC' : \Input::post('tl_sort');
            $this->Session->setData($session);
        }

        // Overwrite the "orderBy" value with the session value
        elseif (isset($session['sorting'][$strSessionKey]) && $session['sorting'][$strSessionKey]) {
            $overwrite = preg_quote(preg_replace('/\s+.*$/', '', $session['sorting'][$strSessionKey]), '/');
            $orderBy = array_diff($orderBy, preg_grep('/^'.$overwrite.'/i', $orderBy));

            array_unshift($orderBy, $session['sorting'][$strSessionKey]);

            $this->firstOrderBy = $overwrite;
            $this->orderBy = $orderBy;
        }

        $options_sorter = [];

        // Sorting fields
        foreach ($sortingFields as $field) {
            $options_label = \strlen(($lbl = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'])) ? $lbl : $GLOBALS['TL_LANG']['MSC'][$field];

            if (\is_array($options_label)) {
                $options_label = $options_label[0];
            }

            $options_sorter[$options_label] = '  <option value="'.specialchars($field).'"'.((isset($session['sorting'][$strSessionKey]) && !\strlen($session['sorting'][$strSessionKey]) && $field === $firstOrderBy || $field === str_replace(' DESC', '',(string) $session['sorting'][$strSessionKey])) ? ' selected="selected"' : '').'>'.$options_label.'</option>';
        }

        // Sort by option values
        uksort($options_sorter, 'strcasecmp');

        return '

<div class="tl_sorting tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['sortBy'].':</strong>
<select name="tl_sort" id="tl_sort" class="tl_select">
'.implode("\n", $options_sorter).'
</select>
</div>';
    }

    /**
     * Return a select menu to limit results.
     *
     * @param bool
     *
     * @return string
     */
    protected function limitMenu($blnOptional = false)
    {
        // Fix warnings and search panel in formdata driver #4 Fritz Michael Gschwantner
        /*
                EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'this->procedure len '.\count($this->procedure).' this->value len '.\count($this->value));
                if (\count($this->value) < 2) {
        */
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'this->procedure len '.\count($this->procedure).' this->value len '.\count($this->values));
        if (\count($this->values) < 2) {
            return false;
        }
        $session = $this->Session->getData();
        // PBD invalid in php8 $filter = (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;
        $filter = ((4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ) ? $this->strFormKey : $this->strTable;
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'limitMenu filter '.$filter);      # PBD leycom
        $fields = '';

        if (\is_array($this->procedure)) {
            $this->procedure = array_unique($this->procedure);
        }
        if (\is_array($this->values)) {
            $this->values = array_unique($this->values);
        }
        EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'limitMenu tl_filters '.\Input::post('FORM_SUBMIT').' tl_filters_limit '.\Input::post('FORM_SUBMIT') );      # PBD 
        // Set limit from user input
        if ('tl_filters' === \Input::post('FORM_SUBMIT') || 'tl_filters_limit' === \Input::post('FORM_SUBMIT')) {
            if ('tl_limit' !== \Input::post('tl_limit')) {
                $session['filter'][$filter]['limit'] = \Input::post('tl_limit');
            } else {
                unset($session['filter'][$filter]['limit']);
            }

            $this->Session->setData($session);

            if ('tl_filters_limit' === \Input::post('FORM_SUBMIT')) {
                \Controller::reload();
            }
        }

        // Set limit from table configuration
        else {
            $this->limit = (isset($session['filter'][$filter]['limit']) && \strlen($session['filter'][$filter]['limit'])) ? (('all' === $session['filter'][$filter]['limit']) ? null : $session['filter'][$filter]['limit']) : '0,'.$GLOBALS['TL_CONFIG']['resultsPerPage'];

            $sqlQuery = '';
            $sqlSelect = '';
            $sqlDetailFields = '';
            $sqlWhere = '';

            if (!empty($this->procedure)) {
                $arrProcedure = $this->procedure;
                foreach ($arrProcedure as $kProc => $vProc) {
                    $arrParts = preg_split('/[\s=><\!]/si', $vProc);
                    $strProcField = $arrParts[0];
                    if (\in_array($strProcField, $this->arrDetailFields, true)) {
                        $arrProcedure[$kProc] = "(SELECT value FROM tl_formdata_details WHERE ff_name='".$strProcField."' AND pid=f.id)=?";
                    }
                }
                $sqlWhere = ' WHERE '.implode(' AND ', $arrProcedure);
            }
            $sqlSelect = 'SELECT COUNT(*) AS count FROM '.$this->strTable.' f';
            $sqlQuery = $sqlSelect.$sqlWhere;
            EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, "sqlQuery $sqlQuery");

            $objTotal = \Database::getInstance()->prepare($sqlQuery)
                ->execute($this->values)
            ;
            $total = $objTotal->count;
            $options_total = 0;
            $blnIsMaxResultsPerPage = false;

            // Overall limit
            if ($total > $GLOBALS['TL_CONFIG']['maxResultsPerPage'] && (null === $this->limit || preg_replace('/^.*,/', '', $this->limit) === $GLOBALS['TL_CONFIG']['maxResultsPerPage'])) {
                if (null === $this->limit) {
                    $this->limit = '0,'.$GLOBALS['TL_CONFIG']['maxResultsPerPage'];
                }

                $blnIsMaxResultsPerPage = true;
                $GLOBALS['TL_CONFIG']['resultsPerPage'] = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
                $session['filter'][$filter]['limit'] = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
            }

            $options = '';

            // Build options
            if ($total > 0) {
                $options = '';
                $options_total = ceil($total / $GLOBALS['TL_CONFIG']['resultsPerPage']);

                // Reset limit if other parameters have decreased the number of results
                if (null !== $this->limit && ('' === $this->limit || preg_replace('/,.*$/', '', $this->limit) > $total)) {
                    $this->limit = '0,'.$GLOBALS['TL_CONFIG']['resultsPerPage'];
                }

                // Build options
                for ($i = 0; $i < $options_total; ++$i) {
                    $this_limit = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage']).','.$GLOBALS['TL_CONFIG']['resultsPerPage'];
                    $upper_limit = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + $GLOBALS['TL_CONFIG']['resultsPerPage']);

                    if ($upper_limit > $total) {
                        $upper_limit = $total;
                    }

                    $options .= '
  <option value="'.$this_limit.'"'.\Widget::optionSelected($this->limit, $this_limit).'>'.($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + 1).' - '.$upper_limit.'</option>';
                }

                if (!$blnIsMaxResultsPerPage) {
                    $options .= '
  <option value="all"'.\Widget::optionSelected($this->limit, null).'>'.$GLOBALS['TL_LANG']['MSC']['filterAll'].'</option>';
                }
            }

            // Return if there is only one page
            if ($blnOptional && ($total < 1 || $options_total < 2)) {
                return '';
            }

            $fields = '
<select name="tl_limit" class="tl_select'.(('all' !== $session['filter'][$filter]['limit'] && $total > $GLOBALS['TL_CONFIG']['resultsPerPage']) ? ' active' : '').'" onchange="this.form.submit()">
  <option value="tl_limit">'.$GLOBALS['TL_LANG']['MSC']['filterRecords'].'</option>'.$options.'
</select> ';
        }

        return '

<div class="tl_limit tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['showOnly'].':</strong> '.$fields.'
</div>';
    }

    /**
     * Generate the filter panel and return it as HTML string.
     *
     * @return string
     */
    protected function filterMenu($intFilterPanel)
    {
        $fields = '';
        $this->bid = 'tl_buttons_a';
        $sortingFields = [];
        $session = $this->Session->getData();
        // PBD invalid in php8 $filter = (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ? $this->strFormKey : $this->strTable;
        $filter = ((4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) ? $this->strTable.'_'.CURRENT_ID : (\strlen($this->strFormKey)) ) ? $this->strFormKey : $this->strTable;

        // Get the sorting fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k => $v) {
            if (isset($v['filter'])&&(int) ($v['filter']) === $intFilterPanel) {            // PBD
                $sortingFields[] = $k;
            }
        }
        // Return if there are no sorting fields
        if (empty($sortingFields)) {
            return '';
        }

        // Set filter from user input
        if ('tl_filters' === \Input::post('FORM_SUBMIT')) {
            foreach ($sortingFields as $field) {
                if (\Input::post($field, true) !== 'tl_'.$field) {
                    $session['filter'][$filter][$field] = \Input::post($field, true);
                } else {
                    unset($session['filter'][$filter][$field]);
                }
            }

            // add filter if called by special form dependent BE nav item
            if ('' !== $this->strFormFilterKey && '' !== $this->strFormFilterValue) {
                $session['filter'][$filter][$this->strFormFilterKey] = $this->strFormFilterValue;
            }

            $this->Session->setData($session);
        }

        // Set filter from table configuration
        else {
            foreach ($sortingFields as $field) {
                if (isset($session['filter'][$filter][$field])) {
                    $strProcField = $field;

                    if (\in_array($field, $this->arrDetailFields, true)) {
                        $strProcField = "(SELECT `value` FROM tl_formdata_details WHERE ff_name='".$field."' AND pid=f.id)";
                    }

                    // Sort by day
                    if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [5, 6], true)) {
                        if ('' === $session['filter'][$filter][$field]) {
                            $this->procedure[] = $strProcField."=''";
                        } else {
                            $objDate = new \Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $strProcField.' BETWEEN ? AND ?';
                            $this->values[] = $objDate->dayBegin;
                            $this->values[] = $objDate->dayEnd;
                        }
                    }

                    // Sort by month
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [7, 8], true)) {
                        if ('' === $session['filter'][$filter][$field]) {
                            $this->procedure[] = $strProcField."=''";
                        } else {
                            $objDate = new \Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $strProcField.' BETWEEN ? AND ?';
                            $this->values[] = $objDate->monthBegin;
                            $this->values[] = $objDate->monthEnd;
                        }
                    }

                    // Sort by year
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [9, 10], true)) {
                        if ('' === $session['filter'][$filter][$field]) {
                            $this->procedure[] = $strProcField."=''";
                        } else {
                            $objDate = new \Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $strProcField.' BETWEEN ? AND ?';
                            $this->values[] = $objDate->yearBegin;
                            $this->values[] = $objDate->yearEnd;
                        }
                    }

                    // Manual filter
                    elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple']) {
                        // CSV lists (see #2890)
                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv'])) {
                            if (\in_array($field, $this->arrDetailFields, true)) {
                                $this->procedure[] = \Database::getInstance()
                                    ->findInSet('?', "(SELECT REPLACE(`value`, '".$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv']."', ',') FROM tl_formdata_details WHERE ff_name='".$field."' AND pid=f.id)", true)
                                ;
                                $this->values[] = $session['filter'][$filter][$field];
                            } else {
                                $this->procedure[] = \Database::getInstance()
                                    ->findInSet('?', $strProcField, true)
                                ;
                                $this->values[] = $session['filter'][$filter][$field];
                            }
                        } else {
                            $this->procedure[] = $strProcField.' LIKE ?';
                            $this->values[] = '%"'.$session['filter'][$filter][$field].'"%';
                        }
                    }

                    // Other sort algorithm
                    else {
                        $this->procedure[] = $strProcField.'=?';
                        $this->values[] = $session['filter'][$filter][$field];
                    }
                }
            }
        }

        // Add sorting options
        foreach ($sortingFields as $cnt => $field) {
            $arrValues = [];
            $arrProcedure = [];

            if (4 === $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode']) {
                $arrProcedure[] = 'pid=?';
                $arrValues[] = CURRENT_ID;
            }

            // add condition if called form specific formdata
            if ('' !== $this->strFormFilterKey && '' !== $this->strFormFilterValue) {
                $arrProcedure[] = $this->strFormFilterKey.'=?';
                $arrValues[] = $this->strFormFilterValue;
            }

            if (!empty($this->root) && \is_array($this->root)) {
                $arrProcedure[] = 'id IN('.implode(',', array_map('intval', $this->root)).')';
            }

            if (\in_array($field, $this->arrBaseFields, true)) {
                $sqlField = $field;
            } elseif (\in_array($field, $this->arrDetailFields, true)) {
                $sqlField = "SELECT DISTINCT(value) FROM tl_formdata_details WHERE ff_name='".$field."' AND pid=f.id";
            }

            $objFields = \Database::getInstance()->prepare('SELECT DISTINCT('.$sqlField.') AS `'.$field.'` FROM '.$this->strTable.' f '.((\is_array($arrProcedure) && (isset($arrProcedure[0]) && \strlen($arrProcedure[0]))) ? ' WHERE '.implode(' AND ', $arrProcedure) : ''))
                ->execute($arrValues)
            ;

            // Begin select menu
            $fields .= '
<select name="'.$field.'" id="'.$field.'" class="tl_select'.(isset($session['filter'][$filter][$field]) ? ' active' : '').'">
  <option value="tl_'.$field.'">'.(\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']).'</option>
  <option value="tl_'.$field.'">---</option>';

            if ($objFields->numRows) {
                $options = $objFields->fetchEach($field);

                // Sort by day
                if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'])&&    //PBD
                  \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [5, 6], true)) {
                    (6 === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag']) ? rsort($options) : sort($options);

                    foreach ($options as $k => $v) {
                        if ('' === $v) {
                            $options[$v] = '-';
                        } else {
                            $options[$v] = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $v);
                        }

                        unset($options[$k]);
                    }
                }

                // Sort by month
                elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'])&&    //PBD
                  \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [7, 8], true)) {
                    (8 === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag']) ? rsort($options) : sort($options);

                    foreach ($options as $k => $v) {
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "options[$k]$v");
                        if ('' === $v) {
                            $options[$v] = '-';
                        } else {
                            $x = (int) $v;
                            $options[$v] = date('Y-m', $x);
                            $intMonth = (date('m', $x) - 1);

                            if (isset($GLOBALS['TL_LANG']['MONTHS'][$intMonth])) {
                                $options[$v] = $GLOBALS['TL_LANG']['MONTHS'][$intMonth].' '.date('Y', $x);
                            }
                        }

                        unset($options[$k]);
                    }
                }

                // Sort by year
                elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'])&&    //PBD
                  \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [9, 10], true)) {
                    (10 === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag']) ? rsort($options) : sort($options);

                    foreach ($options as $k => $v) {
                        if ('' === $v) {
                            $options[$v] = '-';
                        } else {
                            $options[$v] = date('Y', $v);
                        }

                        unset($options[$k]);
                    }
                }

                // Manual filter
                if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'])&&       //PBD
                  $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple']) {               
                    $moptions = [];

                    foreach ($options as $option) {
                        // CSV lists (see #2890)
                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv'])) {
                            $doptions = trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv'], $option);
                        } else {
                            $doptions = deserialize($option);
                        }

                        if (\is_array($doptions)) {
                            $moptions = array_merge($moptions, $doptions);
                        }
                    }

                    $options = $moptions;
                }

                $options = array_unique($options);
                $options_callback = [];

                // Call the options_callback
                if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'])&&           //PBD
                   (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'])) && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference']) {
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'])) {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][1];

                        $this->import($strClass);
                        $options_callback = $this->$strClass->$strMethod($this);
                    } elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'])) {
                        $options_callback = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']($this);
                    }

                    // Sort options according to the keys of the callback array
                    $options = array_intersect(array_keys($options_callback), $options);
                }

                $options_sorter = [];
                if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag']))    //PBD
                  $blnDate = \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [5, 6, 7, 8, 9, 10], true);
                else $blnDate=null;    //PBD

                // Options
                foreach ($options as $kk => $vv) {
                    $value = $blnDate ? $kk : $vv;

                    // Replace the ID with the foreign key
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'])) {
                        $key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'], 2);

                        $objParent = \Database::getInstance()->prepare('SELECT '.$key[1].' AS value FROM '.$key[0].' WHERE id=?')
                            ->limit(1)
                            ->execute($vv)
                        ;

                        if ($objParent->numRows) {
                            $vv = $objParent->value;
                        }
                    }

                    // Replace boolean checkbox value with "yes" and "no"
                    elseif (                    //PBD
                        ((isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isBoolean'])&&$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isBoolean']) ||
                        (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'])&&'checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] )) && 
                        (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'])&&!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'])
                        
                    ) {
                        $vv = ('' !== $vv) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                    }

                    // Options callback
                    elseif (!empty($options_callback) && \is_array($options_callback)) {
                        $vv = $options_callback[$vv];
                    }

                    // Get the name of the parent record (see #2703)
                    elseif ('pid' === $field) {
                        $this->loadDataContainer($this->ptable);
                        $showFields = $GLOBALS['TL_DCA'][$this->ptable]['list']['label']['fields'];

                        if (!$showFields[0]) {
                            $showFields[0] = 'id';
                        }

                        $objShowFields = \Database::getInstance()->prepare('SELECT '.$showFields[0].' FROM '.$this->ptable.' WHERE id=?')
                            ->limit(1)
                            ->execute($vv)
                        ;

                        if ($objShowFields->numRows) {
                            $vv = $objShowFields->$showFields[0];
                        }
                    }

                    $option_label = '';

                    // Use reference array
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'])) {
                        $option_label = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv];
                    }

                    // Associative array
                    elseif        //PBD
                      (
                        (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isAssociative'])&&$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isAssociative']) || 
                        (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'])&&array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options']))
                        )
                      ) {
                        $option_label = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'][$vv];
                    }

                    // No empty options allowed
                    if (empty($option_label)) {
                        $option_label = $vv ?: '-';
                    }

                    $options_sorter['  <option value="'.specialchars($value).'"'.((isset($session['filter'][$filter][$field]) && $value === $session['filter'][$filter][$field]) ? ' selected="selected"' : '').'>'.$option_label.'</option>'] = utf8_romanize($option_label);
                }

                // Sort by option values
                if (!$blnDate) {
                    natcasesort($options_sorter);

                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'])&&
                      \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], [2, 4, 12], true)) {
                        $options_sorter = array_reverse($options_sorter, true);
                    }
                }

                $fields .= "\n".implode("\n", array_keys($options_sorter));
            }

            // End select menu
            $fields .= '
</select> ';

            // Force a line-break after six elements (see #3777)
            if ((($cnt + 1) % 6) === 0) {
                $fields .= '<br>';
            }
        }

        return '

<div class="tl_filter tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['filter'].':</strong> '.$fields.'
</div>';
    }

    /**
     * Return the formatted group header as string.
     *
     * @param string
     * @param mixed
     * @param int
     *
     * @return string
     */
    protected function formatCurrentValue($field, $value, $mode)
    {
        $remoteNew = $value; // see #3861

        if ('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple']) {
            $remoteNew = ('' !== $value) ? ucfirst($GLOBALS['TL_LANG']['MSC']['yes']) : ucfirst($GLOBALS['TL_LANG']['MSC']['no']);
        } elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'])) {
            $key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'], 2);

            $objParent = \Database::getInstance()->prepare('SELECT '.$key[1].' AS value FROM '.$key[0].' WHERE id=?')
                ->limit(1)
                ->execute($value)
            ;

            if ($objParent->numRows) {
                $remoteNew = $objParent->value;
            }
        } elseif (\in_array($mode, [1, 2], true)) {
            $remoteNew = ('' !== $value) ? ucfirst(utf8_substr($value, 0, 1)) : '-';
        } elseif (\in_array($mode, [3, 4], true)) {
            if (!isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['length'])) {
                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['length'] = 2;
            }

            $remoteNew = ('' !== $value) ? ucfirst(utf8_substr($value, 0, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['length'])) : '-';
        } elseif (\in_array($mode, [5, 6], true)) {
            $remoteNew = ('' !== $value) ? \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $value) : '-';
        } elseif (\in_array($mode, [7, 8], true)) {
            $remoteNew = ('' !== $value) ? date('Y-m',(int) $value) : '-';                # PBD leycom        
            $intMonth = ('' !== $value) ? (date('m',(int) $value) - 1) : '-';             # PBD leycom
            if (isset($GLOBALS['TL_LANG']['MONTHS'][$intMonth])) {
               $remoteNew = ('' !== $value) ? $GLOBALS['TL_LANG']['MONTHS'][$intMonth].' '.date('Y', (int)$value) : '-';   # PBD leycom            
            }
        } elseif (\in_array($mode, [9, 10], true)) {
            $remoteNew = ('' !== $value) ? date('Y',(int) $value) : '-';
        } else {
            if ('checkbox' === $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple']) {
                $remoteNew = ('' !== $value) ? $field : '';
            } elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'])) {
                $remoteNew = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$value];
            } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'])) {
                $remoteNew = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'][$value];
            } else {
                $remoteNew = $value;
            }

            if (\is_array($remoteNew)) {
                $remoteNew = $remoteNew[0];
            }

            if (empty($remoteNew)) {
                $remoteNew = '-';
            }
        }

        return $remoteNew;
    }

    /**
     * Return the formatted group header as string.
     *
     * @param string
     * @param mixed
     * @param int
     * @param array
     *
     * @return string
     */
    protected function formatGroupHeader($field, $value, $mode, $row)
    {
        static $lookup = [];

        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'])) {
            $group = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'][$value];
        } elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'])) {
            if (!isset($lookup[$field])) {
                $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][0];
                $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][1];

                $this->import($strClass);
                $lookup[$field] = $this->$strClass->$strMethod($this);
            }

            $group = $lookup[$field][$value];
        } else {
            $group = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$value]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$value][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$value];
        }

        if (empty($group)) {
            $group = \is_array($GLOBALS['TL_LANG'][$this->strTable][$value]) ? $GLOBALS['TL_LANG'][$this->strTable][$value][0] : $GLOBALS['TL_LANG'][$this->strTable][$value];
        }

        if (empty($group)) {
            $group = $value;

            if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isBoolean'] && '-' !== $value) {
                $group = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'];
            }
        }

        // Call the group callback ($group, $sortingMode, $firstOrderBy, $row, $this)
        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback'])) {
            $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback'][0];
            $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback'][1];

            $this->import($strClass);
            $group = $this->$strClass->$strMethod($group, $mode, $field, $row, $this);
        } elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback'])) {
            $group = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback']($group, $mode, $field, $row, $this);
        }

        return $group;
    }

    /**
     * Check if we need to preload TinyMCE.
     */
    protected function checkForTinyMce(): void
    {
        if (!isset($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'])) {
            return;
        }

        foreach ($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'] as $palette) {
            $fields = trimsplit(',', $palette);

            foreach ($fields as $field) {
                if (!isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['rte'])) {
                    continue;
                }

                $rte = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['rte'];

                if (0 !== strncmp($rte, 'tiny', 4)) {
                    continue;
                }

                [$file, $type] = explode('|', $rte);
                $key = 'ctrl_'.$field;

                $GLOBALS['TL_RTE'][$file][$key] = [
                    'id' => $key,
                    'file' => $file,
                    'type' => $type,
                ];
            }
        }
    }

    /**
     * Generate the form to select import source and basic settings and return it as HTML string.
     *
     * @return string
     */
    protected function formImportSource()
    {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'formImportSource');

        $arrSessionData = $this->Session->get('EFG_CO4');

        $objFileSelector = new \FileTree(\Widget::getAttributesFromDca($GLOBALS['TL_DCA']['tl_formdata']['fields']['import_source'], 'import_source', null, 'import_source', 'tl_formdata'));

        // Return form
        return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=import', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_formdata']['import'][1].'</h2>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_formdata_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_formdata_import">
<input type="hidden" name="FORM_MODE" value="preview">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox block">
  <div class="w50">
  <h3><label for="separator">'.$GLOBALS['TL_LANG']['MSC']['separator'][0].'</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset()">
    <option value="comma"'.('comma' === $arrSessionData['import'][$this->strFormKey]['separator'] ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['comma'].'</option>
    <option value="semicolon"'.('semicolon' === $arrSessionData['import'][$this->strFormKey]['separator'] || !isset($arrSessionData['import'][$this->strFormKey]['separator']) ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['semicolon'].'</option>
    <option value="tabulator"'.('tabulator' === $arrSessionData['import'][$this->strFormKey]['separator'] ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['tabulator'].'</option>
  </select>'.(\strlen($GLOBALS['TL_LANG']['MSC']['separator'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['separator'][1].'</p>' : '').'
  </div>
  <div class="w50 m12 cbx">
  <div class="tl_checkbox_single_container">
  <input name="csv_has_header" id="csv_has_header" type="checkbox" value="1"'.('1' === $arrSessionData['import'][$this->strFormKey]['csv_has_header'] ? ' checked="checked"' : '').'>
  <label for="csv_has_header">'.$GLOBALS['TL_LANG']['tl_formdata']['csv_has_header'][0].'</label>
  </div>
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_formdata']['csv_has_header'][1].'</p>
  </div>

  <div class="clr">'
        .$objFileSelector->parse().(\strlen($GLOBALS['TL_LANG']['tl_formdata']['import_source'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_formdata']['import_source'][1].'</p>' : '').'

  </div>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_formdata']['import'][0]).'" onfocus="document.cookie = \'BE_PAGE_OFFSET=0; path=/\';">
</div>

</div>
</form>'.$this->getFilepickerJavascript('reloadEfgImportSource');
    }

    /**
     * Generate the form to select the field mappings and return it as HTML string.
     *
     * @return string
     */
    protected function formImportPreview($objFile, $strSeparator)
    {
        $arrSessionData = $this->Session->get('EFG_CO4');
        $blnUseCsvHeader = ('1' === $arrSessionData['import'][$this->strFormKey]['csv_has_header'] ? true : false);

        $arrEntries = [];
        $resFile = $objFile->handle;

        $intTotal = 0;
        $intReadLines = 50;
        if ($blnUseCsvHeader) {
            ++$intReadLines;
        }

        while (($arrRow = @fgetcsv($resFile, null, $strSeparator)) !== false) {
            $arrEntries[] = $arrRow;
            ++$intTotal;
            if ($intTotal === $intReadLines) {
                break;
            }
        }

        if ($blnUseCsvHeader && !isset($arrSessionData['import'][$this->strFormKey]['import_cols'])) {
            foreach ($arrEntries[0] as $col => $val) {
                if (\array_key_exists($val, $this->arrImportableFields)) {
                    $arrSessionData['import'][$this->strFormKey]['import_cols'][$col] = $val;
                } else {
                    $mxRes = array_search($val, $this->arrImportableFields, true);
                    if (false !== $mxRes) {
                        $arrSessionData['import'][$this->strFormKey]['import_cols'][$col] = $mxRes;
                    } else {
                        $arrSessionData['import'][$this->strFormKey]['import_cols'][$col] = '__IGNORE__';
                    }
                }
            }
        }

        $this->Session->set('EFG_CO4', $arrSessionData);

        // plugin stylect cannot handle selects inside scrolling div
        // .. deactivate it
        $return = '
<script>
var Stylect = {
	convertSelects: function() { return; }
};
</script>
';

        $return .= '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=import', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_formdata']['import'][1].'</h2>'
            .\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_formdata_import" class="tl_form" method="post">
<div class="tl_formbody_edit tl_formdata_import">
	<input type="hidden" name="FORM_SUBMIT" value="tl_formdata_import">
	<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
	<input type="hidden" name="FORM_MODE" value="import">
	<input type="hidden" name="import_source" value="'.\Input::post('import_source').'">
	<input type="hidden" name="separator" value="'.\Input::post('separator').'">
	<input type="hidden" name="csv_has_header" value="'.\Input::post('csv_has_header').'">

	<div class="tl_tbox block">
		<h3>'.$GLOBALS['TL_LANG']['tl_formdata']['import_preview'][0].'</h3>
		<p class="tl_help">'.$GLOBALS['TL_LANG']['tl_formdata']['import_preview'][1].'</p>
		<div class="fd_import_prev">
			<div>';
        $return .= '
			<table class="fd_import_data">
				<thead><tr>';
        foreach ($arrEntries[0] as $col => $val) {
            $return .= '
					<td>'.$this->importFieldmapMenu($arrEntries, $col, $val).'</td>';
        }
        $return .= '
				</tr></thead>';
        $return .= '
				<tbody>';

        if ($blnUseCsvHeader) {
            array_shift($arrEntries);
        }

        foreach ($arrEntries as $row) {
            $return .= '
				<tr>';
            foreach ($row as $col => $val) {
                $return .= '
					<td>'.$val.'</td>';
            }
            $return .= '
			</tr>';
        }
        $return .= '
				</tbody>
			</table>';
        $return .= '
			</div>
		</div>
	</div>
</div>

<div class="tl_formbody_submit">
	<div class="tl_submit_container">
		<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_formdata']['import'][0]).'">
	</div>
</div>
</form>';

        return $return;
    }

    /**
     * Generate a dropdown menu to select destination field and return it as HTML string.
     *
     * @return string
     */
    protected function importFieldmapMenu(&$arrEntries, $col, $val)
    {
        $arrSessionData = $this->Session->get('EFG_CO4');

        $return = '
<select name="import_cols['.$col.']">
	<option value="__IGNORE__"'.((!isset($arrSessionData['import'][$this->strFormKey]['import_cols'][$col]) || '__IGNORE__' === $arrSessionData['import'][$this->strFormKey]['import_cols'][$col]) ? ' selected="SELECTED"' : '').'>'.$GLOBALS['TL_LANG']['tl_formdata']['option_import_ignore'].'</option>';
        if (!empty($this->arrImportableFields)) {
            foreach (array_keys($this->arrImportableFields) as $strFdField) {
                $selected = '';
                if (isset($arrSessionData['import'][$this->strFormKey]['import_cols'])) {
                    if ($arrSessionData['import'][$this->strFormKey]['import_cols'][$col] === $strFdField) {
                        $selected = ' selected="selected"';
                    }
                }
                $return .= '<option value="'.$strFdField.'"'.$selected.'>'.($GLOBALS['TL_DCA']['tl_formdata']['fields'][$strFdField]['label'][0] ?? $strFdField).'</option>';
            }
        }
        $return .= '
</select>';

        return $return;
    }

    private function getSubpaletteJavascript()
    {
        return "
<script>
	AjaxRequest.toggleEfgSubpalette = function (el, id, field) {
		el.blur();
		var item = $(id);

		if (item) {
			if (!el.value) {
				el.value = 1;
				el.checked = 'checked';
				item.setStyle('display', 'block');
				new Request.Contao({field:el}).post({'action':'toggleEfgSubpalette', 'id':id, 'field':field, 'state':1, 'REQUEST_TOKEN':Contao.request_token});
			} else {
				el.value = '';
				el.checked = '';
				item.setStyle('display', 'none');
				new Request.Contao({field:el}).post({'action':'toggleEfgSubpalette', 'id':id, 'field':field, 'state':0, 'REQUEST_TOKEN':Contao.request_token});
			}
			return;
		}

		new Request.Contao({
			field: el,
			evalScripts: false,
			onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
			onSuccess: function(txt, json) {
				var div = new Element('div', {
					'id': id,
					'html': txt,
					'styles': {
						'display': 'block'
					}
				}).inject($(el).getParent('div').getParent('div'), 'after');

				// Execute scripts after the DOM has been updated
				if (json.javascript) $exec(json.javascript);

				el.value = 1;
				el.checked = 'checked';

				AjaxRequest.hideBox();
				Backend.addInteractiveHelp();
				Backend.addColorPicker();

				// HOOK
				window.fireEvent('subpalette'); // Backwards compatibility
				window.fireEvent('ajax_change');
			}
		}).post({'action':'toggleEfgSubpalette', 'id':id, 'field':field, 'load':1, 'state':1, 'REQUEST_TOKEN':Contao.request_token});
	}

window.addEvent('domready', function(){
	$$('input[onclick^=AjaxRequest.toggleSubpalette]').each(function(item){
		item.set('onclick', (item.get('onclick').replace(/toggleSubpalette/, 'toggleEfgSubpalette')))
	});
});
</script>";
    }

    private function getFilepickerJavascript($strReload)
    {
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "getFilepickerJavascript strReload $strReload");

        return "
<script>
function handleEfgFileselectorButton(){
	$$('a[href*=contao/picker]').addEvent('click', function(e){
		var el = e.target;
		var elHidden = el.getParent('div.selector_container').getPrevious('input[type=hidden]');
		var opt = { 'id': elHidden.get('name'), 'url': e.target.get('href') };
		$$('div#simple-modal div.simple-modal-footer a.btn.primary').removeEvents('click').addEvent('click', function() {
// console.log('PBD removeEvents'); uebernehmen filename klick
			var val = [];
			var	frm = null;
			var frms = window.frames;
			for (var i=0; i<frms.length; i++) {
				if (frms[i].name == 'simple-modal-iframe') {
					frm = frms[i];
					break;
				}
			}
			if (frm === null) {
				alert('Could not find the SimpleModal frame');
				return;
			}
			if (frm.document.location.href.indexOf('contao\\/main.php') != -1)  {   // PBD Error / muss escaped sein
				//alert(Contao.lang.close);
				alert('invalid File selected');
				return; // see #5704     was das bedeutet Fehlermeldung sollt sein bitte andere Datei auswaehel  den Fehler such ich noch
			}

      /* PBD geändert die ID ist nicht mehr tl_listing sondern tl_select contao 3.5 */
      /* PBD geändert die ID ist in co4 wieder tl_listing */

			//var inp = frm.document.getElementById('tl_select').getElementsByTagName('input');
			var inp = frm.document.getElementById('tl_listing').getElementsByTagName('input');
			for (var i=0; i<inp.length; i++) {
				if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
				if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));
			}
			if (opt.tag) {
				$(opt.tag).value = val.join(',');
				if (opt.url.match(/page\\.php/)) {
					$(opt.tag).value = '{{link_url::' + $(opt.tag).value + '}}';
				}
				opt.self.set('href', opt.self.get('href').replace(/&value=[^&]*/, '&value='+val.join(',')));
			} else {
				$('ctrl_'+opt.id).value = val.join(\"\t\");
				var act = (opt.url.indexOf('contao/page.php') != -1) ? 'reloadPagetree' : '".$strReload."';
				new Request.Contao({
					field: $('ctrl_'+opt.id),
					evalScripts: false,
					onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
					onSuccess: function(txt, json) {
						$('ctrl_'+opt.id).getParent('div').set('html', json.content);
						json.javascript && Browser.exec(json.javascript);
						AjaxRequest.hideBox();
						window.fireEvent('ajax_change');
					}
				}).post({'action':act, 'name':opt.id, 'value':$('ctrl_'+opt.id).value, 'REQUEST_TOKEN':Contao.request_token});
			}
			$('simple-modal').hide();
			$('simple-modal-overlay').hide();
			document.body.setStyle('overflow', 'auto');
		});
	});
}

window.addEvent('domready', function(){
	handleEfgFileselectorButton();
});
</script>";
        /*
        'REQUEST_TOKEN':Contao.request_token
        'REQUEST_TOKEN':'4sWPMwuMLwLdLF7EYeaf8M6eQGjt0f6J30D739NI24c'
        <script>
              $("ft_import_source").addEvent("click", function(e) {
                e.preventDefault();
                Backend.openModalSelector({
                  "id": "tl_listing",
                  "title": "Quelldatei",
                  "url": this.href + document.getElementById("ctrl_import_source").value,
                  "callback": function(table, value) {
                    new Request.Contao({
                      evalScripts: false,
                      onSuccess: function(txt, json) {
                        $("ctrl_import_source").getParent("div").set("html", json.content);
                        json.javascript && Browser.exec(json.javascript);
                        $("ctrl_import_source").fireEvent("change");
                      }
                    }).post({"action":"reloadFiletree", "name":"import_source", "value":value.join("\t"), "REQUEST_TOKEN":"4sWPMwuMLwLdLF7EYeaf8M6eQGjt0f6J30D739NI24c"});
                  }
                });
              });
            </script></div></div>
        */
    }
}
