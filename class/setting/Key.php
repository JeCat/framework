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
	
	protected $arrItems = array() ;
	
	protected $bDataChanged = false ;

}

?>