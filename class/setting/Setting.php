<?php
namespace org\jecat\framework\setting;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Object;

abstract class Setting extends Object implements ISetting
{	
	public function saveKey($sPath)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			return;
		}
		$aKey->save ();
	}
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		$aKey = $this->key ( $sPath );
		
		if (! $aKey)
		{
			return new \EmptyIterator ();
		}
		
		return $aKey->keyIterator ();
	}
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator($sPath)
	{
		$aKey = $this->key ( $sPath );
		
		if (! $aKey)
		{
			return new \EmptyIterator ();
		}
		
		return $aKey->itemIterator ();
	}
	
	public function item($sPath,$sName='*',$defaultValue=null)
	{
		if (!$aKey=$this->key($sPath,$defaultValue!==null))
		{
			return null;
		}
		return $aKey->item($sName,$defaultValue) ;
	}
	
	public function setItem($sPath, $sName, $value)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			if( !$aKey=$this->createKey($sPath) )
			{
				throw new Exception("无法保存配置建：%s",$sPath) ;
			}
		}
		$aKey->setItem ( $sName, $value );
	}
	
	public function hasItem($sPath, $sName)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			return null;
		}
		return $aKey->hasItem ( $sName );
	}
	
	public function deleteItem($sPath, $sName)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			return;
		}
		$aKey->deleteItem ( $sName );
	}
}
?>