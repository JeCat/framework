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
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class CodeCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );

		$sLang = strtolower($aObject->attributes()->string('lang')) ;
	
		
			
		if( in_array($sLang, array('text/php','php')) and $aTailTag=$aObject->tailTag() )
		{
			// 编译头标签
			$this->compileTag($aObject->headTag(), $aObjectContainer, $aDev, $aCompilerManager) ;
			
			// 设置代码"上色器"
			$sVarName = parent::assignVariableName() ;
			
			$aDev->write("\r\n") ;
			$aDev->write("\${$sVarName} = new \\org\\jecat\\framework\\ui\\xhtml\\compiler\\node\\CodeColor() ;\r\n") ;
			$aDev->write("\\org\\jecat\\framework\\io\\StdOutputFilterMgr::singleton()->add(array(\${$sVarName},'outputFilter')) ;\r\n") ;
			$aDev->write("") ;
			
			// 编译 node body
			$this->compileChildren($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
			
			// 输出代码
			$aDev->write("\r\n") ;
			$aDev->write("\\org\\jecat\\framework\\io\\StdOutputFilterMgr::singleton()->remove( array(\${$sVarName},'outputFilter') ) ;\r\n") ;
			$aDev->write("\${$sVarName}->output(\$aDevice) ;") ;
			$aDev->write("") ;
			
			// 编译尾标签
			$this->compileTag($aTailTag, $aObjectContainer, $aDev, $aCompilerManager) ;
		}
		
		// 按照普通 html 节点处理
		else 
		{
			parent::compile($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		}
	}

}

