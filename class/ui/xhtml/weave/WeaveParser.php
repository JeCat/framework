<?php
namespace jc\ui\xhtml\parsers ;

use jc\lang\Exception;
use jc\ui\xhtml\UIFactory;
use jc\ui\UI;
use jc\util\String;
use jc\ui\ObjectContainer ;
use jc\lang\Object as JcObject ;
use jc\ui\IInterpreter;

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
	 * @return jc\ui\xhtml\weave\WeaveManager
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
	 * @return jc\ui\xhtml\weave\WeaveManager
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