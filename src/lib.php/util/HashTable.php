<?php

namespace jc\util ;

use jc\lang\Object;

class HashTable extends Object implements IHashTable, \ArrayAccess, \Iterator
{
	public function __construct(array $arrDatas=array())
	{
		$this->arrDatas = $arrDatas ;
	}
	
	public function isEmpty()
	{
		return empty($this->arrDatas) ;
	}

	// implement IHashTable
	public function get($sName)
	{
		return isset($this->arrDatas[$sName])? $this->arrDatas[$sName]: null ;
	}

	public function set($sName,$Value)
	{
		$this->arrDatas[ $sName ] = $Value ;
	}
	public function setRef($sName,&$Value)
	{
		$this->arrDatas[ $sName ] = &$Value ;
	}

	public function has($sName)
	{
		return array_key_exists($this->arrDatas,$sName) ;
	}

	public function remove($sName)
	{
		unset($this->arrDatas[ $sName ]) ;
	}

	public function clear()
	{
		$this->arrDatas = array() ;
	}
	

	// implement ArrayAccess
	public function offsetExists($offset)
	{
		return $this->has($offset) ;	
	}

	public function offsetGet($offset)
	{	
		return $this->get($offset) ;
	}

	public function offsetSet($offset,$value)
	{
		return $this->set($offset,$value) ;		
	}

	public function offsetUnset($offset) {
		return $this->unset($offset) ;	
	}

	// implement Iterator
	/**
	 * 
	 * @return mixed
	 */
	public function current ()
	{
		return current($this->arrDatas) ;
	}

	/**
	 * 
	 * @return mixed
	 */
	public function next ()
	{
		return next($this->arrDatas) ;
	}

	/**
	 * 
	 * @return mixed
	 */
	public function key ()
	{
		return key($this->arrDatas) ;
	}

	/**
	 * 
	 * @return mixed
	 */
	public function valid ()
	{
		return each($this->arrDatas)!==false ;
	}

	public function rewind ()
	{
		return reset($this->arrDatas) ;
	}
	
	
	/**
	 * 
	 * @return \Iterator
	 */
	public function nameIterator() {
		return new \ArrayIterator(array_keys($this->arrDatas)) ;
	}

	/**
	 * 
	 * @return \Iterator
	 */
	public function valueIterator()
	{
		return new \ArrayIterator(array_values($this->arrDatas)) ;
	}
	
	
	protected $arrDatas = array() ;
}

?>