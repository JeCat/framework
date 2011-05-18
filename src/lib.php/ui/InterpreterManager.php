<?php
namespace jc\ui ;

use jc\pattern\composite\Container;
use jc\util\String;
use jc\ui\Object as UIObject;
use jc\util\HashTable;

class InterpreterManager extends Container
{
	public function __construct()
	{
		$this->addAcceptClasses('jc\\ui\\IInterpreter') ;
	}
	
	public function add($aInterpreter,$bAdoptRelative=true)
	{
		parent::add($aInterpreter) ;
	}
	
	public function remove($aInterpreter)
	{
		parent::remove($aInterpreter) ;
	}
	
	public function iterate()
	{
		return new \ArrayIterator($this->arrInterpreters) ;
	}
	
	/**
	 * @return IObject
	 */
	public function parse($sSourcePath)
	{
		$aSource = String::createFromFile($sSourcePath) ;
		$aObjectContainer = new UIObject() ;
		
		foreach($this->iterator() as $aInterpreter)
		{
			$aInterpreter->parse($aSource,$aObjectContainer,$sSourcePath) ;
		}
		
		return $aObjectContainer ;
	}
}

?>