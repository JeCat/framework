<?php
namespace org\jecat\framework\io ;

interface IRedirectable extends IOutputStream
{
	public function redirect(IOutputStream $aOutputStream) ;
	
	public function redirectionDev() ;
}

?>