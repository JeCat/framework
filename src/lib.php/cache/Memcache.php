<?php
namespace jc\cache ;

class Memcache implements ICache
{
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	function get($sDataName)
	{
		// todo ...
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function set($sName,$sData,$nCreateTimeMicroSec=-1)
	{
		// todo ...
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function delete($sDataName)
	{
		// todo ...
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function isExpire($sDataName,$nCreateTimeMicroSec=-1)
	{
		// todo ...
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function createTime($sDataName)
	{
		// todo ...
	}
}
?>