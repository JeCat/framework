<?php
namespace org\jecat\framework\cache ;

use org\jecat\framework\lang\Object;

abstract class Cache extends Object
{
	const expire_allways = -1 ;
	const expire_default = null ;

	/**
	 * @return Cache
	 */
	static public function singleton ($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		if( !$aSingleton = parent::singleton(false,null,$sClass) )
		{
			$aSingleton = new EmptyCache() ;
			self::setSingleton($aSingleton,$sClass) ;
		}
		
		return $aSingleton ;
	}
	
	static public function highSpeed()
	{
		return self::flyweight('high-speed',false)?: self::singleton(false) ;
	}
	
	static protected $expire_default = 900 ;
	
	static private $aSingleton ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return mixed
	 */
	abstract public function item($sDataPath) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	abstract public function setItem($sDataPath,$data,$nExpire=self::expire_default) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	abstract public function delete($sDataPath) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	abstract public function isExpire($sDataPath) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return float
	 */
	//abstract public function createTime($sDataPath) ;

	/**
	 * Enter description here ...
	 *
	 * @return int
	 */
	//abstract public function expireTime($sDataPath) ;
}
