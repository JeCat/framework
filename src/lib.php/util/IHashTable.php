<?php

namespace jc\util ;

interface IHashTable extends \Iterator
{
	public function get($sName) ;
	public function set($sName,$Value) ;
	public function setRef($sName,&$Value) ;
	public function has($sName) ;	
	public function remove($sName) ;	
	public function clear() ;	
}

?>