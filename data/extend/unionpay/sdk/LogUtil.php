<?php
namespace data\extend\unionpay\sdk;
include_once 'log.class.php';
include_once 'SDKConfig.php';
header ( 'Content-type:text/html;charset=utf-8' );

class LogUtil
{
	private static $_logger = null;
	public static function getLogger()
	{
		if (LogUtil::$_logger == null ) {
			$l = SDKConfig::getSDKConfig()->logLevel;
			if("INFO" == strtoupper($l))
				$level = PhpLog::INFO;
			else if("DEBUG" == strtoupper($l))
				$level = PhpLog::DEBUG;
			else if("ERROR" == strtoupper($l))
				$level = PhpLog::ERROR;
			else if("WARN" == strtoupper($l))
				$level = PhpLog::WARN;
			else if("FATAL" == strtoupper($l))
				$level = PhpLog::FATAL;
			else
				$level = PhpLog::OFF;
			LogUtil::$_logger = new PhpLog ( SDKConfig::getSDKConfig()->logFilePath, "PRC", $level );
		}
		return self::$_logger;
	}
}

