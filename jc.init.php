<?php 
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8.0.0
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/ 
namespace org\jecat\framework ;


//////////////////////////////
// 报告所有错误
error_reporting(E_ALL & ~E_DEPRECATED) ;

// 默认的时区
date_default_timezone_set('Asia/Shanghai') ;

// 编码
@header("Content-Type: text/html; charset=UTF-8") ;

define( "org\\jecat\\framework\\VERSION", '0.8.0.0' ) ;
define( "org\\jecat\\framework\\PATH", __DIR__ ) ;
define( "org\\jecat\\framework\\CLASSPATH", __DIR__.'/class' ) ;

// 预加载类(Before Class Loader)
require_once CLASSPATH."/pattern/ISingletonable.php" ;
require_once CLASSPATH."/pattern/IFlyweightable.php" ;
require_once CLASSPATH."/lang/IObject.php" ;
require_once CLASSPATH."/lang/Object.php" ;
require_once CLASSPATH."/lang/Type.php" ;
require_once CLASSPATH."/lang/IException.php" ;
require_once CLASSPATH."/lang/Exception.php" ;
require_once CLASSPATH."/lang/oop/Package.php" ;
require_once CLASSPATH."/lang/oop/ShadowClassPackage.php" ;
require_once CLASSPATH."/lang/oop/ClassLoader.php" ;
require_once CLASSPATH."/system/Application.php" ;
require_once CLASSPATH."/system/ApplicationFactory.php" ;

require_once CLASSPATH."/fs/FSO.php" ;
require_once CLASSPATH."/fs/Folder.php" ;
require_once CLASSPATH."/fs/File.php" ;

require_once CLASSPATH."/lang//compile/IStrategySummary.php" ;
require_once CLASSPATH."/lang/aop/AOP.php" ;
require_once CLASSPATH."/lang/compile/Compiler.php" ;
require_once CLASSPATH."/lang/compile/CompilerFactory.php" ;

// functions
require_once PATH."/functions.php" ;


// 处理未捕获的异常
set_exception_handler(function(\Exception $aException)
{
	$sContents = "<pre>" ;

	do{
		
		$sContents.= "------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------\r\n" ;
		
		$sContents.= "无法处理的异常：".get_class($aException)."\r\n" ;
			
		if($aException instanceof \org\jecat\framework\lang\Exception)
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