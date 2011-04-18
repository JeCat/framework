<?php

namespace jc\ui ;

interface IDisplayDevice
{
	public function hasRendered() ;
	
	public function destroy() ;
	
	public function show($bShow=true) ;
}

?>