<?php
/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 *
 * EFG Log - BE/FE
 *
 * nach Vorlage ModuleVisitorLog (BugBuster)
 * @copyright  PBD 2021..
 * @author     PBD
 * @Mail       pb-contao@gmx.de
 * @licence    LGPL
 * @filesource
 * @see	       https://github.com/pbd-kn/contao-efg-bundle
 */

namespace PBDKN\Efgco4\Resources\contao\classes;
use Contao\StringUtil;

/**
 * Class EfgLog
 *
 * @copyright  PBD 2021..
 * @author     PBD 
 * @license    LGPL
 */
class EfgLog
{

    protected static $cnt=0;
    protected static $debFormKey;                    // Formularkey fuer den Altuellen Debug
    protected static $uniqid;
    protected static $myefgdebuglevel;
    /* debugkeys binary key 
               => array('0' => kein debug, 
                        '1' => 'small', 
                        '01' => 'medium' = 2  small + medium (3)
                        '001' => 'full' = 4   small + medium + full (7)
                        '0001 '=>'emailsmall= 8  (8)
                        '00001'=>'emailmedium' = 16 (24)
                        '000001'=>'emailfull' =32 (56)
    */
    static public function setefgDebugmode($key)
	{
		if (!isset(self::$debFormKey) || $key != self::$debFormKey) 
		{
			// Get all forms marked to store data
			$objForms = \Database::getInstance()->prepare("SELECT alias,title,efgDebugMode FROM tl_form WHERE storeFormdata=?")
				->execute("1");

			while ($objForms->next())
			{  // suche Form
                if ($key == 'form') {    // bei neuer form ist der key form nimm den höchsten wert
                  if (!isset(self::$myefgdebuglevel) || $objForms->efgDebugMode > self::$myefgdebuglevel) {
                    self::$myefgdebuglevel=$objForms->efgDebugMode;
                    self::$debFormKey=$key;
                    $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
                    self::$uniqid = $arrUniqid[1];
//\System::log("PBD EfgwriteLog set $myefgdebuglevel '" . self::$myefgdebuglevel . "' key $key", __METHOD__, TL_GENERAL);
                    continue;
                  } 
                }
				$strFormKey = (!empty($objForms->alias)) ? $objForms->alias : str_replace('-', '_', standardize($objForms->title));
                if ($strFormKey==$key)  {
                  self::$myefgdebuglevel=$objForms->efgDebugMode;
                  self::$debFormKey=$key;
                  $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
                  self::$uniqid = $arrUniqid[1];
//\System::log("PBD EfgwriteLog set $myefgdebuglevel '" . self::$myefgdebuglevel . "' key $key", __METHOD__, TL_GENERAL);
                  break;
                } 
			}
            if (!isset(self::$myefgdebuglevel)) { 
//\System::log("PBD EfgwriteLog reset $myefgdebuglevel key $key", __METHOD__, TL_GENERAL);
              unset (self::$myefgdebuglevel);    // key not found   reset values
              unset(self::$debFormKey);
              unset(self::$uniqid);
            }
       }
	}

    public static function EfgwriteLog($level,$method, $line, $value)
    {
      if (!isset(self::$debFormKey)) { 
        return;
      }
      $method = trim($method);   
      $arrNamespace = StringUtil::trimsplit('::', $method);
      $arrClass =  StringUtil::trimsplit('\\', $arrNamespace[0]);
      $vclass = $arrClass[\count($arrClass)-1] . "::" . $arrNamespace[1] ; // class that will write the log

        if (\is_array($value))
        {
            $value = print_r($value, true);
        }
        if (($level & self::$myefgdebuglevel) == $level) {
          //self::logMessage(sprintf('[level: %s] [$myefgdebuglevel %s] [and %s] ', $level, self::$myefgdebuglevel, self::$myefgdebuglevel&$level), 'efg_debug');
          self::logMessage(sprintf('[%s] [%s] [%s] [%s] %s', self::$uniqid,$level, '(' . $vclass . ')', $line, 'PBD ' . $value), 'efg_debug');
        }
/*   
      $handle = fopen('C:\wampneu\www\co4\websites\co4raw\var\logs\myLog.log', 'a+');
      if ($level & self::$myefgdebuglevel) {
        fwrite ( $handle , self::$cnt . " [self::myefgdebuglevel: " . self::$myefgdebuglevel . " level [$level] $method $line $value\n");
      } else {
        fwrite ( $handle , self::$cnt . " !!!! Not [$level] $method $line $value\n");
      }
      fclose ( $handle );
        self::$cnt++;
*/
    }
    /**
     * Write in log file, if debug is enabled
     *
     * @param string  $method
     * @param integer $line
     */
    public static function EfgwriteLog1($level,$method, $line, $value)
    {
        $method = trim($method);       
\System::log("PBD EfgwriteLog cnt " . self::$cnt . " level $level method $method  first '" . $GLOBALS['efgdebug']['debug']['first'] . "'", __METHOD__, TL_GENERAL);
        if (!(strpos ( $method, '## START ##', 0 ) === false))        // Start des Debugs
        {
            $arr = explode("::", $method);
            if (count($arr) < 2 ) return;              // kein debuglevel angegeben
            if (!isset($GLOBALS['efgdebug']['debug']['first'])) 
            {
                $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
                $GLOBALS['efgdebug']['debug']['first'] = $arrUniqid[1];
                $GLOBALS['efgdebug']['debug']['efgdebuglevel'] = $arr[1];
\System::log("PBD EfgwriteLog set new first '" . $GLOBALS['efgdebug']['debug']['first'] . "' efgdebuglevel '" . $GLOBALS['efgdebug']['debug']['efgdebuglevel'] . "'", __METHOD__, TL_GENERAL);
                if ($level & $GLOBALS['efgdebug']['debug']['efgdebuglevel']) {
\System::log("PBD EfgwriteLog to file level $level method $method line $line value $value efgdebuglevel '" . $GLOBALS['efgdebug']['debug']['efgdebuglevel'] . "'", __METHOD__, TL_GENERAL);
                  self::logMessage(sprintf('[%s] [%s] [%s] [%s] %s', $GLOBALS['efgdebug']['debug']['first'],$level, $method, $line, $value), 'efg_debug');
                }
                return;
            }
            else
            {
\System::log("PBD EfgwriteLog first schon gesetzt first '" . $GLOBALS['efgdebug']['debug']['first'] . "'", __METHOD__, TL_GENERAL);
                return;
            }
        }

        $arrNamespace = StringUtil::trimsplit('::', $method);
        $arrClass =  StringUtil::trimsplit('\\', $arrNamespace[0]);
        $vclass = $arrClass[\count($arrClass)-1]; // class that will write the log

        if (\is_array($value))
        {
            $value = print_r($value, true);
        }
\System::log("PBD EfgwriteLog no start level $level method $method line $line efgdebuglevel '" . $GLOBALS['efgdebug']['debug']['efgdebuglevel'] . "'", __METHOD__, TL_GENERAL);
  
        //if ($level & $GLOBALS['efgdebug']['debug']['efgdebuglevel']) {
\System::log("PBD EfgwriteLog to file start level $level efgdebuglevel '" . $GLOBALS['efgdebug']['debug']['efgdebuglevel'] . "'", __METHOD__, TL_GENERAL);
          self::logMessage(sprintf('[%s] [%s] [%s] [%s] %s', $GLOBALS['efgdebug']['debug']['first'],$level, $method, $line, '('.$vclass.')'.$value), 'efg_debug');
        //}
        return;
/*
        switch ($vclass)
        {
            case "ModuleVisitorsTag":
                if ($GLOBALS['visitors']['debug']['tag'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "ModuleVisitorChecks":
                if ($GLOBALS['visitors']['debug']['checks'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "ModuleVisitorReferrer":
                if ($GLOBALS['visitors']['debug']['referrer'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "ModuleVisitorSearchEngine":
                if ($GLOBALS['visitors']['debug']['searchengine'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "FrontendVisitors":
                if ($GLOBALS['visitors']['debug']['screenresolutioncount'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "VisitorsFrontendController":
                if ($GLOBALS['visitors']['debug']['tag']) //@todo temporär, eigene Regel notwendig  
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            default:
                self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $method, $line, '('.$vclass.')'.$value), 'visitors_debug');
                break;
        }
*/ 
    }

    /**
     * Wrapper for old log_message
     * 
     * @param string $strMessage
     * @param string $strLogg
     */
    public static function logMessage($strMessage, $strLog=null)
    {
        $env = $_SERVER['APP_ENV'] ?? 'prod';

        if ($strLog === null)
        {
            $strLog = $env . '-' . date('Y-m-d') . '.log';
        }
        else 
        {
            $strLog = $env . '-' . date('Y-m-d') . '-' . $strLog . '.log';
        }

        $strLogsDir = null;

        if (($container = \System::getContainer()) !== null)
        {
            $strLogsDir = $container->getParameter('kernel.logs_dir');
        }

        if (!$strLogsDir)
        {
            $strLogsDir = TL_ROOT . '/var/logs';
        }

        error_log(sprintf("[%s] %s\n", date('d-M-Y H:i:s'), $strMessage), 3, $strLogsDir . '/' . $strLog);
    }

    /**
     * Triggers a silenced warning notice.
     *
     * @param string $package The name of the Composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The message of the deprecation
     * @param mixed  ...$args Values to insert in the message using printf() formatting
     *
     * @author Nicolas Grekas <p@tchwork.com> (original was trigger_deprecation)
     */
    public static function triggerWarning(string $package, string $version, string $message, ...$args)
    {
       @trigger_error(($package || $version ? "Since $package $version: " : '').($args ? vsprintf($message, $args) : $message), E_USER_WARNING);
    }

    /**
     * Triggers a silenced deprecation notice.
     *
     * @param string $package The name of the Composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The message of the deprecation
     * @param mixed  ...$args Values to insert in the message using printf() formatting
     */
    function triggerDeprecation(string $package, string $version, string $message, ...$args)
    {
        trigger_deprecation($package, $version, $message, ...$args);
    }
}

