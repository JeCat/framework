<?php 
namespace jc ;


//////////////////////////////
// 错误处理
use jc\mvc\view\Webpage;
use jc\ui\xhtml\Factory;
use jc\system\Application;

// 报告所有错误
error_reporting(E_ALL & ~E_DEPRECATED) ;

// 默认的时区
date_default_timezone_set('Asia/Shanghai') ;

// 编码
header("Content-Type: text/html; charset=UTF-8") ;

// 关闭魔术引用
set_magic_quotes_runtime(false) ;

// 预加载类(Before Class Loader)
require_once __DIR__."/class/lang/IObject.php" ;
require_once __DIR__."/class/lang/Object.php" ;
require_once __DIR__."/class/lang/IException.php" ;
require_once __DIR__."/class/lang/Exception.php" ;
require_once __DIR__."/class/lang/oop/Package.php" ;
require_once __DIR__."/class/lang/oop/ClassLoader.php" ;
require_once __DIR__."/class/system/Application.php" ;
require_once __DIR__."/class/system/ApplicationFactory.php" ;

require_once __DIR__."/class/fs/FileSystem.php" ;
require_once __DIR__."/class/fs/IFSO.php" ;
require_once __DIR__."/class/fs/IFile.php" ;
require_once __DIR__."/class/fs/IFolder.php" ;
require_once __DIR__."/class/fs/FSO.php" ;
require_once __DIR__."/class/fs/imp/LocalFSO.php" ;
require_once __DIR__."/class/fs/imp/LocalFile.php" ;
require_once __DIR__."/class/fs/imp/LocalFolder.php" ;
require_once __DIR__."/class/fs/imp/LocalFileSystem.php" ;

require_once __DIR__."/class/lang//compile/IStrategySummary.php" ;
require_once __DIR__."/class/lang/aop/AOP.php" ;
require_once __DIR__."/class/lang/compile/Compiler.php" ;
require_once __DIR__."/class/lang/compile/CompilerFactory.php" ;


define( __NAMESPACE__."\\VERSION", '0.6.1' ) ;
define( __NAMESPACE__."\\PATH", __DIR__ ) ;

// 处理未捕获的异常
set_exception_handler(function(\Exception $aException)
{
	$sContents = "<pre>" ;

	do{
		
		$sContents.= "------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------\r\n" ;
		
		$sContents.= "无法处理的异常：".get_class($aException)."\r\n" ;
			
		if($aException instanceof \jc\lang\Exception)
		{
			$sContents.= $aException->message()."\r\n" ;
		}
		else
		{
			$sContents.= $aException->getMessage()."\r\n" ;
		}
		
		$sContents.= 'Line '.$aException->getLine().' in file: '.$aException->getFile()."\r\n" ;
		$sContents.= $aException->getTraceAsString()."\r\n" ;
	
	// 递归 cause
	} while( $aException = $aException->getPrevious() ) ;
	
	$sContents.= "</pre>\r\n" ;
	
	echo $sContents ;
}) ;
?>