<?php
namespace jc\cache ;

interface ICache
{
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	function get($sDataName) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function set($sName,$sData,$nCreateTimeMicroSec=-1) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function delete($sDataName) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	function isExpire($sDataName,$nCreateTimeMicroSec=-1) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function createTime($sDataName) ;
	
}
?>