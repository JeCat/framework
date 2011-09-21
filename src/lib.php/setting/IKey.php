<?php
namespace jc\setting ;

interface IKey
{
	public function item($sName='*',$sDefault=null) ;
	
	public function setItem($value,$sName) ;
	
	public function hasItem($sName) ;
	
	public function deleteItem($sName) ;
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator() ;
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator() ;
	
	public function save() ;
}

?>