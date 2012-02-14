<?php
namespace org\jecat\framework\util ;

use org\jecat\framework\lang\Object;

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
	
	public function putRef(&$element) 
	{
		$this->arrDataStack[] =& $element ;
	}
	
	public function & outRef()
	{
		if(empty($this->arrDataStack))
		{
			$var = null ;
		}
		else
		{
			end($this->arrDataStack) ;
			$nIdx = key($this->arrDataStack) ;
			
			$var =& $this->arrDataStack[$nIdx] ;
			unset($this->arrDataStack[$nIdx]) ;
		}
		
		return $var ;
	}
	
	public function & getRef()
	{
		if(empty($this->arrDataStack))
		{
			$var = null ;
			return $var ;
		}
		else
		{
			end($this->arrDataStack) ;
			return $this->arrDataStack[key($this->arrDataStack)] ;
		}
	}
	
	public function length()
	{
		return count($this->arrDataStack) ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function iterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrDataStack) ;
	}

	private $arrDataStack ;
}

?>