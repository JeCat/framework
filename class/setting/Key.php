<?php
namespace org\jecat\framework\setting ;

use org\jecat\framework\lang\Object;

abstract class Key extends Object implements IKey
{
	public function item($sName='*',$sDefault=null)
	{
		if( !$this->hasItem($sName) )
		{
			if($sDefault===null)
			{
				return null ;
			}
			else 
			{
				$this->arrItems[$sName] = $sDefault ;
				$this->bDataChanged = true ;
			}
		}
		
		return $this->arrItems[$sName] ; 
	}
	
	public function setItem($sName,$value)
	{
		if( !array_key_exists($sName,$this->arrItems) or $this->arrItems[$sName]!==$value)
		{
			$this->bDataChanged = true ;
		}
		$this->arrItems[$sName] = $value ;
	}
	
	public function hasItem($sName)
	{
		return array_key_exists($sName,$this->arrItems) ;
	}
	
	public function deleteItem($sName)
	{
		unset($this->arrItems[$sName]) ;
		$this->bDataChanged = true ;
	}
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator()
	{
		return new \ArrayIterator( array_keys($this->arrItems) ) ;
	}
	
	public function __destruct()
	{
		if( $this->bDataChanged )
		{
			$this->save() ;
		}
	}
	
	// implements ArrayAccess	
	/**
	 * @param offset
	 */
	public function offsetExists ($offset)
	{
		return isset($this->arrItems[$offset]) ;
	}
	
	/**
	 * @param offset
	 */
	public function offsetGet ($offset)
	{
		return $this->item($offset) ;
	}
	
	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet ($offset, $value)
	{
		return $this->setItem($offset, $value) ;
	}
	
	/**
	 * @param offset
	 */
	public function offsetUnset ($offset)
	{
		return $this->deleteItem($offset) ;
	}
	
	protected $arrItems = array() ;
	
	protected $bDataChanged = false ;

}

?>