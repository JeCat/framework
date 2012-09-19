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

use org\jecat\framework\ui\UI;
use org\jecat\framework\ui\ObjectContainer;
use org\jecat\framework\lang\Exception;

class PatchSlot
{
	public function __construct($sObjectPath)
	{
		$this->aPath = PatchSlotPath::parsePath($sObjectPath) ;
	}

	public function applyPatchs(ObjectContainer $aObjectContainer,UI $aUi)
	{
		// ----------
		// 根据 path 定位织入的目标对象
		try{
			
			$aTargetObject = $this->aPath->localObject($aObjectContainer) ;
			
		} catch (Exception $e) {
			/*throw new Exception(
				"将内容织入模板文件 %s 时遇到错误:" . $e->getMessage() ,
				array_merge(
					array($aObjectContainer->templateName()) ,
					$e->messageArgvs()
				)
			) ;*/
		}
		
		// ----------
		// 织入内容
		foreach($this->arrPatchs as $aPatch)
		{
			$aPatchObjectContainer = $aPatch->parse($aObjectContainer,$aUi) ;
			
			$aPatch->apply($aObjectContainer,$aTargetObject,$aPatchObjectContainer) ;
			
		}
	}
	
	public function addPatch(Patch $aPatch)
	{
		$this->arrPatchs[] = $aPatch ;
	}

	public function clearPatchs()
	{
		$this->arrPatchs = array() ;
	}

	public function removePatch(Patch $aPatch)
	{
		// todo
	}
	
	public function patchIterator()
	{
		return new \ArrayIterator($this->arrPatchs) ;
	}
	
	/**
	 * @return PatchSlotPath
	 */
	public function path()
	{
		return $this->aPath ;
	}
	
	private $aPath ;
	
	private $arrPatchs ;
}

