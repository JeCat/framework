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

use org\jecat\framework\lang\Object;

class WeaveManager extends Object
{
	
	public function registerTemplate($sSourceTemplateName,$sPatchSlot,$sPatchTemplate,$sType=Patch::insertAfter)
	{
		$aPatchSlot = $this->patchSlot($sSourceTemplateName,$sPatchSlot) ;
		$aPatchSlot->addPatch(Patch::templatePatch($sPatchTemplate,$sType)) ;
	}
	
	public function registerCode($sSourceTemplateName,$sPatchSlot,$sPatchCode,$sType=Patch::insertAfter)
	{	
		$aPatchSlot = $this->patchSlot($sSourceTemplateName,$sPatchSlot) ;
		$aPatchSlot->addPatch(Patch::codePatch($sPatchCode,$sType)) ;
	}
	
	public function registerFilter($sSourceTemplateName,$sPatchSlot,$fnFilter)
	{
		$aPatchSlot = $this->patchSlot($sSourceTemplateName,$sPatchSlot) ;
		$aPatchSlot->addPatch(Patch::filterPatch($fnFilter)) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function patchSlotIterator($sSourceTemplateName)
	{
		return	isset($this->arrPatchSlots[$sSourceTemplateName])?
			new \ArrayIterator( $this->arrPatchSlots[$sSourceTemplateName] ) :
			new \EmptyIterator() ;
	}
	
	public function hasPatchSlot($sTemplateName)
	{
		return !empty( $this->arrPatchSlots[$sTemplateName] ) ;
	}

	/**
	 * @return PatchedObject
	 */
	protected function patchSlot($sTemplateName,$sPatchSlot)
	{
		$sKey = strtolower($sPatchSlot) ;
		
		if( !isset($this->arrPatchSlots[$sTemplateName][$sKey]) )
		{
			$aPatchSlot = new PatchSlot($sPatchSlot) ;
			
			$sKey = $aPatchSlot->path()->__toString() ;	// form 后的string格式路径
			
			if( !isset($this->arrPatchSlots[$sTemplateName][$sKey]) )
			{
				$this->arrPatchSlots[$sTemplateName][$sKey] = $aPatchSlot ;
			}
		}
		
		return $this->arrPatchSlots[$sTemplateName][$sKey] ;
	}

	public function compileStrategySignture()
	{
		return __CLASS__ ;
	}
	
	private $arrPatchSlots = array() ;
}

