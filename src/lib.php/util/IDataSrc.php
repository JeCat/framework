<?php
namespace jc\util ;

interface IDataSrc extends IHashTable, \ArrayAccess
{
	public function int($sName) ;
	public function float($sName) ;
	public function bool($sName) ;
	public function string($sName) ;
	public function quoteString($sName) ;
	
	public function addChild(IDataSrc $aParams) ;
	public function removeChild(IDataSrc $aParams) ;
	public function clearChild() ;
	
	public function nameIterator() ;
	public function valueIterator() ;
	public function childIterator() ;
}

?>