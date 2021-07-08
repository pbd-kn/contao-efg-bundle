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
    protected $vendorPath = 'vendor/pbd-kn/contao-efg-bundle/';
    protected $cachePath = '';
    protected $rootDir = '';
    // Types of form fields with storable data
    protected $arrFFstorable = [];

    // Mapping of frontend form fields to backend widgets
    protected $arrMapTL_FFL = [];

    public function __construct()
    {
        EfgLog::setEfgDebugmode(\Input::get('do'));
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "construct do '".\Input::get('do')."'");
        $this->rootDir = \System::getContainer()->getParameter('kernel.project_dir');
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "rootDir '".$this->rootDir."'");
        $cp = realpath($this->rootDir.'/var/cache/'.$_ENV['APP_ENV'].'/contao/');   // cachpath
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "cp '".$cp."'");
        if (!empty($cp) && (\strlen($cp) > 0)) {      // cache vorhanden
            $this->cachePath = 'var/cache/'.$_ENV['APP_ENV'].'/contao/';
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "set cachepath '".$this->cachePath."'");
        }
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
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'createFormdataDca start formid '.$this->intFormId);
        $arrForm = \Database::getInstance()->prepare('SELECT * FROM tl_form WHERE id=?')
            ->execute($this->intFormId)
            ->fetchAssoc()
        ;

        $strFormKey = (!empty($arrForm['alias'])) ? $arrForm['alias'] : str_replace('-', '_', standardize($arrForm['title']));
        if (isset($this->Formdata->arrStoringForms)) {
            unset($this->Formdata->arrStoringForms);
        }
        $this->Formdata->getStoringForms();
        $this->updateConfig([$strFormKey => $arrForm]);
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "createFormdataDca end strFormKey $strFormKey formid ".$this->intFormId." alias '".$arrForm['alias']."'");
    }

    /**
     * Callback ondelete_callback.
     */
    public function deleteFormdataDca(\DataContainer $dc, $undoId): void
    {
        if (!$dc->id) {
            return;
        }
        $this->intFormId = $dc->id;
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'do '.\Input::get('do').' FormId '.$this->intFormId.' this->intId '.$this->intId);
        $arrForm = \Database::getInstance()->prepare('SELECT * FROM tl_form WHERE id=?')->execute($dc->id)->fetchAssoc();
        $arrFormData = \Database::getInstance()->prepare('SELECT * FROM tl_formdata WHERE form=?')->execute($arrForm['title'])->fetchAssoc();

        $strFormKey = (!empty($arrForm['alias'])) ? $arrForm['alias'] : str_replace('-', '_', standardize($arrForm['title']));
        // remove formdata an formdatadetails
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'delete1 DELETE  FROM tl_formdata_details WHERE pid='.$arrFormData['id']);
        \Database::getInstance()->prepare('DELETE  FROM tl_formdata_details WHERE pid=?')->execute($arrFormData['id']);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'delete2 DELETE  FROM tl_formdata WHERE form='.$arrForm['title']);
        \Database::getInstance()->prepare('DELETE  FROM tl_formdata WHERE form=?')->execute($arrForm['title']);
        $this->Formdata->removeFromStoringForm($strFormKey);
        $this->updateConfig();
    }

    /**
     * Callback edit button.
     *
     * @return string
     */
    public function callbackEditButton($row, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
    {
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "title $title");
        $return = '';

        $strDcaKey = array_search($row['form'], $this->Formdata->arrFormsDcaKey, true);
        if ($strDcaKey) {
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "title $title dcakey find $strDcaKey");
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
        $arrStoringForms = $this->Formdata->arrStoringForms;
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'len arrStoringForms '.\count($arrStoringForms));
        $createNewForm = true;
        if (null === $arrForms) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'updateConfig aktuell schon gespeicherte arrStoringForms aktualisieren');
            $arrForms = $arrStoringForms;
            $createNewForm = false;          // nur aktuelle storingForms aktualisieren. Auch evtl. löschen
        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "updateConfig createNewForm $createNewForm len arrStoringForms ".\count($arrStoringForms));

        // Remove unused dca files
        $arrFiles = scan($this->rootDir.'/'.$this->vendorPath.'src/Resources/contao/dca', true);
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'scan dca '.$this->rootDir.'/'.$this->vendorPath.'src/Resources/contao/dca');
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'len dca arrfiles '.\count($arrFiles));
        //$files = \System::getContainer()->get('contao.resource_locator')->locate('src/Resources/contao/dca/*', null, false);
        //EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "len dca files ". count($files));

        foreach ($arrFiles as $strFile) {
            if ('fd_' === substr($strFile, 0, 3)) {
                if (empty($arrStoringForms) || !\in_array(str_replace('.php', '', substr($strFile, 3)), array_keys($arrStoringForms), true)) {
                    $objFile = new \File($this->vendorPath.'src/Resources/contao/dca/'.$strFile);
                    $objFile->delete();
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'dca File '.$strFile.' in vendor geloescht');
                }
            }
        }
        if (\strlen($this->cachePath) > 0) {      // cache vorhanden
            // Remove cached dca files
            if (is_dir($this->rootDir.'/'.$this->cachePath.'dca')) {
                $arrFiles = scan($this->rootDir.'/'.$this->cachePath.'dca', true);
                foreach ($arrFiles as $strFile) {
                    if ('fd_' === substr($strFile, 0, 3) || 'tl_formdata.php' === $strFile || 'tl_formdata_details.php' === $strFile) {
                        $objFile = new \File($this->cachePath.'dca/'.$strFile);
                        $objFile->delete();
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'dca File '.$strFile.' in cache geloescht');
                    }
                }
            }
        }
        // config/config.php neu erstellen
        // write new config to vendor
        $tplConfig = $this->newTemplate('efg_internal_config');
        $tplConfig->arrStoringForms = $arrStoringForms;
        $objConfig = new \File($this->vendorPath.'src/Resources/contao/config/config.php');

        $objConfig->write($tplConfig->parse());
        $objConfig->close();
        if (\strlen($this->cachePath) > 0) {      // cache vorhanden dann auch config.php im cache erneuern
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'configcache from '.$this->cachePath.'config/config.php');
            $objCache = new \File($this->cachePath.'config/config.php');
            $strconfcache = $objCache->getContent();
            $startpos = stripos($strconfcache, '// begin config efg');
            if (false === $startpos) {
            } else {
                $endpos = stripos($strconfcache, '// end config efg', $startpos);
                $pars = $tplConfig->parse();
                $startpospars = stripos($pars, '// begin config efg');
                $newcachestr = substr($strconfcache, 0, $startpos - 1)."\n".substr($pars, $startpospars)."\n".substr($strconfcache, $endpos + \strlen('// end config efg'));
                $objCache->write($newcachestr);
            }
            $objCache->close();
        }
        //$this->log('rewrite config.php in cache and vendor', __METHOD__, TL_GENERAL);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'rewrite cache config var/cache/'.$_ENV['APP_ENV'].'/contao/config/config.php');
        if (empty($arrStoringForms)) {
            \Message::addInfo('Cache bitte neu erzeugen');

            return; // keine Formulare vorhanden deren Daten gespeichert werden sollen
        }
        // languages/modules.php

        $arrModLangs = scan($this->rootDir.'/'.$this->vendorPath.'src/Resources/contao/languages');
        $arrLanguages = $this->getLanguages();

        foreach ($arrModLangs as $strModLang) { // über alle Sprachen in Vendor
            // Create language files
        if (\array_key_exists($strModLang, $arrLanguages)) {    // vendor Sprache in Sprachen vorhanden
            $strFile = $this->rootDir.'/'.$this->vendorPath.'src/Resources/contao/languages/'.$strModLang.'/tl_efg_modules.php';
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "languageFile Sprache $strModLang File ".$strFile);
            if (file_exists($strFile)) {
                include $strFile;        // ist das zugehoerige default Sprachefile wird dann im Template efg_internal_modules ausgewertet
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'include '.$strFile);
            }

            $tplMod = $this->newTemplate('efg_internal_modules');
            $tplMod->arrStoringForms = $arrStoringForms;
            $objMod = new \File($this->vendorPath.'src/Resources/contao/languages/'.$strModLang.'/modules.php');
            $objMod->write($tplMod->parse());
            $objMod->close();
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'neu erzeugt '.$this->vendorPath.'src/Resources/contao/languages/'.$strModLang.'/modules.php');
            if (\strlen($this->cachePath) > 0) {      // cache vorhanden dann modulefiles loeschen
                // Remove cached language files
                //$objCache = new \File($this->cachePath.'language/'.$strModLang.'/modules.php');
                //$objCache->delete();
                //$objCache = new \File($this->cachePath.'language/'.$strModLang.'/tl_formdata.php');
                //$objCache->delete();
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'modules cache from '.$this->cachePath.'languages/'.$strModLang.'/modules.php');
                $objCache = new \File($this->cachePath.'languages/'.$strModLang.'/modules.php');
                $strmodulescache = $objCache->getContent();
                $startpos = stripos($strmodulescache, '// begin modules efg');
                if (false === $startpos) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'no startpos modules cache from '.$this->cachePath.'languages/'.$strModLang.'/modules.php');
                } else {
                    $endpos = stripos($strmodulescache, '// end modules efg', $startpos);
                    $pars = $tplMod->parse();
                    $startpospars = stripos($pars, '// begin modules efg');
                    $newcachestr = substr($strmodulescache, 0, $startpos - 1)."\n".substr($pars, $startpospars)."\n".substr($strmodulescache, $endpos + \strlen('// end modules efg'));
                    $objCache->write($newcachestr);
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'rewrite cache languages var/cache/'.$_ENV['APP_ENV'].'/contao/languages/'.$strModLang.'/modules.php');
                }
                $objCache->close();
            }
            //$this->log('rewrite cache and vendor modules.php in '.$strModLang, __METHOD__, TL_GENERAL);
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'rewrite cache and vendor modules.php in '.$strModLang);
        }
        }
        //*/
        // dca/fd_FORMKEY.php  fd_... erzeugen und übernehmen der Formdata in fd_...
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'vor dca/fd_Formkey len arrForms '.\count($arrForms));
        if (\is_array($arrForms) && !empty($arrForms)) {
            foreach ($arrForms as $arrForm) /* erzeuge die eingabefelder zur form */
        {
            if (!empty($arrForm)) {
                // alle felder der form
                EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'Schleife Arrforms id '.$arrForm['id']);
                $arrForm = \Database::getInstance()->prepare('SELECT * FROM tl_form WHERE id=?')->execute($arrForm['id'])->fetchAssoc();

                $arrFields = [];
                $arrFieldNamesById = [];

                $arrSelectors = [];
                $arrPalettes = [];
                $strCurrentPalette = '';
                $strPreviousPalette = '';
                // Get all form fields of this form
                $arrFormFields = $this->Formdata->getFormFieldsAsArray($arrForm['id']);

                if (!empty($arrFormFields)) {
                    EfgLog::EfgwriteLog(debmedium, __METHOD__, __LINE__, 'Formfields gelesen und vorhanden FORM id:  '.$arrForm['id'].' title: '.$arrForm['title'].' Anzahl '.\count($arrFormFields));
                    foreach ($arrFormFields as $strFieldKey => $arrField) {
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'name '.$arrField['name'].' type '.$arrField['type']);
                        // Ignore not storable fields and some special fields like checkbox CC, fields of type password ...
                        if (!\in_array($arrField['type'], $this->arrFFstorable, true)
                            || ('checkbox' === $arrField['type'] && 'cc' === $strFieldKey)) {
                            continue;
                        }
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'formfieldType '.$arrField['formfieldType'].' conditionType '.$arrField['conditionType']);

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
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'strFieldKey '.$strFieldKey);
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
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'Formfields bearbeitet:'.$strFormKey.' anzahl fields '.\count($arrFields));
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
                if ($arrForm['sendConfirmationMail'] || (isset($arrForm['confirmationMailText']) && \strlen($arrForm['confirmationMailText']))) {
                    $blnBackendMail = true;
                }
                $tplDca->blnBackendMail = $blnBackendMail;
                // eigentlich sollt hier mal eindiff gemacht werden ob zwischen neuem und alten File ein Unterschied besteht
                // https://www.php.net/manual/de/function.xdiff-string-diff.php
                $objDca = new \File($this->vendorPath.'src/Resources/contao/dca/fd_'.$strFormKey.'.php');
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vor tplpars file '.$this->vendorPath.'src/Resources/contao/dca/fd_'.$strFormKey.'.php');
                $objDca->write($tplDca->parse());
                $objDca->close();
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'vendor geschrieben file '.$this->vendorPath.'src/Resources/contao/dca/fd_'.$strFormKey.'.php');
                if (isset($cachepath) && (\strlen($cachepath) > 0)) {      // cache vorhanden dann auch in cache schreiben
                    // ich weiss nicht ob das Screiben der Datei in den Cache genügt vielleicht ist danach noch ein include notwendig ???
                    $objDcaCache = new \File('var/cache/'.$_ENV['APP_ENV'].'/contao/dca/fd_'.$strFormKey.'.php');
                    $objDcaCache->write($tplDca->parse());
                    $objDcaCache->close();
                    //$this->log('dca rewrite in cache und vendor fd_'.$strFormKey.'.php', __METHOD__, TL_GENERAL);
                    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'dca rewrite vendor und cache'.$this->vendorPath.'src/Resources/contao/dca/fd_'.$strFormKey.'.php');
                }
            }
          }
        }

        // overall storingForms dca/fd_feedback.php  fd_... erzeugen und übernehmen der Formdata in fd_...
        // Get all form fields of all storing forms
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'vor dca/fd_Formkey len arrStoringForms '.\count($arrStoringForms));
        // siehe auch Kommentare bei arrforms
        if (!empty($arrStoringForms)) {
            $arrAllFields = [];
            $arrFieldNamesById = [];
            foreach ($arrStoringForms as $strFormKey => $arrForm) {
                // Get all form fields of this form
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'b) bearbeite arrStoringForms FORM id:  '.$arrForm['id'].' title: '.$arrForm['title']);

                $arrFormFields = $this->Formdata->getFormFieldsAsArray($arrForm['id']);
                if (!empty($arrFormFields)) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'b) arrFormFields da FORM id:  '.$arrForm['id'].' title: '.$arrForm['title']);
                    foreach ($arrFormFields as $strFieldKey => $arrField) {
                        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "b) arrFormFields da $strFieldKey type ".$arrField['formfieldType']);
                        // Ignore not storable fields and some special fields like checkbox CC, fields of type password ...
                        if (!\in_array($arrField['formfieldType'], $this->arrFFstorable, true)
                        || ('checkbox' === $arrField['formfieldType'] && 'cc' === $strFieldKey)
                        || ('condition' === $arrField['formfieldType'] && 'stop' === $arrField['conditionType'])
                        || ('cm_alternative' === $arrField['formfieldType'] && \in_array($arrField['cm_alternativeType'], ['cm_else', 'cm_stop'], true))) {
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
            $tplDca->arrFields = $arrAllFields;   // alle Felder von allen Formularen
            $tplDca->arrFieldNamesById = $arrFieldNamesById;

            $objDca = new \File($this->vendorPath.'src/Resources/contao/dca/fd_'.$strFormKey.'.php');
            $objDca->write($tplDca->parse());
            $objDca->close();
            //$this->log('dca rewrite '.$this->vendorPath.'src/Resources/contao/dca/fd_'.$strFormKey.'.php', __METHOD__, TL_GENERAL);  // PBD
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'dca rewrite '.$this->vendorPath.'src/Resources/contao/dca/fd_'.$strFormKey.'.php');
        }
        // Rebuild internal cache
        if (!$GLOBALS['TL_CONFIG']['bypassCache']) {
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
        $objTemplate = new \BackendTemplate($strTemplate);
        $objTemplate->folder = 'efg_co4';
        return $objTemplate;
    }
}
