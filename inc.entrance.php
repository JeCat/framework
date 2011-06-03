<?php 
namespace jc ;


//////////////////////////////
// 错误处理
use jc\ui\xhtml\Factory;
use jc\system\Application;
use jc\ui\xhtml\Factory as UIFactory ;

error_reporting(E_ALL) ;							// 报告所有错误	


// 预加载类(Before Class Loader)
require_once __DIR__."/src/lib.php/lang/IObject.php" ;
require_once __DIR__."/src/lib.php/lang/Object.php" ;
require_once __DIR__."/src/lib.php/lang/IException.php" ;
require_once __DIR__."/src/lib.php/lang/Exception.php" ;
require_once __DIR__."/src/lib.php/lang/Factory.php" ;
require_once __DIR__."/src/lib.php/system/ClassLoader.php" ;
require_once __DIR__."/src/lib.php/system/CoreApplication.php" ;
require_once __DIR__."/src/lib.php/system/Application.php" ;
require_once __DIR__."/src/lib.php/system/AppFactory.php" ;


define( __NAMESPACE__."\\VERSION", '0.6.1' ) ;
define( __NAMESPACE__."\\PATH", __DIR__.'/' ) ;

// 创建 Application 对象
$aApp = Application::singleton(true) ;

// 模板目录
UIFactory::singleton()->sourceFileManager()->addFolder(PATH.'src/template/') ;

// 处理未捕获的异常
set_exception_handler(function(\Exception $aException)
{
	$aRspn = Application::singleton()->response() ;

	do{
		
		$aRspn->output("------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------") ;
		$aRspn->output($aException->message()) ;
		$aRspn->output($aException->getTraceAsString()) ;
	
	// 递归 cause
	} while( $aException = $aException->getPrevious() ) ;
}) ;

return $aApp ;
?>