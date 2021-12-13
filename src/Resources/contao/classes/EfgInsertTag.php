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

/*
 * fügt eine Wert aus einem Efg Formular ein. Die daten müssen in tl_form gespeichert werden
 * Auswahlfeld ist das efgaliasfeld, das im Formulat angegeben ist
 * Parameter
 * Liste: Aliasname des formulars (input)
 * value: Wert den das Alias Feldes haben soll (input)
 * column: Feldname der eingesetzt werden soll (value von column = result)
 * format: optional wenn vorhanden wir der gefundene Wert damit formatiert
 */

namespace PBDKN\Efgco4\Resources\contao\classes;

class EfgInsertTag extends \Contao\Frontend
{
    /**
     * InsertTags für Efg Modul
     * {{efg_insert::Liste::value::column(::format)}}     insert Wert aus formular
     * {{efg_insertd::Liste::value::column(::format)}}    insert Wert aus formular (debugversion)).
     */
    /* database doku
     * https://de.contaowiki.org/Datenbank_Klasse_verwenden
     */
    public function Efg_InsertTags($strTag)
    {
        $debug = false;
        $this->Date = new \Date(date('Ymd'), 'Ymd');
        $heute = $this->Date->dayBegin;     // Tagesbeginn HEUTE
        $resstr = '';
        $arrTag = explode('::', $strTag);

        if ('efg_insert' !== $arrTag[0]) {
            if ('efg_insertd' === $arrTag[0]) {
                $debug = true;
            } else {
                return false;
            }
        }
        if ($debug) {
            $resstr .= $strTag.'<br>';
        }
        $objForm = $this->Database->execute("SELECT id,title,alias,efgAliasField FROM tl_form where alias='$arrTag[1]'");
        if (0 === $objForm->numRows) {
            if ($debug) {
                $resstr .= "EFG Formular $arrTag[1] existiert nicht.";
            } else {
                $resstr = '';
            }

            return $resstr;
        }
        $tableID = $objForm->id;
        $tableTitle = $objForm->title;
        $tablealias = $objForm->alias;
        $tableEfgAliasField = $objForm->efgAliasField;
        if ($debug) {
            // 1. Datensatz ausgeben
            $resstr .= 'numRows '.\count($objForm)." tableID $tableID<br>";
            $resstr .= "Title $tableTitle<br>";
            $resstr .= "tablealias $tablealias<br>";
            $resstr .= "tableEfgAliasField $tableEfgAliasField<br>";
            // alle weiteren ausgeben
            while ($objForm->next()) {
                $resstr .= '------id '.$result->title.'<br';
            }
        }
        // besorge Spaltenname
        $objSpalte = @$this->Database->execute("SELECT name FROM tl_form_field where label='".$arrTag[3]."'");
        if (0 === $objSpalte->numRows) {
            if ($debug) {
                $resstr .= "Label $arrTag[3] existiert nicht in tl_form_field.";
            } else {
                $resstr = '';
            }

            return $resstr;
        }
        $Spalte = $objSpalte->name;
        // besorge pid aus dem aliasfeld
        $objPid = @$this->Database->execute("SELECT pid FROM tl_formdata_details where ff_name='$tableEfgAliasField' AND value='$arrTag[2]'");
        if (0 === $objPid->numRows) {
            if ($debug) {
                $resstr .= "Alias $arrTag[2] existiert nicht in Formular $arrTag[1].";
            } else {
                $resstr = '';
            }

            return $resstr;
        }
        $pid = $objPid->pid;
        if ($debug) {
            $resstr .= 'numRows Article '.\count($objPid)." pid $pid<br>";
        }
        $objVal = @$this->Database->execute("SELECT value FROM tl_formdata_details where ff_name='$Spalte' AND pid='$pid'");
        $val = $objVal->value;
        if ($debug) {
            $resstr .= "$Spalte ($arrTag[3])  val $val<br>";
        }
        if (\count($arrTag) > 4) {
            $resstr .= sprintf($arrTag[4], $val);
        } else {
            $resstr .= $val;
        }

        return "$resstr";
    }
}
