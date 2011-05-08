<?php
namespace jc\ui ;

use jc\util\String;
use jc\lang\Object;
use jc\ui\Object as UIObject;
use jc\util\HashTable;

class InterpreterManager extends Object
{
	public function add(IInterpreter $aInterpreter)
	{
		$this->arrInterpreters[] = $aInterpreter ;
	}
	
	public function remove(IInterpreter $aInterpreter)
	{
		for( end($this->arrInterpreters); current($this->arrInterpreters); prev($this->arrInterpreters) )
		{
			if(current($this->arrInterpreters)===$aInterpreter)
			{
				unset( $this->arrInterpreters[ key($this->arrInterpreters) ] ) ;
				return true ;
			}
		}
		
		return false ;
	}
	
	public function clear()
	{
		$this->arrInterpreters = array() ;
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
		
		foreach($this->arrInterpreters as $aInterpreter)
		{
			$aInterpreter->parse($aSource,$aObjectContainer,$sSourcePath) ;
		}
		
		return $aObjectContainer ;
	}
	
	private $arrInterpreters = array() ;
}

?>