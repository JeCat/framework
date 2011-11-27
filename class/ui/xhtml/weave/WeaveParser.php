<?php
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\xhtml\UIFactory;
use org\jecat\framework\ui\UI;
use org\jecat\framework\util\String;
use org\jecat\framework\ui\ObjectContainer ;
use org\jecat\framework\ui\IInterpreter;

class WeaveParser extends Object implements IInterpreter
{	
	/**
	 * return IObject
	 */
	public function parse(String $aSource,ObjectContainer $aObjectContainer,UI $aUI)
	{
		$sTempateName = $aObjectContainer->ns() . ':' . $aObjectContainer->templateName() ;
		$aWeaveMgr = $this->weaveManager() ;
		
		if( !$aWeaveMgr->hasPatchSlot($sTempateName) )
		{
			return ;
		}

		foreach($aWeaveMgr->patchSlotIterator($sTempateName) as $aPatchSlot)
		{
			$aPatchSlot->applyPatchs($aObjectContainer,$aUI) ;
		} 
	}

	/**
	 * @return org\jecat\framework\ui\xhtml\weave\WeaveManager
	 */
	public function weaveManager()
	{
		if( !$this->aWeaveManager )
		{
			$this->aWeaveManager = WeaveManager::singleton() ;
		}
		
		return $this->aWeaveManager ; 
	}
	
	public function setWeaveManager(WeaveManager $aWeaveManager)
	{
		$this->aWeaveManager = $aWeaveManager ;
	}

	public function compileStrategySignture()
	{
		return WeaveManager::singleton()->compileStrategySignture() ;
	}
	
	private $aWeaveManager ;
}

?>