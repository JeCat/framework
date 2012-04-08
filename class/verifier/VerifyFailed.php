<?php
namespace org\jecat\framework\verifier ;

use org\jecat\framework\lang\Exception ;

class VerifyFailed extends Exception
{
	public function add(VerifyFailed $aVerifyFaild){
		$this->arrVerify[] = $aVerifyFaild;
	}
	
	public function clear(){
		$this->arrVerify = array();
	}
	
	public function verifyFaildIterator(){
		return new \org\jecat\framework\pattern\iterate\ArrayIterator ( $this->arrVerify );
	}
	
	private $arrVerify = array(); 
}

?>