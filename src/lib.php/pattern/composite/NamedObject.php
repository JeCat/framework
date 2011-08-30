<?php
namespace jc\pattern\composite ;

use jc\lang\Object;

abstract class NamedObject extends Object
{
	public function __construct($sName)
	{
		$this->setName($sName) ;
	}
	
	public function name()
	{
		return $this->sName ;
	}
	
	public function setName($sName)
	{
		$this->sName = $sName ;
	}
	
	private $sName ;
}

?>