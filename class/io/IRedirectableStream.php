<?php
namespace org\jecat\framework\io ;

interface IRedirectableStream extends IOutputStream
{
	public function redirect(IOutputStream $aOutputStream=null) ;
	
	public function redirectionDev() ;
}

?>