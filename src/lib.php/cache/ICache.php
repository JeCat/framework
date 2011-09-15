<?php
namespace jc\cache ;

interface ICache
{
	/**
	 * Enter description here ...
	 * 
	 * @return mixed
	 */
	function item($sDataPath) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function setItem($sDataPath,$data) ;
	
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