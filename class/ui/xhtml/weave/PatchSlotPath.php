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

use org\jecat\framework\ui\IObject;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\ObjectContainer;

class PatchSlotPath
{
	private function __construct()
	{}
	
	/**
	 * @return PatchSlotPath
	 */
	static public function parsePath($sPath)
	{
		$aPath = new self() ;
		
		$arrSegments = explode('/',$sPath) ;
		foreach( $arrSegments as $sSegment )
		{
			$sSegment = trim($sSegment) ;
			
			if( empty($sSegment) )
			{
				continue ;
			}
			
			$aPath->arrSegments[] = PatchSlotPathSegment::parseSegment($sSegment) ;
		}
		
		return $aPath ;
	}
	
	/**
	 * @return org\jecat\framework\ui\xhtml\ObjectBase
	 */
	public function localObject(ObjectContainer $aObjectContainer)
	{
		$aObject = $aObjectContainer ;
		$arrProcessedSegments = array() ;
		
		foreach($this->arrSegments as $aSegment)
		{
			$aObject = $aSegment->localObject($aObject) ;
			$arrProcessedSegments[] = $aSegment ;
			
			if(!$aObject)
			{
				$sProcessedSegments = '/'.implode("/", $arrProcessedSegments) ;
				throw new Exception(
						"无法根据路径 %s(%s) 找到对应的对象"
						, array(
							$this->__toString() ,
							$sProcessedSegments ,
						)
				) ;
			}
		}
		
		return $aObject ;
	}
	
	public function __toString()
	{
		return '/' . implode('/', $this->arrSegments) ;
	}

	static public function reflectXPath(IObject $aParentObject,$sParentXPath='')
	{
		$arrChildIdxies = array() ;
		echo __METHOD__ ;
		foreach($aParentObject->iterator() as $aChildObject)
		{
			echo $sType = PatchSlotPathSegment::xpathType($aChildObject) ;
			if( !isset($arrChildIdxies[$sType]) )
			{
				$arrChildIdxies[$sType] = 0 ;
			}echo $arrChildIdxies[$sType] ;
				
			$sXPath = $sParentXPath.'/'.$sType.'@'.($arrChildIdxies[$sType]++) ;
			$aChildObject->properties()->set('xpath',$sXPath) ;
			
			self::reflectXPath($aChildObject,$sXPath) ;
		}
	}
	
	private $arrSegments = array() ;
}

