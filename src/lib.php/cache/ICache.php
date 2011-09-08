<?php
namespace jc\cache ;

interface ICache
{
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	function get($sDataPath) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function set($sDataPath,$sData) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function delete($sDataPath) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function isExpire($sDataPath,$fValidSec) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function createTime($sDataPath) ;
	
}
?>