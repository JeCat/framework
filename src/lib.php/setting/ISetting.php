<?php
namespace jc\setting ;

interface ISetting
{
	/**
	 * @return IKey 
	 */
	public function key($sPath) ;
	
	public function createKey($sPath) ;
	
	public function hasKey($sPath) ;
	
	public function deleteKey($sPath) ;
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath) ;
	
	
	public function item($sPath,$sName='*') ;
	
	public function setItem($sPath,$sName,$value) ;
	
	public function hasItem($sPath) ;
	
	public function deleteItem($sPath,$sName) ;
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator($sPath) ;
}

?>