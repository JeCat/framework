<?php

namespace jc\util ;

interface IFilterMangeger
{
	public function start()  ;
	
	public function stop()  ;
	
	public function isWorking() ;
	
	public function handle() ;
	
	public function add($callback,$arrArgvs=array()) ;
	
	/**
	 * @return callback
	 */
	public function remove($callback) ;
	
	public function removeStackTop() ;
	
	public function clear() ;
	
	/**
	 * 
	 * @return \Iterator
	 */
	public function iterator() ;
	
}

?>