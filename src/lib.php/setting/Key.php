<?php
namespace jc\setting ;

use jc\lang\Object;

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
				return $this->arrItems[$sName] = $sDefault ;
			}
		}
		
		return $this->arrItems[$sName] ; 
	}
	
	public function setItem($value,$sName)
	{
		$this->arrItems[$sName] = $value ;
	}
	
	public function hasItem($sName)
	{
		return array_key_exists($sName,$this->arrItems) ;
	}
	
	public function deleteItem($sName)
	{
		unset($this->arrItems[$sName]) ;
	}
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator()
	{
		return new \ArrayIterator( array_keys($this->Items) ) ;
	}
	
	protected $arrItems = array() ;
	
	protected $bDataChanged = false ;

}

?>