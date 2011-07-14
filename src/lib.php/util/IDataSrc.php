<?php
namespace jc\util ;

interface IDataSrc extends IHashTable
{
	public function int($sName) ;
	public function float($sName) ;
	public function bool($sName) ;
	public function string($sName) ;
	public function quoteString($sName) ;
	
	public function addChild(IDataSrc $aParams) ;
	public function removeChild(IDataSrc $aParams) ;
	public function clearChild() ;
	
	public function childIterator() ;
	
	public function values(/*$sKey1,...$sKeyN*/) ;
}

?>