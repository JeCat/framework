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
namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\ui\xhtml\Tag;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

class NodeCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

		if( $aCompiler=$this->subCompiler(strtolower($aObject->tagName())) )
		{
			$aCompiler->compile($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		}
		
		else 
		{
			$this->compileTag($aObject->headTag(), $aObjectContainer, $aDev, $aCompilerManager) ;
			
			if( $aTailTag = $aObject->tailTag() )
			{
				$this->compileChildren($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
				
				$this->compileTag($aTailTag, $aObjectContainer, $aDev, $aCompilerManager) ;
			}
		}
	}

	protected function compileTag(Tag $aTag,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->output('<') ;
		if( $aTag->isTail() )
		{
			$aDev->output('/') ;
		}
		
		$aDev->output($aTag->name()) ;
		
		// 属性
		$aAttrs = $aTag->attributes() ;
		foreach ($aAttrs->valueIterator() as $aAttrVal)
		{
			$aDev->output(' ') ;
			
			// 具名属性
			if($sName=$aAttrVal->name())
			{
				$aDev->output($sName) ;
				$aDev->output('=') ;
			}
			
			$aDev->output($aAttrVal->quoteType()) ;
			if( $aAttrCompiler = $aCompilerManager->compiler($aAttrVal) )
			{
				$aAttrCompiler->compile($aAttrVal,$aObjectContainer,$aDev,$aCompilerManager) ;
				get_class($aAttrCompiler) ;
			}
			else 
			{
				if($sName)
				{
					$aDev->output(
						addcslashes($aAttrs->get($sName),$aAttrVal->quoteType().'\\')
					) ;
				}
				else 
				{
					$aDev->output(
						addcslashes($aAttrs->source(),$aAttrVal->quoteType().'\\')
					) ;
				}
			}
		
			$aDev->output($aAttrVal->quoteType()) ;
		}
		
		if( $aTag->isSingle() )
		{
			$aDev->output(' /') ;
		}
		
		$aDev->output('>') ;
	}
	
	public function compileChildren(Node $aNode,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		foreach($aNode->childElementsIterator() as $aObject)
		{
			if( $aCompiler = $aCompilerManager->compiler($aObject) )
			{
				$aCompiler->compile($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
			}
		}
	}
	
	//
	static public function assignVariableName($sPrefix='')
	{
		return $sPrefix.'var'.self::$nVariableAssigned++ ;
	}
	static private $nVariableAssigned = 0 ;
}
/**
 * @wiki /模板引擎/标签/自定义标签
 *
 *  Jecat定义了很多类html标签来提高模板中代码的灵活度,提高工作效率.
 *  如果你需要一个名叫sidebar的标签来显示侧边栏,或者一个名叫banner的标签用来显示横幅广告,但是Jecat本身没有提供这样的标签,那么自己做一个好了!
 *  其实对于Jecat来说没有所谓的"原有标签",所有的标签都是"自定义"的,只不过非常常用的标签例如if标签都已经被Jecat开发人员定义好了,我们可以直接拿来用.
 *  也就是说你只要仿照Jecat框架中已经存在的标签的源代码的套路就可以做成自己的标签了.
 *  [example title="/模板引擎/标签/自定义标签"]
 *  
 *  
 */

