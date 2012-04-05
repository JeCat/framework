<?php
namespace org\jecat\framework\cache ;

use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Assert;

class Memcache extends Cache
{
	public function __construct($sServer='127.0.0.1',$nPort=11211)
	{
		$this->hMemcacheConnection = memcache_connect($sServer,$nPort) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function item($sDataName)
	{
		$data = memcache_get($this->hMemcacheConnection,$sDataName) ;
		return unserialize($data) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function setItem($sName,$data,$nExpire=self::expire_default)
	{
		$data = serialize($data) ;
		return memcache_set($this->hMemcacheConnection,$sName,$data,0,$nExpire=self::expire_default) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function delete($sDataName)
	{
		return memcache_delete($this->hMemcacheConnection,$sDataName) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isExpire($sDataName)
	{
		return $this->get($sDataName)===null ;
	}
	
	
	private $hMemcacheConnection ;
}
?>