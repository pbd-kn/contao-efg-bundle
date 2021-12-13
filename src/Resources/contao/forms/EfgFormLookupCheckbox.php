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

namespace PBDKN\Efgco4\Resources\contao\forms;

use PBDKN\Efgco4\Resources\contao\classes\EfgLog;

/**
 * Class EfgFormLookupCheckbox.
 *
 * Form field "checkbox (DB)".
 * based on FormCheckbox by Leo Feyer
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class EfgFormLookupCheckbox extends \Widget
{
    /**
     * Submit user input.
     *
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'form_widget';

    /**
     * Options.
     *
     * @var array
     */
    protected $arrOptions = [];

    /**
     * Add specific attributes.
     *
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue): void
    {
        switch ($strKey) {
            case 'efgLookupOptions':
                $this->import('Formdata');
                $this->arrConfiguration['efgLookupOptions'] = $varValue;
                $arrOptions = $this->Formdata->prepareWidgetOptions($this->arrConfiguration);
                $this->arrOptions = $arrOptions;
                break;

            case 'mandatory':
                $this->arrConfiguration['mandatory'] = $varValue ? true : false;
                break;

            case 'rgxp':
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Return a parameter.
     *
     * @throws Exception
     *
     * @return string
     */
    public function __get($strKey)
    {
        switch ($strKey) {
            case 'options':
                return $this->arrOptions;
                break;

            default:
                return parent::__get($strKey);
                break;
        }
    }

    /**
     * Check options if the field is mandatory.
     */
    public function validate(): void
    {
//            EfgLog::setEfgDebugmode('form');

        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'TL_MODE '.TL_MODE);
        $mandatory = $this->mandatory;
        $options = $this->getPost($this->strName);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'this->strName '.$this->strName.' options '.$options);
        foreach ($options as $k => $v) {
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "options[$k]: $v");
        }
        // Check if there is at least one value
        if ($mandatory && \is_array($options)) {
            foreach ($options as $option) {
                if (\strlen($option)) {
                    $this->mandatory = false;
                    break;
                }
            }
        }

        $varInput = $this->validator($options);
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'varInput '.$varInput);

        if (!$this->hasErrors()) {
            $this->varValue = $varInput;
        }

        // Reset the property
        if ($mandatory) {
            $this->mandatory = true;
        }

        // Clear result if nothing has been submitted
        if (!isset($_POST[$this->strName])) {
            $this->varValue = '';
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'result this->varValue '.$this->varValue);
    }

    /**
     * Generate the widget and return it as string.
     *
     * @return string
     */
    public function generate()
    {
        $strOptions = '';
        $strReferer = $this->getReferer();
        $arrLookupOptions = deserialize($this->arrConfiguration['efgLookupOptions']);
        $strLookupTable = substr($arrLookupOptions['lookup_field'], 0, strpos($arrLookupOptions['lookup_field'], '.'));
        $blnSingleEvent = false;

        // if used as lookup on table tl_calendar_events and placed on events detail page
        if ('tl_calendar_events' === $strLookupTable && \strlen(\Input::get('events'))) {
            if (1 === \count($this->arrOptions)) {
                $this->varValue = [$this->arrOptions[0]['value']];
                $blnSingleEvent = true;
            }
        }
        // .. equivalent,  if linked from event reader page
        if ('tl_calendar_events' === $strLookupTable && (strpos($strReferer, '/event-reader/events/') || strpos($strReferer, '&events='))) {
            if (1 === \count($this->arrOptions)) {
                $this->varValue = [$this->arrOptions[0]['value']];
                $blnSingleEvent = true;
            }
        }
        EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'this->varValue '.$this->varValue);

        foreach ($this->arrOptions as $i => $arrOption) {
            $checked = '';
            EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "this->arrOptions[$i]: $arrOption");
            if (\is_array($arrOption)) {
                foreach ($arrOption as $k => $v) {
                    EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, "arrOption[$k]: $v");
                }
            }

            if ((\is_array($this->varValue) && \in_array($arrOption['value'], $this->varValue, true) || $this->varValue === $arrOption['value'] || $this->varValue === $arrOption['label'])) {
                $checked = ' checked="checked"';
                EfgLog::EfgwriteLog(debfull, __METHOD__, __LINE__, 'checked set');
            }
            /*  meiner Ansicht nach muss als Option das label genommen werden
                        $strOptions .= sprintf('<span><input type="checkbox" name="%s" id="opt_%s" class="checkbox" value="%s"%s%s <label for="opt_%s">%s</label></span>',
                            $this->strName.((\count($this->arrOptions) > 1) ? '[]' : ''),
                            $this->strId.'_'.$i,
                            $arrOption['value'],
                            $checked,
                            $this->strTagEnding,
                            $this->strId.'_'.$i,
                            $arrOption['label']);
            */
            $strOptions .= sprintf('<span><input type="checkbox" name="%s" id="opt_%s" class="checkbox" value="%s"%s%s <label for="opt_%s">%s</label></span>',
                $this->strName.((\count($this->arrOptions) > 1) ? '[]' : ''),
                $this->strId.'_'.$i,
                $arrOption['label'],
                $checked,
                $this->strTagEnding,
                $this->strId.'_'.$i,
                $arrOption['label']);

            // render as checked radio if used as lookup on tl_calendar_events and only one event available
            if ('tl_calendar_events' === $strLookupTable && $blnSingleEvent) {
                $strOptions = sprintf('<span><input type="radio" name="%s" id="opt_%s" class="radio" value="%s"%s%s <label for="opt_%s">%s</label></span>',
                    $this->strName.((\count($this->arrOptions) > 1) ? '[]' : ''),
                    $this->strId.'_'.$i,
                    $arrOption['value'],
                    $checked,
                    $this->strTagEnding,
                    $this->strId.'_'.$i,
                    $arrOption['label']);
            }
        }

        return sprintf('<div id="ctrl_%s" class="checkbox_container%s">%s</div>',
            $this->strId,
            (isset($this->strClass) && \strlen($this->strClass) ? ' '.$this->strClass : ''),
            $strOptions).$this->addSubmit();
    }
}
