<?php 
namespace jc ;


//////////////////////////////
// 错误处理
error_reporting(E_ALL) ;		// 报告所有错误	

// 将错误转换成异常抛出
function __exception_error_handler($errno, $errstr, $errfile, $errline )
{
	if( ($errno&E_STRICT)==E_STRICT )
	{
		return ;
	}
	
	throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("jc\\__exception_error_handler",E_ALL|E_STRICT) ;


// 加载核心类
require_once __DIR__."/lib.php/Object.php" ;
require_once __DIR__."/lib.php/util/IDataSrc.php" ;
require_once __DIR__."/lib.php/util/DataSrc.php" ;
require_once __DIR__."/lib.php/system/ApplicationFactory.php" ;


define( __NAMESPACE__."\\VERSION", '0.6.0' ) ;
define( __NAMESPACE__."\\PATH", __DIR__.'/' ) ;


?>