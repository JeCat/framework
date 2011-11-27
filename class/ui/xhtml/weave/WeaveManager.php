<?php
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\lang\Exception;

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
	
	/**
	 * @return \Iterator
	 */
	public function patchSlotIterator($sSourceTemplateName)
	{
		return	isset($this->arrPatchSlots[$sSourceTemplateName])?
			new \ArrayIterator( array_keys($this->arrPatchSlots[$sSourceTemplateName]) ) :
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

?>