<?php

namespace jc\lang ;

interface IException
{
	public function message($sLanguage) ;
	
	public function code() ;
	
	public function file() ;
	
	public function line() ;
	
	public function trace() ;
	
	public function __toString() ;
	
}

?>