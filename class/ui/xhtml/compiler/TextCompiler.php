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
namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\ui\xhtml\Node;

use org\jecat\framework\ui\xhtml\AttributeValue;

use org\jecat\framework\locale\Locale;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

class TextCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		if( $aObject instanceof \org\jecat\framework\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			Assert::type("org\\jecat\\framework\\ui\\xhtml\\Text",$aObject,'aObject') ;

			$sText = $aObject->source() ;
			
			// locale translate
			do{
				if( !trim($sText) )
				{
					break ;
				}
				// 排除 script/style 等标签中的内容
				if( $aParent=$aObject->parent() and ($aParent instanceof Node) and in_array(strtolower($aParent->tagName()),array('script','style')) )
				{
					break ;
				}
				// 仅 title alt 等属性
				if( $aObject instanceof AttributeValue and !in_array(strtolower($aObject->name()),array('title','alt')) )
				{
					break ;	
				}
				// 过滤 注释 和 doctype 声明
				if( preg_match('/^\\s*<\\!.*>\\s*$/',$sText) )
				{
					break ;
				}
				
				$sText = Locale::singleton()->trans($sText,null,'ui') ;
			
			} while(0) ;
			$aDev->output($sText) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		}
	}
}

