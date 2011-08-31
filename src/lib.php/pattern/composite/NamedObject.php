<?php
namespace jc\pattern\composite ;

use jc\lang\Object;

abstract class NamedObject extends Object implements INamable
{
	public function __construct($sName=null)
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