<?php
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\xhtml\UIFactory;
use org\jecat\framework\ui\UI;
use org\jecat\framework\util\String;
use org\jecat\framework\ui\ObjectContainer ;
use org\jecat\framework\lang\Object as JcObject ;
use org\jecat\framework\ui\IInterpreter;

class WeaveParser extends JcObjec implements IInterpreter
{
	public function __construct(UI $aUi)
	{
		$this->setUi($aUi) ;
	}
	
	/**
	 * return IObject
	 */
	public function parse(String $aSource,ObjectContainer $aObjectContainer)
	{
		$sTempateName = $aObjectContainer->ns() . ':' . $aObjectContainer->templateName() ;
		$aWeaveMgr = $this->weaveManager() ;
		
		if( !$aWeaveMgr->hasPatchSlot($sTempateName) )
		{
			return ;
		}

		foreach($aWeaveMgr->patchSlotIterator($sTempateName) as $aPatchSlot)
		{
			$aPatchSlot->applyPatchs($aObjectContainer,$this->ui()) ;
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
	
	/**
	 * @return org\jecat\framework\ui\xhtml\weave\WeaveManager
	 */
	public function ui()
	{
		return $this->aUi ; 
	}
	
	public function setUi(UI $aUi)
	{
		$this->aUi = $aUi ;
	}
	
	private $aWeaveManager ;
	private $aUi ;
}

?>