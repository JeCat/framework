<?php
namespace jc\lang ;

class Object
{	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\lang\Factory
	 */
	public function factory()
	{
		return $this->aFactory ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return Factory
	 */
	public function rootFactory()
	{
		return $this->aFactory? $this->aFactory->rootFactory(): null ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function setFactory(Factory $aFactory)
	{
		$this->aFactory = $aFactory ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return stdClass
	 */
	public function create($sClassName,$sNamespace='\\',array $arrArgvs=array())
	{
		if($this->aFactory)
		{
			return $this->aFactory->create($sClassName,$sNamespace,$arrArgvs) ;
		}
		else
		{
			return Factory::createNewObject($sClassName,$sNamespace,$arrArgvs) ;
		}
	}
	
	private $aFactory ;
}
?>