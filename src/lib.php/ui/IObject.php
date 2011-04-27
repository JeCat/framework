<?php

namespace jc\ui ;

use jc\io\IOutputStream;

interface IObject
{	
	public function depth() ;
	
	public function compile(IOutputStream $aDev,ICompiler $aCompiler) ;
}

?>