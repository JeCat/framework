<?php
namespace org\jecat\framework\cache ;

class EmptyCache extends Cache
{
	public function itemData($sDataPath)
	{
		return null ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return void
	 */
	public function setItemData($sDataPath,$data,$nExpire=self::expire_default)
	{}
	
	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function deleteItemData($sDataPath)
	{}
	
	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function isDataExpire($sDataPath)
	{
		return true ;
	}
}

