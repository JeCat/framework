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
	
	public function compile(UI $aUi)
	{
		if( $this->aCompiled )
		{
			return ;
		}
		
		if( $this->nKind==self::code )
		{
			$aOutput = new OutputStreamBuffer() ;
			$aUi->compile(new InputStreamCache($this->sCode),$aOutput,null,false) ;
			
			$this->aCompiled = new String($aOutput->bufferBytes()) ;
		}
		
		else if( $this->nKind==self::template )
		{
			$this->aCompiled = new String("
// 织入模板： {$this->sTemplate}----------------------
\$this->display(\"{$this->sTemplate}\",\$aVariables,\$aDevice) ;
// -------------------------------------------------------------------------
") ;
		}
		
		else if( $this->nKind==self::filter )
		{
			// nothing todo
		}
	}
	
	public function apply(ObjectContainer $aObjectContainer,IObject &$aTargetObject)
	{
		if( $this->nKind==self::filter )
		{
			call_user_func_array($this->fnFilter,array($aObjectContainer,$aTargetObject)) ;
		}
		else
		{
			$aWeaveinObject = new WeaveinObject($this->aCompiled,$aTargetObject) ;
			
			switch ( $this->sType )
			{
				case self::insertBefore :
					$aTargetObject->insertAfterByPosition(0,$aWeaveinObject) ;
					break ;
					
				case self::insertAfter :
					$aTargetObject->add($aWeaveinObject) ;
					break ;
					
				case self::appendBefore :
					$aParent = $aTargetObject->parent() ;
					if(!$aParent)
					{
						throw new Exception("遇到错误，无法将内容织入指定的路径") ;
					}
					$aParent->insertBefore($aTargetObject,$aWeaveinObject) ;
					break ;
					
				case self::appendAfter :
					$aParent = $aTargetObject->parent() ;
					if(!$aParent)
					{
						throw new Exception("遇到错误，无法将内容织入指定的路径") ;
					}
					$aParent->insertAfter($aTargetObject,$aWeaveinObject) ;
					break ;
					
				case self::replace :
					$aParent = $aTargetObject->parent() ;
					if(!$aParent)
					{
						throw new Exception("遇到错误，无法将内容织入指定的路径") ;
					}
					$aParent->replace($aTargetObject,$aWeaveinObject) ;
					$aTargetObject = $aWeaveinObject ;
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

