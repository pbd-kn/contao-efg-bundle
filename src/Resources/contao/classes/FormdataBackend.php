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

/**
 * Namespace.
 */
/*
 * PBD
 * unter contao 4 ist die Neuerstellung des Caches nicht möglich. Muss evtl separat nachgeholt werden
 */

namespace PBDKN\Efgco4\Resources\contao\classes;

/**
 * Class FormdataBackend.
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class FormdataBackend extends \Backend
{
    /**
     * Data container object.
     *
     * @var object
     */
    protected $objDc;

    /**
     * Current record.
     *
     * @var array
     */
    protected $arrData = [];

    // Types of form fields with storable data
    protected $arrFFstorable = [];

    // Mapping of frontend form fields to backend widgets
    protected $arrMapTL_FFL = [];

    public function __construct()
    {
        EfgLog::setEfgDebugmode(\Input::get('do'));
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "construct erste Zeile'".\Input::get('do')."'");
        //$this->log("PBD FormdataBackend construct do '" . \Input::get('do') . "'", __METHOD__, TL_GENERAL);
        parent::__construct();

        $this->loadDataContainer('tl_form_field');
        $this->import('Formdata');

        // Types of form fields with storable data
        $this->arrFFstorable = $this->Formdata->arrFFstorable;

        // Mapping of frontend form fields to backend widgets
        $this->arrMapTL_FFL = $this->Formdata->arrMapTL_FFL;
    }

    public function generate()
    {
        //$this->log("generate input do " . \Input::get('do') , __METHOD__, TL_GENERAL);
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "generate input do '".\Input::get('do')."'");

        if (\Input::get('do') && 'feedback' !== \Input::get('do')) {
            if ($this->Formdata->arrStoringForms[\Input::get('do')]) {
                $session = $this->Session->getData();
                $session['filter']['tl_feedback']['form'] = $this->Formdata->arrStoringForms[\Input::get('do')]['title'];

                $this->Session->setData($session);
            }
        }

        if ('' === \Input::get('act')) {
            return $this->objDc->showAll();
        }

        $act = \Input::get('act');

        return $this->objDc->$act();
    }

    /**
     * Create DCA files.
     */
    public function createFormdataDca(\DataContainer $dc): void
    {
        $this->intFormId = $dc->id;
        $arrForm = \Database::getInstance()->prepare('SELECT * FROM tl_form WHERE id=?')
            ->execute($this->intFormId)
            ->fetchAssoc()
        ;

        $strFormKey = (!empty($arrForm['alias'])) ? $arrForm['alias'] : str_replace('-', '_', standardize($arrForm['title']));
        //$this->log("createFormdataDca strFormKey $strFormKey formid " . $this->intFormId . " alias " . $arrForm['alias'], __METHOD__, TL_GENERAL);
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "createFormdataDca strFormKey $strFormKey formid ".$this->intFormId." alias '".$arrForm['alias']."'");
        $this->updateConfig([$strFormKey => $arrForm]);
    }

    /**
     * Callback edit button.
     *
     * @return string
     */
    public function callbackEditButton($row, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
    {
        //$this->log("callbackEditButton title $title", __METHOD__, TL_GENERAL);
        $return = '';

        $strDcaKey = array_search($row['form'], $this->Formdata->arrFormsDcaKey, true);
        if ($strDcaKey) {
            $return .= '<a href="'.\Backend::addToUrl($href.'&amp;do=fd_'.$strDcaKey.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
        }

        return $return;
    }

    /**  PBD
     * Update efg/config/config.php, dca and language files
     * Parameter null alle form die kennzeichnung store data in form
     * sonst key der Form => Satz aus tl-Form.
     *
     * @param mixed|null $arrForms
     */
    public function updateConfig($arrForms = null): void
    {
        /*
        * PBD
        * Get all forms marked to store data in tl_formdata (Formdata.php)
        * das sind alle Forms die gekennzechnet sind, dass die Daten gespeichtert werden sollen
          $this->arrStoringForms[$strFormKey] = $objForms->row(); id,title,alias,formID,useFormValues,useFieldNames,efgDebugMode
          $this->arrFormsDcaKey[$strFormKey] = $objForms->title;
        */
        //$this->log("updateConfig ", __METHOD__, TL_GENERAL);
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'updateConfig ');
        $arrStoringForms = $this->Formdata->arrStoringForms;

        if (null === $arrForms) {
            //$this->log("updateConfig aktuelle storingForms bearbeiten", __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'updateConfig aktuell schon gespeicherte storingForms bearbeiten');
            $arrForms = $arrStoringForms;
        }
        //APP_ENV environment variable can contain either prod or dev
        $env = $_ENV['APP_ENV'];
        $cp = realpath(TL_ROOT."/var/cache/$env/contao/");
        //$this->log("updateConfig realpath cache $cp ", __METHOD__, TL_GENERAL);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "updateConfig realpath cache $cp ");
        if (true === $cp) {      // cache vorhanden
            $cachepath = $cp.'dca/';
            // Remove unused dca files
            //$this->log("updateConfig cachepath DCA $cachepath ", __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "updateConfig cachepath DCA $cachepath ");
            $arrFiles = scan(TL_ROOT.$cachepath, true);          // im Kernel scandir mit cache

            foreach ($arrFiles as $strFile) {                 // ueber alle files im cache Pfad
                if ('fd_' === substr($strFile, 0, 3)) {
                    if (empty($arrStoringForms) || !\in_array(str_replace('.php', '', substr($strFile, 3)), array_keys($arrStoringForms), true)) {
                        $objFile = new \File($cachepath.'/'.$strFile);
                        $objFile->delete();
                    }
                }
            }

            // Remove cached dca files
            if (is_dir(TL_ROOT.$cachepath)) {
                $arrFiles = scan(TL_ROOT.$cachepath, true);

                foreach ($arrFiles as $strFile) {
                    if ('fd_' === substr($strFile, 0, 3) || 'tl_formdata.php' === $strFile || 'tl_formdata_details.php' === $strFile) {
                        //$this->log("updateConfig remove cached File " . $cachepath . "/" . $strFile, __METHOD__, TL_GENERAL);
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'updateConfig remove cached File '.$cachepath.'/'.$strFile);
                        $objFile = new \File($cachepath.'/'.$strFile);
                        $objFile->delete();
                    }
                }
            }
        }
        // config/config.php
        $tplConfig = $this->newTemplate('efg_internal_config');
        $tplConfig->arrStoringForms = $arrStoringForms;    /* StoringForms in Config Template */

        /*foreach ($arrStoringForms as $k=>$v) {
          foreach ($v as $k1=>$v1) {
        $this->log("PBD b FormdataBackend updateConfig arrStoringForms[$k][$k1]$v1 ", __METHOD__, TL_GENERAL);
          }
        }
        */

        $objConfig = new \File('vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/config/config.php');

        $objConfig->write($tplConfig->parse());   // PBD config.php neu erzeugen muss cache neu erzeugt werden??
        $objConfig->close();

        $this->log('rewrite vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/config/config.php', __METHOD__, TL_GENERAL);    // PBD
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'rewrite vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/config/config.php');

        if (empty($arrStoringForms)) {
            return; // keine Formulare vorhanden deren Daten gespeichert werden sollen
        }

        // languages/modules.php
        $arrModLangs = scan(TL_ROOT.'/vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/languages');
        $arrLanguages = $this->getLanguages();
        $cachepathlang = "/var/cache/$env/contao/languages/";
        $cachepath = $cp.'dca/';
        foreach ($arrModLangs as $strModLang) /* über alle Sprachen */
    {
        // Remove cached language files
        if (is_file(TL_ROOT.$cachepathlang.$strModLang.'/modules.php')) {
            $objFile = new \File($cachepathlang.$strModLang.'/modules.php');
            $objFile->delete();
            //$this->log("updateConfig remove cached Language File " . $cachepathlang . $strModLang . '/modules.php' , __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'updateConfig remove cached Language File '.$cachepathlang.$strModLang.'/modules.php');
        }
        if (is_file(TL_ROOT.$cachepathlang.$strModLang.'/tl_formdata.php')) {
            $objFile = new \File($cachepathlang.$strModLang.'/tl_formdata.php');
            $objFile->delete();
            //$this->log("updateConfig remove cached Language File " . $cachepathlang . $strModLang . '/tl_formdata.php', __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'updateConfig remove cached Language File '.$cachepathlang.$strModLang.'/tl_formdata.php');
        }

        // Create language files
        if (\array_key_exists($strModLang, $arrLanguages)) {
            $strFile = sprintf('%s/vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/languages/%s/%s.php', TL_ROOT, $strModLang, 'tl_efg_modules');
            //$this->log("languageFile " . $strFile, __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'languageFile '.$strFile);
            if (file_exists($strFile)) {
                include $strFile;
                //$this->log("include " . $strFile, __METHOD__, TL_GENERAL);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'include '.$strFile);
            }

            $tplMod = $this->newTemplate('efg_internal_modules');
            $tplMod->arrStoringForms = $arrStoringForms;
            $objMod = new \File('vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/languages/'.$strModLang.'/modules.php');
            $objMod->write($tplMod->parse());
            $objMod->close();
            //$this->log("neu erzeugt " . 'vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/languages/'.$strModLang.'/modules.php', __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'neu erzeugt '.'vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/languages/'.$strModLang.'/modules.php');
        }
    }

        // dca/fd_FORMKEY.php
        if (\is_array($arrForms) && !empty($arrForms)) {
            foreach ($arrForms as $arrForm) /* bearbeite neu angelegte Forms ($arrForms)*/
        {
            if (!empty($arrForm)) {
                $arrForm = \Database::getInstance()->prepare('SELECT * FROM tl_form WHERE id=?')
                    ->execute($arrForm['id'])
                    ->fetchAssoc()
                ;

                $arrFields = [];
                $arrFieldNamesById = [];

                $arrSelectors = [];
                $arrPalettes = [];
                $strCurrentPalette = '';
                $strPreviousPalette = '';
                //$this->log("a) bearbeite FORM id:  " . $arrForm['id'] . " title: " . $arrForm['title'], __METHOD__, TL_GENERAL);
                // Get all form fields of this form
                $arrFormFields = $this->Formdata->getFormFieldsAsArray($arrForm['id']);

                if (!empty($arrFormFields)) {
                    foreach ($arrFormFields as $strFieldKey => $arrField) {
                        // Ignore not storable fields and some special fields like checkbox CC, fields of type password ...
                        if (!\in_array($arrField['type'], $this->arrFFstorable, true)
                            || ('checkbox' === $arrField['type'] && 'cc' === $strFieldKey)) {
                            continue;
                        }

                        // Set current palette name (for 'conditionalforms' and 'cm_alternativeforms')
                        if (('condition' === $arrField['formfieldType'] && 'start' === $arrField['conditionType'])
                            || ('cm_alternative' === $arrField['formfieldType'] && 'cm_start' === $arrField['cm_alternativeType'])
                            || ('cm_alternative' === $arrField['formfieldType'] && 'cm_else' === $arrField['cm_alternativeType'])) {
                            $arrSelectors[] = $arrField['name'];

                            if ('cm_alternative' === $arrField['formfieldType'] && 'cm_start' === $arrField['cm_alternativeType']) {
                                if ('' !== $strCurrentPalette) {
                                    $strPreviousPalette = $strCurrentPalette;
                                }
                                $strCurrentPalette = $arrField['name'].'_0';

                                $arrField['options'] = [['value' => '', 'label' => '-'], ['value' => '0', 'label' => $arrField['cm_alternativelabel']], ['value' => '1', 'label' => $arrField['cm_alternativelabelelse']]];
                                $arrField['value'] = $arrField['cm_alternativelabel'];

                                // Add field to palette if we are inside a palette
                                if ('' !== $strPreviousPalette) {
                                    $arrPalettes[$strPreviousPalette][] = $arrField['name'];
                                }
                            } elseif ('cm_alternative' === $arrField['formfieldType'] && 'cm_else' === $arrField['cm_alternativeType']) {
                                if ('' !== $strCurrentPalette) {
                                    if ($arrField['name'].'_0' !== $strCurrentPalette) {
                                        $strPreviousPalette = $strCurrentPalette;
                                    }
                                }
                                $strCurrentPalette = $arrField['name'].'_1';
                            } else {
                                if ('' !== $strCurrentPalette) {
                                    $strPreviousPalette = $strCurrentPalette;
                                }
                                $strCurrentPalette = $arrField['name'];
                                // Add field to palette if we are inside a palette
                                if ('' !== $strPreviousPalette) {
                                    $arrPalettes[$strPreviousPalette][] = $arrField['name'];
                                }
                            }
                        }
                        // Ignore conditionalforms conditionType 'stop' and cm_alternativeforms cm_alternativeType 'cm_stop', reset palette name
                        if (('condition' === $arrField['formfieldType'] && 'stop' === $arrField['conditionType'])
                            || ('cm_alternative' === $arrField['formfieldType'] && 'cm_stop' === $arrField['cm_alternativeType'])) {
                            if ('' !== $strPreviousPalette) {
                                $strCurrentPalette = $strPreviousPalette;
                                $strPreviousPalette = '';
                            } else {
                                $strCurrentPalette = '';
                            }
                            continue;
                        }
                        if (!\in_array($strFieldKey, array_keys($arrFields), true)
                            && !('cm_alternative' === $arrField['formfieldType'] && 'cm_else' === $arrField['cm_alternativeType'])) {
                            $arrFields[$strFieldKey] = $arrField;
                            $arrFieldNamesById[$arrField['id']] = $strFieldKey;
                        }
                        // Add field to palette
                        if ('' !== $strCurrentPalette) {
                            if (!('condition' === $arrField['formfieldType'] && 'start' === $arrField['conditionType'])
                                && !('cm_alternative' === $arrField['formfieldType'] && \in_array($arrField['cm_alternativeType'], ['cm_start', 'cm_else', 'cm_stop'], true))) {
                                $arrPalettes[$strCurrentPalette][] = $arrField['name'];
                            }
                        }
                    }
                }
                if (!empty($arrSelectors)) {
                    $arrSelectors = array_unique($arrSelectors);
                }
                $strFormKey = (!empty($arrForm['alias'])) ? $arrForm['alias'] : str_replace('-', '_', standardize($arrForm['title']));
                //$this->log("felder vor newTemplate bearbeitet: $strFormKey ", __METHOD__, TL_GENERAL);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "felder vor newTemplate bearbeitet: $strFormKey ");
                $tplDca = $this->newTemplate('efg_internal_dca_formdata');
                $tplDca->strFormKey = $strFormKey;
                $tplDca->arrForm = $arrForm;
                $tplDca->arrStoringForms = $arrStoringForms;
                $tplDca->arrFields = $arrFields;
                $tplDca->arrFieldNamesById = $arrFieldNamesById;
                $tplDca->arrSelectors = $arrSelectors;
                $tplDca->arrPalettes = $arrPalettes;
                // Enable backend confirmation mail
                $blnBackendMail = false;
                if ($arrForm['sendConfirmationMail'] || \strlen($arrForm['confirmationMailText'])) {
                    $blnBackendMail = true;
                }
                $tplDca->blnBackendMail = $blnBackendMail;
                $objDca = new \File('vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/dca/fd_'.$strFormKey.'.php');
                $objDca->write($tplDca->parse());
                $objDca->close();
                $this->log('dca rewrite '.'vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/dca/fd_'.$strFormKey.'.php', __METHOD__, TL_GENERAL);  // PBD
                EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'dca rewrite '.'vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/dca/fd_'.$strFormKey.'.php');
            }
        }
        }
        // overall dca/fd_feedback.php
        // Get all form fields of all storing forms
        if (!empty($arrStoringForms)) {
            $arrAllFields = [];
            $arrFieldNamesById = [];
            foreach ($arrStoringForms as $strFormKey => $arrForm) {
                // Get all form fields of this form
                //$this->log("b) bearbeite FORM id:  " . $arrForm['id'] . " title: " . $arrForm['title'], __METHOD__, TL_GENERAL);
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'b) bearbeite FORM id:  '.$arrForm['id'].' title: '.$arrForm['title']);

                $arrFormFields = $this->Formdata->getFormFieldsAsArray($arrForm['id']);
                if (!empty($arrFormFields)) {
                    //$this->log("b) arrFormFields da FORM id:  " . $arrForm['id'] . " title: " . $arrForm['title'], __METHOD__, TL_GENERAL);
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'b) arrFormFields da FORM id:  '.$arrForm['id'].' title: '.$arrForm['title']);
                    foreach ($arrFormFields as $strFieldKey => $arrField) {
                        //$this->log("b) arrFormFields da $strFieldKey type " . $arrField['formfieldType'], __METHOD__, TL_GENERAL);
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "b) arrFormFields da $strFieldKey type ".$arrField['formfieldType']);
                        // Ignore not storable fields and some special fields like checkbox CC, fields of type password ...
                        if (!\in_array($arrField['formfieldType'], $this->arrFFstorable, true)
                        || ('checkbox' === $arrField['formfieldType'] && 'cc' === $strFieldKey)
                        || ('condition' === $arrField['formfieldType'] && 'stop' === $arrField['conditionType'])
                        || ('cm_alternative' === $arrField['formfieldType'] && \in_array($arrField['cm_alternativeType'], ['cm_else', 'cm_stop'], true))) {
                            //$this->log("b) arrFormFields da $strFieldKey type ignored" . $arrField['formfieldType'], __METHOD__, TL_GENERAL);
                            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "b) arrFormFields da $strFieldKey type ignored".$arrField['formfieldType']);
                            continue;
                        }
                        $arrAllFields[$strFieldKey] = $arrField;
                        $arrFieldNamesById[$arrField['id']] = $strFieldKey;
                    }
                }
            }

            $strFormKey = 'feedback';
            $tplDca = $this->newTemplate('efg_internal_dca_formdata');
            $tplDca->arrForm = ['key' => 'feedback', 'title' => $this->arrForm['title']];
            $tplDca->arrStoringForms = $arrStoringForms;
            $tplDca->arrFields = $arrAllFields;
            $tplDca->arrFieldNamesById = $arrFieldNamesById;

            $objDca = new \File('vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/dca/fd_'.$strFormKey.'.php');
            $objDca->write($tplDca->parse());
            $objDca->close();
            $this->log('dca rewrite '.'vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/dca/fd_'.$strFormKey.'.php', __METHOD__, TL_GENERAL);  // PBD
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'dca rewrite '.'vendor/pbd-kn/contao-efg-bundle/src/Resources/contao/dca/fd_'.$strFormKey.'.php');
        }
        // Rebuild internal cache
        if (!$GLOBALS['TL_CONFIG']['bypassCache']) {
            //$this->log("vor rebuild internal cache ", __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'vor rebuild internal cache ');
            $this->import('Automator');        // PBD korrektur im Automator existieren die Routinen nicht mehr

//      PBD   das gibts in contao 4 nicht mehr
            //			$this->Automator->generateConfigCache();
            //			$this->Automator->generateDcaCache();
            //			$this->Automator->generateDcaExtracts();
            //$this->Automator->purgeInternalCache(); // löscht den internen cache
            //$this->Automator->generateInternalCache(); // Dauert u.U etwas
            $this->log('update Config file Bitte Cache neu aufbauen', __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'updateConfig Bitte Cache neu aufbauen');
            \Message::addInfo('Cache gel&ouml;scht. Bitte neu erzeugen');
        }
    }

    /**
     * Import Form data from CSV file.
     *
     * @param object Datacontainer
     *
     * @return string CSV imort form
     */
    public function importCsv($dc)
    {
        if ('import' !== \Input::get('key')) {
            return '';
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'importCsv table '.$dc->table.' id '.$dc->id);

        return $dc->importFile();   // PBD baut File die selection auf
    }

    /**
     * Return a new template object.
     *
     * @param string
     *
     * @return object
     */
    private function newTemplate($strTemplate)
    {
        $deb = \Config::get('debugMode');        // im Debugmodus wird der Text TEMPLATE START und TEMPLATE ENDE eingefügt
        // das führt bei den internen php templates zu Fehlern
        \Config::set('debugMode', false);
        $objTemplate = new \BackendTemplate($strTemplate);
        $objTemplate->folder = 'efg_co4';
        \Config::set('debugMode', $deb);

        return $objTemplate;
    }
}
