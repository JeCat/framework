<?php

namespace jc\util ;

interface IHashTable
{
	public function get($sName) ;
	public function set($sName,$Value) ;
	public function setRef($sName,&$Value) ;
	public function has($sName) ;	
	public function remove($sName) ;	
	public function clear() ;	
	public function count() ;
	
	public function end() ;
	
	public function nameIterator() ;
	public function valueIterator() ;
}

?>