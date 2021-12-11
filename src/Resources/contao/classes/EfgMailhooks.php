<?php
/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace PBDKN\Efgco4\Resources\contao\classes; 
use PBDKN\Efgco4\Resources\contao\classes\EfgLog; 

class efgMailHooks // Make sure this class name matches the first item in the above line of code
{
  public function __construct() {}

  /*
   * Ist das Hiddenfeld "sendto" vorhanden
   * so wird der Wert als zusätzlicher zu dem im Formular angegebenen Empfaenger eingetragen
   * Ist die im Formular angegeben Mailadresse = dummy@dummy.de so wird diese entfernt
   */    

  public function prepareFormData(&$arrSubmitted, $arrLabels,$arrFields,$objForm)
  {
    $debug = true;
    foreach($arrLabels as $k=>$v) {EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "arrLabels[$k]: $v");}
    foreach($arrFields as $k=>$v) {EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "arrFields[$k]: ".$v->name);}
    foreach($arrSubmitted as $k=>$v) {EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "arrSubmitted[$k]: ".$v);}
    //.return;

    $sendFormattedMail =  $objForm->__get("sendFormattedMail");
    $sendViaEmail =  $objForm->__get("sendViaEmail");
    $useSendto = $objForm->__get("useSendto");
    $defaultrecipient = "";
    if ($sendViaEmail)  {
      $defaultrecipient = $objForm->__get("recipient");
    }
    if ($sendFormattedMail)  {
      $defaultrecipient = $objForm->__get("formattedMailRecipient");
    }
    $sendto="";
    if ($useSendto) $sendto = $arrSubmitted['sendto'];                // from Hiddenfield 

    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "get default recipient $defaultrecipient sendto $sendto");
    if ($sendto != "") {
      // sendto als zusätzlicher Empfänger
      if ($defaultrecipient != "" && $defaultrecipient != "dummy@dummy.de") {
        if ($sendViaEmail) {
          $objForm->__set("recipient",  $defaultrecipient . "," . $sendto);
          EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "Email recipient changed to " . $objForm->__get("recipient"));
        }
        if ($sendFormattedMail) {
          $objForm->__set("formattedMailRecipient",  $defaultrecipient . "," . $sendto);
          EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "formatted Email recipient changed to " . $objForm->__get("formattedMailRecipient"));
        }
      } else {
        if ($sendViaEmail) {
          $objForm->__set("recipient",  $sendto);
          EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "Email recipient changed to " . $objForm->__get("recipient"));
        }
        if ($sendFormattedMail) {
          $objForm->__set("formattedMailRecipient",  $sendto);
          EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, "formatted Email recipient changed to " . $objForm->__get("formattedMailRecipient"));
        }
      }
    }
    // Test ob confirmationMail (Bestätigungsmail) gesendet werden soll
    if ($arrSubmitted['kopie'] == 'ja') {
      $objForm->__set("sendConfirmationMail",'1');
    } else {
      $objForm->__set("sendConfirmationMail","");
    }
    EfgLog::EfgwriteLog(debsmall, __METHOD__, __LINE__, 'end prepareFormData');
  }
}
?>