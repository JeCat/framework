<?php
namespace jc\io ;

interface IClosable
{
	/**
	 * Enter description here ...
	 * 
	 * @throws
	 * @return bool
	 */
	public function close() ;

	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isActiving() ;
	
}
?>