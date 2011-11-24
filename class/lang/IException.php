<?php

namespace jc\lang ;

use jc\locale\ILocale ;

interface IException
{
	public function message(ILocale $aLocale=null) ;
	
	public function code() ;
	
	public function file() ;
	
	public function line() ;
	
	public function trace() ;
	
}

?>