<?php

namespace jc\ui\xhtml ;

use jc\ui\IDisplayDevice;
use jc\io\OutputStreamBuffer;

class StreamDisplayDevice extends OutputStreamBuffer implements IDisplayDevice
{
	public function hasRendered()
	{
		
	}
	
	public function destroy()
	{
		$this->clean() ;
	}
	
	public function show($bShow=true)
	{
		$this->flush() ;
	}
}

?>