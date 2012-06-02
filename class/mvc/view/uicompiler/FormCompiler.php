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
//  正在使用的这个版本是：0.8
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
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class FormCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		if( $aTailTag = $aObject->tailTag() )
		{
			$aDev->putCode("if( !(\$aVariables->get('theView') instanceof \\org\\jecat\\framework\\mvc\\view\\IFormView) or \$aVariables->get('theView')->isShowForm() )\r\n{\r\n") ;
			
			$this->compileTag($aObject->headTag(), $aObjectContainer, $aDev, $aCompilerManager) ;
			
			$aDev->putCode("\tif(\$aVariables->get('theView') instanceof \\org\\jecat\\framework\\mvc\\view\\IFormView){\r\n") ;
			$aDev->putCode("\t\t") ;
			$aDev->output('<input type="hidden" name="') ;
			$aDev->putCode("\t\t\$aDevice->write( \$aVariables->get('theView')->htmlFormSignature() ) ;\r\n") ;
			$aDev->output('" value="1" />') ;
			$aDev->output('<input type="hidden" name="act" value="submit" />') ;
			$aDev->putCode("\t}\r\n") ;

			$this->compileChildren($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
			
			$this->compileTag($aTailTag, $aObjectContainer, $aDev, $aCompilerManager) ;
			
			$aDev->putCode("}\r\n") ;
		}
		
		else
		{
			throw new Exception("form 节点必须是成对标签形式（位置：%d行）。",$aObject->line()) ;
		}
	}
}

