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

namespace PBDKN\Efgco4\Resources\contao\classes;

/**
 * Class FormdataComments.
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class FormdataComments
{
    /**
     * List a particular record.
     *
     * @param array
     *
     * @return string
     */
    public function listComments($arrRow)
    {
        $strRet = '';

        $objParent = \Database::getInstance()->prepare('SELECT `id`, `form`, `alias`  FROM tl_formdata WHERE id=?')
            ->execute($arrRow['parent'])
        ;

        if ($objParent->numRows) {
            $strRet .= ' ('.$objParent->form;

            if (\strlen($objParent->alias)) {
                $strRet .= ' - '.$objParent->alias;
            }
            $strRet .= ')';
        }

        return $strRet;
    }
}
