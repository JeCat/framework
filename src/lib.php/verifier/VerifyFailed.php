<?php
namespace jc\verifier ;

use jc\lang\Exception ;

class VerifyFailed extends Exception
{
	public function add(VerifyFailed $aVerifyFaild){
		$this->arrVerify[] = $aVerifyFaild;
	}
	
	public function clear(){
		$this->arrVerify = array();
	}
	
	public function verifyFaildIterator(){
		return new \ArrayIterator ( $this->arrVerify );
	}
	
	private $arrVerify = array(); 
}

?>