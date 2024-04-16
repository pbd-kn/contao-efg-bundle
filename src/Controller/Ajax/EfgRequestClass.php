<?php
// EfgRequestClass.php
// einzele Ajax Request für efg. z.B csv-import
// 'data' => $html,  gerendete Info
// 'debug'=>$debug   evtl. debuginfo
declare(strict_types=1);

namespace Pbdkn\Efgco4\Controller\Ajax;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\DcaUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DoctrineDBALDriverException;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class EfgRequestClass extends AbstractController
{
    private ContaoFramework $framework;
    private Connection $connection; 
    //private InsertTagParser $insertTagParser; 
    //private TranslatorInterface $translator;
    private CgiUtil $cgiUtil; 
    private EfgUtil $efgUtil; 


        
    public function __construct(
      ContaoFramework $framework,
      Connection $connection,
      //InsertTagParser $insertTagParser,
      //TranslatorInterface $translator,
      CgiUtil $cgiUtil,
      EfgUtil $efgUtil      
      )
    {
        $this->framework = $framework;
        $this->connection = $connection;
        //$this->insertTagParser=$insertTagParser; 
        //$this->translator=$translator;
        $this->cgiUtil=$cgiUtil;
        $this->efgUtil=$efgUtil;
        //parent::__construct($strTable);
    } 
    /**
     * @throws \Exception
     * @Route("/efg/reloadFiletree/{aktion}/{strtable}/{name}/{value}", 
     * name="EfgRequestClass::class\reloadFiletree", 
     * defaults={"_scope" = "frontend"})
     */


  public function reloadFiletree(string $aktion, string $strtable, string $name,string $value)
  {
    // Direkter Zugriff auf den DataContainer für tl_example_table
        return new Response('pbd reloadFiletree EfgRequestClass');
    $html="";
    $debug="";
    $errortxt="";
    if (!isset($GLOBALS['TL_DCA'][$strtable])) {
      $errortxt.="Kein Datacontainer dc zu Tabelle $strtable<br>";
      $errortxt = utf8_encode($errortxt);
      return new JsonResponse(['data' => $html,'error'=>$errortxt, 'debug'=>$debug]); 
    }
    $dc = DcaUtil::getDca($strtable);

// Jetzt kannst du auf die Konfigurationen und Felder des DataContainers zugreifen
//$config = $dc['config'];
//$fields = $dc['fields'];
    $strField = $strFieldName = $name;

    // Handle the keys in "edit multiple" mode
    if ('editAll' === $aktion) {
      $intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
      $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
    }

    if (!\in_array($strField, $dc['arrBaseFields'], true) && !\in_array($strField, $dc['arrDetailFields'] ?? [], true)) {
      $this->log('Field "'.$strField.'" does not belong to form data table', 'Formdata executePostActions()', TL_ERROR);
      $errortxt.="'Field .$strField.' existiert nicht in  form data table $strField<br>";
      $errortxt = utf8_encode($errortxt);
      return new JsonResponse(['data' => $html,'error'=>$errortxt, 'debug'=>$debug]); 
    }

/* das verstehe ich noch nicht was ist das für eine id und was wird da gelesen
    $objRow = \Database::getInstance()->prepare('SELECT * FROM '.$dc->table.' WHERE id=?')->execute($intId);

    // The record does not exist
    if ($objRow->numRows < 1) {
       $this->log('A record with the ID "'.$intId.'" does not exist in table "'.$dc->table.'"', 'Ajax executePostActions()', TL_ERROR);
       header('HTTP/1.1 400 Bad Request');
       exit('Bad Request');
    }
*/

    $varValue = $value;      // ist wohl der Dateiname
    $strKey = 'fileTree';

    // Convert the selected values
    if ('' !== $varValue) {
      $varValue = trimsplit("\t", $varValue);
      // Automatically add resources to the DBAFS
      if ('fileTree' === $strKey) {     // mehrfach selection ??
        foreach ($varValue as $k => $v) {
          $varValue[$k] = \Dbafs::addResource($v)->uuid;
        }
      }

      $varValue = serialize($varValue);
    }

    // Set the new value
    /* siehe oben wg. verstaendnis
    if (\in_array($strField, $dc['arrBaseFields'] ?? [], true) || \in_array($strField, $dc['arrDetailFields'] ?? [], true)) {
      $objRow->$strField = $varValue;
      $arrAttribs['activeRecord'] = $objRow;
    }
    */

    $arrAttribs['id'] = $strFieldName;
    $arrAttribs['name'] = $strFieldName;
    $arrAttribs['value'] = $varValue;
    $arrAttribs['strTable'] = $dc['table'];
    $arrAttribs['strField'] = $strField;

    $objWidget = new $GLOBALS['BE_FFL'][$strKey]($arrAttribs);
    $html.=$objWidget->generate().'<script>handleEfgFileselectorButton();</script>';
    $html = utf8_encode($html);
	return new JsonResponse(['dir'=>__DIR__,'data' => $html,'debug'=>$debug]); 
  }
}