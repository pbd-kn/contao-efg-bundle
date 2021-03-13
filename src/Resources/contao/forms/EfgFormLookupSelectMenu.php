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

namespace PBDKN\Efgco4\Resources\contao\forms;

/**
 * Class EfgFormLookupSelectMenu.
 *
 * Form field "select menu (DB)".
 * based on FormSelectMenu by Leo Feyer
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class EfgFormLookupSelectMenu extends \Widget
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
            case 'mSize':
                if ($this->multiple) {
                    $this->arrAttributes['size'] = $varValue;
                }
                break;

            case 'efgLookupOptions':
                $this->import('Formdata');
                $this->arrConfiguration['efgLookupOptions'] = $varValue;
                $arrOptions = $this->Formdata->prepareWidgetOptions($this->arrConfiguration);
                $this->arrOptions = $arrOptions;
                break;

            case 'multiple':
                if (\strlen($varValue)) {
                    $this->arrAttributes[$strKey] = 'multiple';
                }
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
     * Generate the widget and return it as string.
     *
     * @return string
     */
    public function generate()
    {
        $strOptions = '';
        $strClass = 'select';
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
        // .. equivalent,  if linked from event reader or events list page
        if ('tl_calendar_events' === $strLookupTable && (strpos($strReferer, '/event-reader/events/') || strpos($strReferer, '&events='))) {
            if (1 === \count($this->arrOptions)) {
                $this->varValue = [$this->arrOptions[0]['value']];
                $blnSingleEvent = true;
            }
        }

        if ($this->multiple) {
            $this->strName .= '[]';
            $strClass = 'multiselect';
        }

        // Add empty option (XHTML) if there are none
        if (empty($this->arrOptions) && !$blnSingleEvent) {
            $this->arrOptions = [['value' => '', 'label' => '-']];
        }
        foreach ($this->arrOptions as $i => $arrOption) {
            $selected = '';
            if ((\is_array($this->varValue) && \in_array($arrOption['value'], $this->varValue, true) || $this->varValue === $arrOption['value'])) {
                $selected = ' selected="selected"';
            }

            $strOptions .= sprintf('<option value="%s"%s>%s</option>',
                $arrOption['value'],
                $selected,
                $arrOption['label']);

            // render as checked radio if used as lookup on tl_calendar_events and only one event available
            if ('tl_calendar_events' === $strLookupTable && $blnSingleEvent) {
                $selected = ' checked="checked"';
                $strOptions = sprintf('<span><input type="radio" name="%s" id="opt_%s" class="radio" value="%s"%s%s <label for="opt_%s">%s</label></span>',
                    $this->strName.((\count($this->arrOptions) > 1) ? '[]' : ''),
                    $this->strId.'_'.$i,
                    $arrOption['value'],
                    $selected,
                    $this->strTagEnding,
                    $this->strId.'_'.$i,
                    $arrOption['label']);

                return sprintf('<div id="ctrl_%s" class="radio_container%s">%s</div>',
                    $this->strId,
                    (\strlen($this->strClass) ? ' '.$this->strClass : ''),
                    $strOptions).$this->addSubmit();
            }
        }

        return sprintf('<select name="%s" id="ctrl_%s" class="%s%s"%s>%s</select>',
            $this->strName,
            $this->strId,
            $strClass,
            (\strlen($this->strClass) ? ' '.$this->strClass : ''),
            $this->getAttributes(),
            $strOptions).$this->addSubmit();
    }
}
