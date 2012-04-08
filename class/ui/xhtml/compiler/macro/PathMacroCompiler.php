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
//  正在使用的这个版本是：0.7.1
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
namespace org\jecat\framework\ui\xhtml\compiler\macro ;

use org\jecat\framework\ui\xhtml\compiler\MacroCompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/宏
 * @wiki 速查/模板引擎/宏
 * =={/}==
 *  
 *  可以在宏内使用*.url/*.uri，显示出url地址
 * {|
 *  !使用方法
 *  !说明
 *  !
 *  !
 *  !
 *  |---
 *  |{/*.uri}
 *  |显示uri的路径
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.url}
 *  |显示url的路径
 *  |
 *  |
 *  |---
 *  |{/*.url.scheme}
 *  |显示协议类型
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.uri.host}
 *  |显示host
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.uri.path}
 *  |显示path的路径
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.uri.query}
 *  |显示query的路径
 *  |
 *  |
 *  |
 *  |}
 *  [example php frameworktest template/test-template/macro/PathMacroCase.html 1 6]
 *  
 *  [^]注意，url和uri的不同[/^]
 */

class PathMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$sContents = trim($aObject->source()) ;
		
		if( $sContents=='*.uri' )
		{
			$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->uri()) ;" ) ;
		}
		else if( substr($sContents,0,5)=='*.url' )
		{
			$sPart = strlen($sContents)>5?
				substr($sContents,5): '' ;
				
			switch($sPart)
			{
			case '' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->url()) ;" ) ;
				break ;
				
			case '.scheme' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlScheme()) ;" ) ;
				break ;
				
			case '.host' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlHost()) ;" ) ;
				break ;
				
			case '.path' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlPath()) ;" ) ;
				break ;
				
			case '.query' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlQuery()) ;" ) ;
				break ;
				
			default :
				break ;
			}
		}
	}
}

