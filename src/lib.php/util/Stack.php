<?php
namespace jc\util ;

use jc\lang\Object;

class Stack extends Object
{
	public function __construct(array $arrDataStack=array())
	{
		parent::__construct() ;
		
		$this->arrDataStack = $arrDataStack ;
	}

	public function isEmpty()
	{
		return count($this->arrDataStack)==0 ;
	}
	
	public function put($element) 
	{
		$this->arrDataStack[] = $element ;
	}
	
	public function get() 
	{
		return end($this->arrDataStack) ;
	}

	public function out()
	{
		if( count($this->arrDataStack) )
		{
			return array_pop($this->arrDataStack) ;
		}
		return null ;
	}
	
	public function length()
	{
		return count($this->arrDataStack) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function iterator()
	{
		return new \ArrayIterator($this->arrDataStack) ;
	}

	private $arrDataStack ;
}

?>