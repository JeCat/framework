<?php

namespace jc\util ;

use jc\lang\Exception;
use jc\lang\Assert;
use jc\lang\Object;

class HashTable extends Object implements IHashTable, \ArrayAccess, \Iterator
{
	public function __construct(array $arrDatas=array(),$sClass=null)
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
		if( $this->sClass )
		{
			Assert::type($this->sClass,$Value,'Value') ;
		}
		
		$this->arrDatas[ $sName ] = $Value ;
	}
	public function setRef($sName,&$Value)
	{
		$this->arrDatas[ $sName ] = &$Value ;
	}

	public function has($sName)
	{
		return array_key_exists($sName,$this->arrDatas) ;
	}

	public function remove($sName)
	{
		unset($this->arrDatas[ $sName ]) ;
	}

	public function clear()
	{
		$this->arrDatas = array() ;
	}
	
	public function count()
	{
		return count($this->arrDatas) ;
	}

	public function end()
	{
		return end($this->arrDatas) ;
	}
	public function prev()
	{
		return prev($this->arrDatas) ;
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
		// 使用 null 作为数字索引，会被转换成空字符串 ''，因此可以使用 key()===null 来检查迭代状态
		//
		// $arr = array(null=>1,2,3) ;
		// key($arr)===''
		 
		return key($this->arrDatas)!==null ;
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
	
	public function add($Value)
	{
		$this->arrDatas[] = $Value ;
	}
	
	public function reverse () 
	{
		krsort($this->arrDatas) ;
	}
	
	public function acceptClass($sClass)
	{
		return $this->sClass ;
	}
	public function setAcceptClass($sClass)
	{
		$this->sClass = $sClass ;
	} 
	
	protected $arrDatas = array() ;
	
	private $sClass = array() ;
}

?>