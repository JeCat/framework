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
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\util\String;
use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\io\InputStreamCache;
use org\jecat\framework\ui\UI;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\lang\Exception;

class Patch
{
	const insertBefore = 'insertBefore' ;
	const insertAfter = 'insertAfter' ;
	const appendBefore = 'appendBefore' ;
	const appendAfter = 'appendAfter' ;
	const replace = 'replace' ;
	
	
	const template = 1 ;
	const code = 2 ;
	const filter = 3 ;
	
	private function __construct()
	{}
	
	static public function templatePatch($sTemplate,$sType)
	{
		$aPatch = new self() ;
	
		if( !in_array($sType, self::$arrValidTypes) )
		{
			throw new Exception("传入了无效的参数：%s",$sType) ;
		}
		
		$aPatch->nKind = self::template ;
		$aPatch->sType = $sType ;
		$aPatch->sTemplate = $sTemplate ;
		
		return $aPatch ;
	} 
	static public function codePatch($sCode,$sType)
	{
		$aPatch = new self() ;
	
		if( !in_array($sType, self::$arrValidTypes) )
		{
			throw new Exception("传入了无效的参数\$sType：%s",$sType) ;
		}
		
		$aPatch->nKind = self::code ;
		$aPatch->sType = $sType ;
		$aPatch->sCode = $sCode ;
		
		return $aPatch ;
	}
	static public function filterPatch($fnFilter)
	{
		$aPatch = new self() ;
	
		$aPatch->nKind = self::filter ;
		$aPatch->fnFilter = $fnFilter ;
		
		return $aPatch ;
	}
	
	public function parse(ObjectContainer $aObjectContainer,UI $aUi)
	{
		if( $this->aCompiled )
		{
			return ;
		}

		$aPatchObjectContainer = new ObjectContainer(null,'*',$aObjectContainer->templateSignature()) ;
		
		if( $this->nKind==self::code )
		{
			$aUi->interpreters()->parse( new InputStreamCache($this->sCode) , $aPatchObjectContainer, $aUi ) ;
		}
		
		else if( $this->nKind==self::template )
		{
			$aSrcMgr = $aUi->sourceFileManager() ;
			list($sNamespace,$sSourceFile) = $aSrcMgr->detectNamespace($this->sTemplate) ;
			if( !$aSourceFile=$aSrcMgr->find($sSourceFile,$sNamespace) )
			{
				throw new Exception("处理模板编织时遇到错误，无法找到用于织入的模板文件：%s",$this->sTemplate) ;
			}
			
			$aUi->interpreters()->parse( $aSourceFile->openReader(), $aPatchObjectContainer, $aUi ) ;
		}
		
		else if( $this->nKind==self::filter )
		{
			// nothing todo
		}
		
		return $aPatchObjectContainer ;
	}
	
	public function apply(ObjectContainer $aObjectContainer,IObject &$aTargetObject,ObjectContainer $aPatchObjectContainer)
	{
		if( $this->nKind==self::filter )
		{
			call_user_func_array($this->fnFilter,array($aObjectContainer,$aTargetObject)) ;
		}
		else
		{
			switch ( $this->sType )
			{
				case self::insertBefore :
					$nPos = 0 ;
					foreach($aPatchObjectContainer->iterator() as $aObject)
					{
						$aTargetObject->insertAfterByPosition($nPos++,$aObject) ;
					}
					break ;
					
				case self::insertAfter :
					foreach($aPatchObjectContainer->iterator() as $aObject)
					{
						$aTargetObject->add($aObject) ;
					}
					break ;
					
				case self::appendBefore :
					$aParent = $aTargetObject->parent() ;
					if(!$aParent)
					{
						throw new Exception("遇到错误，无法将内容织入指定的路径") ;
					}					
					foreach($aPatchObjectContainer->iterator() as $aObject)
					{
						$aParent->insertBefore($aTargetObject,$aObject) ;
					}					
					break ;
					
				case self::appendAfter :
					$aParent = $aTargetObject->parent() ;
					if(!$aParent)
					{
						throw new Exception("遇到错误，无法将内容织入指定的路径") ;
					}
					foreach($aPatchObjectContainer->iterator() as $aObject)
					{
						$aParent->insertAfter($aTargetObject,$aObject) ;
					}
					break ;
					
				case self::replace :
					$aParent = $aTargetObject->parent() ;
					if(!$aParent)
					{
						throw new Exception("遇到错误，无法将内容织入指定的路径") ;
					}
					
					foreach($aPatchObjectContainer->iterator() as $aObject)
					{
						$aParent->insertAfter($aTargetObject,$aObject) ;
					}
					$aParent->remove($aTargetObject) ;
					
					break ;
			}
		}
	}
	

	static private $arrValidTypes = array(
		self::insertBefore ,
		self::insertAfter ,
		self::appendBefore ,
		self::appendAfter ,
		self::replace ,
	) ; 

	
	private $sType ;
	
	private $nKind ;
	
	private $sTemplate ;
	
	private $sCode ;
	
	private $fnFilter ;
	
	private $aCompiled ;
}

