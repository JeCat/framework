<?php
namespace org\jecat\framework\util ;

interface IDataSrc extends IHashTable
{
	public function int($sName) ;
	public function float($sName) ;
	public function bool($sName) ;
	public function string($sName) ;
	public function quoteString($sName) ;
	
	public function addChild(IHashTable $aParams) ;
	public function removeChild(IHashTable $aParams) ;
	public function clearChild() ;
	
	public function childIterator() ;
	
	public function values(/*$sKey1,...$sKeyN*/) ;
	
	public function disableData($sName) ;
	public function enableData($sName) ;
	public function clearDisabled() ;
	
	public function toUrlQuery() ;
}

?>