<?php
namespace jc\setting ;

use jc\lang\Object;

abstract class Setting extends Object implements ISetting
{
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		$aKey = $this->key($sPath) ;
		
		if(!$aKey)
		{
			return new \EmptyIterator() ;			
		}
		
		return $aKey->keyIterator() ;
	}
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator($sPath)
	{
		$aKey = $this->key($sPath) ;
		
		if(!$aKey)
		{
			return new \EmptyIterator() ;			
		}
		
		return $aKey->itemIterator() ;
	}
	
	public function item($sPath,$sName='*',$defaultValue=null)
	{
		if(!$aKey = $this->key($sPath))
		{
			return null ;			
		}
		return $aKey->item($sName,$defaultValue) ;
	}
	
	public function setItem($sPath,$sName,$value)
	{
		if(!$aKey = $this->key($sPath))
		{
			$aKey = $this->createKey($sPath);
		}
		$aKey->setItem($sName,$value) ;
	}
	
	public function hasItem($sPath,$sName)
	{
		if(!$aKey = $this->key($sPath))
		{
			return null ;			
		}
		return $aKey->hasItem($sName) ;
	}
	
	public function deleteItem($sPath,$sName)
	{
		if(!$aKey = $this->key($sPath))
		{
			return null ;			
		}
		return $aKey->deleteItem($sName) ;
	}
}

?>