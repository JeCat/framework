<?php

namespace jc\ui ;

use jc\util\IHashTable;

interface IDisplayDevice
{
	public function hasRendered() ;
	
	public function destroy() ;
	
	public function show($bShow=true) ;
	
	public function render(ICompiled $aCompiled, IHashTable $aVariables=null) ;
}

?>