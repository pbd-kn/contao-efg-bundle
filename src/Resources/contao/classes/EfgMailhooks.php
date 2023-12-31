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

namespace PBDKN\Efgco4\Resources\contao\classes;

class EfgMailhooks // Make sure this class name matches the first item in the above line of code
{
    public function __construct()
    {
    }

    /*
     * Ist das Hiddenfeld "sendto" vorhanden
     * so wird der Wert als zusätzlicher zu dem im Formular angegebenen Empfaenger eingetragen
     * Ist die im Formular angegeben Mailadresse = dummy@dummy.de so wird diese entfernt
     */

    public function prepareFormData(&$arrSubmitted, $arrLabels, $arrFields, $objForm): void
    {
        $debug = true;
        foreach ($arrLabels as $k => $v) {
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "arrLabels[$k]: $v");
        }
        foreach ($arrFields as $k => $v) {
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "arrFields[$k]: ".$v->name);
        }
        foreach ($arrSubmitted as $k => $v) {
            EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "arrSubmitted[$k]: ".$v);
        }
        //.return;

        $sendFormattedMail = $objForm->__get('sendFormattedMail');
        $sendViaEmail = $objForm->__get('sendViaEmail');
        $useSendto = $objForm->__get('useSendto');
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'useSendto: '.$useSendto);
        $defaultrecipient = '';
        if ($sendViaEmail) {
            $defaultrecipient = $objForm->__get('recipient');
        }
        if ($sendFormattedMail) {
            $defaultrecipient = $objForm->__get('formattedMailRecipient');
        }
        $sendto = '';
        if ($useSendto) {
            $sendto = $arrSubmitted['sendto'];
        }                // from Hiddenfield

        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "get default recipient $defaultrecipient sendto $sendto");
        if ('' !== $sendto) {
            // sendto als zusätzlicher Empfänger
            if ('' !== $defaultrecipient && 'dummy@dummy.de' !== $defaultrecipient) {
                if ($sendViaEmail) {
                    $objForm->__set('recipient', $defaultrecipient.','.$sendto);
                    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'Email recipient changed to '.$objForm->__get('recipient'));
                }
                if ($sendFormattedMail) {
                    $objForm->__set('formattedMailRecipient', $defaultrecipient.','.$sendto);
                    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'formatted Email recipient changed to '.$objForm->__get('formattedMailRecipient'));
                }
            } else {
                if ($sendViaEmail) {
                    $objForm->__set('recipient', $sendto);
                    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'Email recipient changed to '.$objForm->__get('recipient'));
                }
                if ($sendFormattedMail) {
                    $objForm->__set('formattedMailRecipient', $sendto);
                    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'formatted Email recipient changed to '.$objForm->__get('formattedMailRecipient'));
                }
            }
        }
        // Test ob confirmationMail (Bestätigungsmail) gesendet werden soll
        if (isset($arrSubmitted['kopie']) && 'ja' === $arrSubmitted['kopie']) {
            $objForm->__set('sendConfirmationMail', '1');
        } else {
            $objForm->__set('sendConfirmationMail', '');
        }
        EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'end prepareFormData');
    }
}
