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

namespace PBDKN\Efgco4\Resources\contao\classes;

use Contao\StringUtil;

/**
 * Class EfgLog.
 *
 * @copyright  PBD 2021..
 * @license    LGPL
 */
class EfgLog
{
    protected static $cnt = 0;
    protected static $debFormKey = '';                    // Formularkey fuer den Aktuellen Debug
    protected static $uniqid = 0;
    protected static $myefgdebuglevel = 0;

    /* debugkeys binary key
               => array('0' => kein debug,
                        '1' => 'small',
                        '01' => 'medium' = 2  small + medium (3)
                        '001' => 'full' = 4   small + medium + full (7)
                        '0001 '=>'emailsmall= 8  (8)
                        '00001'=>'emailmedium' = 16 (24)
                        '000001'=>'emailfull' =32 (56)
    */
    public static function setefgDebugmode($key): void
    {
        if ('' === self::$debFormKey || $key !== self::$debFormKey) {
            // Get all forms marked to store data
            $objForms = \Database::getInstance()->prepare('SELECT alias,title,efgDebugMode FROM tl_form WHERE storeFormdata=?')
                ->execute('1')
            ;
            //$savekey = $key;
            while ($objForms->next()) {  // suche Form
                if ('form' === $key) {    // bei neuer form ist der key form nimm den höchsten wert
                  if ($objForms->efgDebugMode > self::$myefgdebuglevel) {
                      self::$myefgdebuglevel = $objForms->efgDebugMode;
                      self::$debFormKey = $key;
                      $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
                      self::$uniqid = $arrUniqid[1];
                      continue;
                  }
                }
                $strFormKey = (!empty($objForms->alias)) ? $objForms->alias : str_replace('-', '_', standardize($objForms->title));
                if ($strFormKey === substr($key, 3)) {
                    self::$myefgdebuglevel = $objForms->efgDebugMode;
                    self::$debFormKey = $key;
                    $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
                    self::$uniqid = $arrUniqid[1];
                    \System::log("PBD EfgwriteLog set debuglevel '".self::$myefgdebuglevel."' for $key FormKey $strFormKey", __METHOD__, TL_GENERAL);
                    return;
                }
            }
            if (0 === self::$myefgdebuglevel) {
                //\System::log("PBD EfgwriteLog reset $myefgdebuglevel key $key", __METHOD__, TL_GENERAL);
                self::$debFormKey = '';
                self::$uniqid = 0;
            } 
        }
    }

    /**
     * Write in log file, if debug is enabled.
     *
     * @param int    $level
     * @param string $method
     * @param int    $line
     * @param string $value
     */
    public static function EfgwriteLog($level, $method, $line, $value): void
    {
        if ('' === self::$debFormKey) {
            return;
        }
        $method = trim($method);
        //$arrNamespace = StringUtil::trimsplit('::', $method);
        //$arrClass =  StringUtil::trimsplit('\\', $arrNamespace[0]);
        //$vclass = $arrClass[\count($arrClass)-1] . "::" . $arrNamespace[1] ; // class that will write the log
        $arr = StringUtil::trimsplit('\\', $method);
        $vclass = $arr[\count($arr) - 1];

        if (\is_array($value)) {
            $value = print_r($value, true);
        }
        if (($level & self::$myefgdebuglevel) === $level) {
            //self::logMessage(sprintf('[level: %s] [$myefgdebuglevel %s] [and %s] ', $level, self::$myefgdebuglevel, self::$myefgdebuglevel&$level), 'efg_debug');
            self::logMessage(sprintf('[%s] [%s] [%s:%s] %s', self::$uniqid, $level, $vclass, $line, 'PBD '.$value), 'efg_debug');
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
     * Write in log file, if debug is enabled.
     *
     * @param string $method
     * @param int    $line
     */
    public static function EfgwriteLog1($level, $method, $line, $value): void
    {
        $method = trim($method);
        \System::log('PBD EfgwriteLog cnt '.self::$cnt." level $level method $method  first '".$GLOBALS['efgdebug']['debug']['first']."'", __METHOD__, TL_GENERAL);
        if (!(false === strpos($method, '## START ##', 0))) {        // Start des Debugs
            $arr = explode('::', $method);
            if (\count($arr) < 2) {
                return;
            }              // kein debuglevel angegeben
            if (!isset($GLOBALS['efgdebug']['debug']['first'])) {
                $arrUniqid = StringUtil::trimsplit('.', uniqid('efgc0n7a0', true));
                $GLOBALS['efgdebug']['debug']['first'] = $arrUniqid[1];
                $GLOBALS['efgdebug']['debug']['efgdebuglevel'] = $arr[1];
                \System::log("PBD EfgwriteLog set new first '".$GLOBALS['efgdebug']['debug']['first']."' efgdebuglevel '".$GLOBALS['efgdebug']['debug']['efgdebuglevel']."'", __METHOD__, TL_GENERAL);
                if ($level & $GLOBALS['efgdebug']['debug']['efgdebuglevel']) {
                    \System::log("PBD EfgwriteLog to file level $level method $method line $line value $value efgdebuglevel '".$GLOBALS['efgdebug']['debug']['efgdebuglevel']."'", __METHOD__, TL_GENERAL);
                    self::logMessage(sprintf('[%s] [%s] [%s] [%s] %s', $GLOBALS['efgdebug']['debug']['first'], $level, $method, $line, $value), 'efg_debug');
                }

                return;
            }

            \System::log("PBD EfgwriteLog first schon gesetzt first '".$GLOBALS['efgdebug']['debug']['first']."'", __METHOD__, TL_GENERAL);

            return;
        }

        $arrNamespace = StringUtil::trimsplit('::', $method);
        $arrClass = StringUtil::trimsplit('\\', $arrNamespace[0]);
        $vclass = $arrClass[\count($arrClass) - 1]; // class that will write the log

        if (\is_array($value)) {
            $value = print_r($value, true);
        }
        \System::log("PBD EfgwriteLog no start level $level method $method line $line efgdebuglevel '".$GLOBALS['efgdebug']['debug']['efgdebuglevel']."'", __METHOD__, TL_GENERAL);

        //if ($level & $GLOBALS['efgdebug']['debug']['efgdebuglevel']) {
        \System::log("PBD EfgwriteLog to file start level $level efgdebuglevel '".$GLOBALS['efgdebug']['debug']['efgdebuglevel']."'", __METHOD__, TL_GENERAL);
        self::logMessage(sprintf('[%s] [%s] [%s] [%s] %s', $GLOBALS['efgdebug']['debug']['first'], $level, $method, $line, '('.$vclass.')'.$value), 'efg_debug');
        //}

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
     * Wrapper for old log_message.
     *
     * @param string     $strMessage
     * @param mixed|null $strLog
     */
    public static function logMessage($strMessage, $strLog = null): void
    {
        $env = $_SERVER['APP_ENV'] ?? 'prod';

        if (null === $strLog) {
            $strLog = $env.'-'.date('Y-m-d').'.log';
        } else {
            $strLog = $env.'-'.date('Y-m-d').'-'.$strLog.'.log';
        }

        $strLogsDir = null;

        if (($container = \System::getContainer()) !== null) {
            $strLogsDir = $container->getParameter('kernel.logs_dir');
        }

        if (!$strLogsDir) {
            $strLogsDir = TL_ROOT.'/var/logs';
        }

        error_log(sprintf("[%s] %s\n", date('d-M-Y H:i:s'), $strMessage), 3, $strLogsDir.'/'.$strLog);
    }

    /**
     * Triggers a silenced warning notice.
     *
     * @param string $package The name of the Composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The message of the deprecation
     * @param mixed  ...$args Values to insert in the message using printf() formatting
     */
    public static function triggerWarning(string $package, string $version, string $message, ...$args): void
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
    public function triggerDeprecation(string $package, string $version, string $message, ...$args): void
    {
        trigger_deprecation($package, $version, $message, ...$args);
    }
}
