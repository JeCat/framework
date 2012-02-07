<?php

namespace org\jecat\framework\util ;

interface IHashTable
{
	public function get($sName) ;
	public function &getRef($sName) ;
	public function set($sName,$Value) ;
	public function setRef($sName,&$Value) ;
	public function has($sName) ;
	public function hasValue($value) ;	
	public function remove($sName) ;	
	public function clear() ;	
	public function count() ;
	
	public function __get($sName) ;
	public function __set($sName,$Value) ;
	
	public function end() ;
	
	public function nameIterator() ;
	public function valueIterator() ;
}

?>