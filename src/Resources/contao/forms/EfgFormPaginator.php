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
 * Class EfgFormPaginator.
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class EfgFormPaginator extends \Widget
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'form_paginator';

    /**
     * Add specific attributes.
     *
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue): void
    {
        switch ($strKey) {
            case 'singleSRC':
                $this->arrConfiguration['singleSRC'] = $varValue;
                break;

            case 'imageSubmit':
                $this->arrConfiguration['imageSubmit'] = $varValue ? true : false;
                break;

            case 'name':
                $this->arrAttributes['name'] = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Validate input and set value.
     */
    public function validate(): void
    {
    }

    /**
     * Generate the widget and return it as string.
     *
     * @return string
     */
    public function generate()
    {
        global $objPage;

        if ('html5' === $objPage->outputFormat) {
            $blnIsHtml5 = true;
        }

        $blnSwitchOrder = (bool) $this->efgSwitchButtonOrder;

        $strButtonBack = '';
        $strButtonSubmit = '';

        if ($this->efgAddBackButton && (($this->formTotalPages > 1 && $this->formActivePage > 1) || TL_MODE === 'BE')) {
            if ($this->efgBackImageSubmit && '' !== $this->efgBackSingleSRC) {
                $objFileModel = \FilesModel::findById($this->efgBackSingleSRC);

                $strButtonBack .= sprintf('<input type="image"%s src="%s" id="ctrl_%s_back" class="submit back%s" alt="%s" title="%s" value="%s"%s%s',
                    ($this->formActivePage ? ' name="FORM_BACK"' : ''),
                    $objFileModel->path,
                    $this->strId,
                    (\strlen($this->strClass) ? ' '.$this->strClass : ''),
                    specialchars($this->efgBackSlabel),
                    specialchars($this->efgBackSlabel),
                    specialchars('submit_back'),
                    $this->getAttributes(),
                    $this->strTagEnding);
            } else {
                $strButtonBack .= sprintf('<input type="submit"%s id="ctrl_%s_back" class="submit back%s" value="%s"%s%s',
                    ($this->formActivePage ? ' name="FORM_BACK"' : ''),
                    $this->strId,
                    (\strlen($this->strClass) ? ' '.$this->strClass : ''),
                    specialchars($this->efgBackSlabel),
                    $this->getAttributes(),
                    $this->strTagEnding);
            }
        }

        if ($this->imageSubmit && '' !== $this->singleSRC) {
            $objFileModel = \FilesModel::findById($this->singleSRC);

            $strButtonSubmit .= sprintf('<input type="image"%s src="%s" id="ctrl_%s" class="submit next%s" alt="%s" title="%s" value="%s"%s%s',
                ($this->formActivePage ? ' name="FORM_NEXT"' : ''),
                $objFileModel->path,
                $this->strId,
                (\strlen($this->strClass) ? ' '.$this->strClass : ''),
                specialchars($this->slabel),
                specialchars($this->slabel),
                specialchars('submit_next'),
                $this->getAttributes(),
                $this->strTagEnding);
        } else {
            $strButtonSubmit .= sprintf('<input type="submit"%s id="ctrl_%s" class="submit next%s" value="%s"%s%s',
                ($this->formActivePage ? ' name="FORM_NEXT"' : ''),
                $this->strId,
                (\strlen($this->strClass) ? ' '.$this->strClass : ''),
                specialchars($this->slabel),
                $this->getAttributes(),
                $this->strTagEnding);
        }

        return ($blnSwitchOrder) ? $strButtonSubmit.$strButtonBack : $strButtonBack.$strButtonSubmit;
    }
}
