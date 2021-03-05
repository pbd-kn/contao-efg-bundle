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
\System::log("PBD EfgwriteLog load me ", "LOAD", TL_GENERAL);

/**
 * Class EfgLog
 *
 * @copyright  PBD 2021..
 * @author     PBD 
 * @license    LGPL
 */
class EfgLog
{

    public static $myFirst = 'anton';
    protected static $level;
    /**
     * Write in log file, if debug is enabled
     *
     * @param string  $method
     * @param integer $line
     */
    public static function EfgwriteLog($level,$method, $line, $value)
    {
        $method = trim($method);       
\System::log("PBD EfgwriteLog myTest " . EfgLog::$myFirst . " level $level method $method  first '" . $GLOBALS['efgdebug']['debug']['first'] . "'", __METHOD__, TL_GENERAL);

        if (!(strpos ( $method, '## START ##', 0 ) === false))        // Start des Debugs
        {
\System::log("PBD EfgwriteLog start found level $level method $method  ", __METHOD__, TL_GENERAL);
            $arr = explode("::", $method);
            if (count($arr) < 2 ) return;              // kein debuglevel angegeben
            //if (!isset($GLOBALS['efgdebug']['debug']['first']) || $arr[1] != $GLOBALS['efgdebug']['debug']['level']) 
            if (EfgLog::$myFirst == 'anton' || $arr[1] != $GLOBALS['efgdebug']['debug']['level']) 
            {
                //$GLOBALS['efgdebug']=array();
                //$GLOBALS['efgdebug']['debug']=array();
                $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
                $GLOBALS['efgdebug']['debug']['first'] = $arrUniqid[1];
                $GLOBALS['efgdebug']['debug']['level'] = $arr[1];
\System::log("PBD EfgwriteLog set new first '" . $GLOBALS['efgdebug']['debug']['first'] . "' level '" . $GLOBALS['efgdebug']['debug']['level'] . "'", __METHOD__, TL_GENERAL);
EfgLog::$myFirst = 'caesar';
\System::log("PBD EfgwriteLog myTest nun " . EfgLog::$myFirst , __METHOD__, TL_GENERAL);
                if ($level & $GLOBALS['efgdebug']['debug']['level']) {
\System::log("PBD EfgwriteLog start level $level method $method line $line value $value efgdebuglevel '" . $GLOBALS['efgdebug']['debug']['level'] . "'", __METHOD__, TL_GENERAL);
                  self::logMessage(sprintf('[%s] [%s] [%s] [%s] %s', $GLOBALS['efgdebug']['debug']['first'],$level, $method, $line, $value), 'efg_debug');
                }
                return;
            }
            else
            {
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
\System::log("PBD EfgwriteLog no start level $level method $method line $line efgdebuglevel '" . $GLOBALS['efgdebug']['debug']['level'] . "'", __METHOD__, TL_GENERAL);
  
        if ($level & $GLOBALS['efgdebug']['debug']['level']) {
          self::logMessage(sprintf('[%s] [%s] [%s] [%s] %s', $GLOBALS['efgdebug']['debug']['first'],$level, $method, $line, '('.$vclass.')'.$value), 'efg_debug');
        }
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

