<?php 
namespace jc ;


//////////////////////////////
// 错误处理
error_reporting(E_ALL) ;		// 报告所有错误	


// 加载核心类
require_once __DIR__."/src/lib.php/lang/IObject.php" ;
require_once __DIR__."/src/lib.php/lang/Object.php" ;
require_once __DIR__."/src/lib.php/lang/IException.php" ;
require_once __DIR__."/src/lib.php/lang/Exception.php" ;
require_once __DIR__."/src/lib.php/lang/Factory.php" ;
require_once __DIR__."/src/lib.php/util/IDataSrc.php" ;
require_once __DIR__."/src/lib.php/util/DataSrc.php" ;
require_once __DIR__."/src/lib.php/system/ClassLoader.php" ;
require_once __DIR__."/src/lib.php/system/CoreApplication.php" ;
require_once __DIR__."/src/lib.php/system/Application.php" ;


define( __NAMESPACE__."\\VERSION", '0.6.0' ) ;
define( __NAMESPACE__."\\PATH", __DIR__.'/' ) ;


?>