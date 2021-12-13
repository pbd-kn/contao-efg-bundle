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
 * Class EfgFormLookupRadio.
 *
 * Form field "radio (DB)".
 * based on FormRadio by Leo Feyer
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class EfgFormLookupRadio extends \Widget
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
     * Generate the widget and return it as string.
     *
     * @return string
     */
    public function generate()
    {
        $strOptions = '';

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

            /*
                        $strOptions .= sprintf('<span><input type="radio" name="%s" id="opt_%s" class="radio" value="%s"%s%s <label for="opt_%s">%s</label></span>',
                            $this->strName,
                            $this->strId.'_'.$i,
                            $arrOption['value'],
                            ((is_array($this->varValue) && in_array($arrOption['value'] , $this->varValue) || $this->varValue == $arrOption['value'] || $this->varValue == $arrOption['label']) ? ' checked="checked"' : ''),
                            $this->strTagEnding,
                            $this->strId.'_'.$i,
                            $arrOption['label']);
             meiner Ansicht nach muss als Option das label genommen werden scheint aber bei der Radiobox zu funktionieren !!
            */
            $strOptions .= sprintf('<span><input type="radio" name="%s" id="opt_%s" class="radio" value="%s"%s%s <label for="opt_%s">%s</label></span>',
                $this->strName.((\count($this->arrOptions) > 1) ? '[]' : ''),
                $this->strId.'_'.$i,
                $arrOption['label'],
                $checked,
                $this->strTagEnding,
                $this->strId.'_'.$i,
                $arrOption['label']);
        }

        return sprintf('<div id="ctrl_%s" class="radio_container%s">%s</div>',
            $this->strId,
            (isset($this->strClass) && \strlen($this->strClass) ? ' '.$this->strClass : ''),
            $strOptions).$this->addSubmit();
    }
}
