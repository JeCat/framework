<?php
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
			throw new Exception(
				"将内容织入模板文件 %s 时遇到错误:" . $e->getMessage() ,
				array_merge(
					array($aObjectContainer->templateName()) ,
					$e->messageArgvs()
				)
			) ;
		}
		
		// ----------
		// 织入内容
		foreach($this->arrPatchs as $aPatch)
		{
			$aPatch->compile($aUi) ;
			
			$aPatch->apply($aObjectContainer,$aTargetObject) ;
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

?>