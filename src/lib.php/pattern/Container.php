<?php
namespace jc\pattern ;

use jc\lang\Object;

class Container extends Object
{
	public function __construct()
	{ }
	
	protected function accept($object)
	{
		return true ;
	}

	public function add($object)
	{
		if( $this->accept($object) and !in_array($object,$this->arrObjects) )
		{
			$this->arrObjects[] = $object ;
		}
	}
	public function remove($object)
	{
		$nIdx = array_search($object,$this->arrObjects) ;
		if($nIdx!==false)
		{
			unset($this->arrObjects[$nIdx]) ;
		}
	}
	public function clear($object)
	{
		$this->arrObjects = array() ;
	}
	/**
	 * @return \Iterate
	 */
	public function iterator()
	{
		return new \ArrayIterator($this->arrObjects) ;
	}
	
	
	private $arrObjects = array() ;
}

?>