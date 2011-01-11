<?php
namespace jc\lang ;

class Object
{
	public function initialize()
	{}
	
	/**
	 * Enter description here ...
	 * 
	 * @return Factory
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
	
	private $aFactory ;
}
?>