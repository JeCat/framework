<?php
namespace jc\lang ;

class Object
{
	public function initialize()
	{}
	
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
	public function create($sClassName,array $arrArgvs=array())
	{
		if($this->aFactory)
		{
			return $this->aFactory->create($sClassName,$arrArgvs) ;
		}
		else
		{
			return Factory::createNewObject($sClassName,$arrArgvs) ;
		}
	}
	
	private $aFactory ;
}
?>