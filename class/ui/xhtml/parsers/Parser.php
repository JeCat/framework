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
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\ui\UI;
use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\util\String;
use org\jecat\framework\ui\IInterpreter;
use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\lang\Object as JcObject;

/**
 * @author alee
 */
class Parser extends JcObject implements IInterpreter
{
	/**
	 * return IObject
	 */
	public function parse(String $aSource,ObjectContainer $aObjectContainer,UI $aUI)
	{
		$nProcIndex = 0 ;
		
		$aState = ParserStateDefault::singleton() ;
		$aCurrentObject = $aRootObject = new ObjectBase(0,$aSource->length()-1,0,'') ;
		
		while( $nProcIndex < $aSource->length() )
		{
			
			$aNewState = $aState->examineStateChange($aSource,$nProcIndex,$aCurrentObject) ;
			if( $aNewState )
			{
				// 切换状态
				if( $aNewState!=$aState )
				{
					// 
					$aCurrentObject = $aState->sleep($aCurrentObject,$aSource,$nProcIndex-1) ;
					
					$aCurrentObject = $aNewState->active($aCurrentObject,$aSource,$nProcIndex) ;
					
					// 状态变化
					$aState = ParserState::queryState($aCurrentObject) ;
				}
			}
			
			else 
			{
				$aCurrentObject = $aState->complete($aCurrentObject,$aSource,$nProcIndex) ;
				if(!$aCurrentObject)
				{
					break ;
				}
				
				$aCurrentObject = ParserState::queryState($aCurrentObject)->wakeup($aCurrentObject,$aSource,$nProcIndex) ;
				
				// 状态变化
				$aState = ParserState::queryState($aCurrentObject) ;
			}
			
			$nProcIndex ++ ;
		}
		
		// 未完成的对象
		if( $aCurrentObject!=$aRootObject )
		{
			$aBuff = new OutputStreamBuffer() ;
			$aRootObject->printStruct($aBuff) ;
			
			throw new Exception(
				"<pre>\r\n分析UI模板时遇到未完成的对象：%s\r\n%s\r\n</pre>"
				, array( $aCurrentObject->summary(), $aBuff )
			) ;
		}
		
		$aObjectContainer->clear() ;
		foreach($aRootObject->iterator() as $aObject)
		{
			$aObjectContainer->add($aObject) ;
		}
	}
	
	public function compileStrategySignture()
	{
		return __CLASS__ ;
	}
}

