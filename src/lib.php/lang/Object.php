<?php
namespace jc\lang ;

use jc\system\Application ;

class Object implements IObject
{
	/**
	 * Enter description here ...
	 * 
	 * @return stdClass
	 */
	public function create($sClassName,$sNamespace='\\',array $arrArgvs=array())
	{
		Object::$funcCreate() ;
		
		$aObject = Factory::createNewObject($sClassName,$sNamespace,$arrArgvs) ;
		
		if( $aObject instanceof IObject )
		{
			$aObject->setApplication($this->application(true)) ;
		}
		
		return $aObject ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function application($bDefaultGlobal=true)
	{
		if($this->aApplication)
		{
			return $this->aApplication ;
		}
		else 
		{
			return $bDefaultGlobal? Application::singleton(): null ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function setApplication(Application $aApp)
	{
		$this->aApplication = $aApp ;
	}

	private $aApplication ;
}
?>