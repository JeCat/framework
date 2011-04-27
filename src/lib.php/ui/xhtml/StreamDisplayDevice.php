<?php

namespace jc\ui\xhtml ;

use jc\util\IHashTable;
use jc\ui\ICompiled;
use jc\ui\IStreamDisplayDevice;
use jc\io\OutputStreamBuffer;

class StreamDisplayDevice extends OutputStreamBuffer implements IStreamDisplayDevice
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
	
	public function render(ICompiled $aCompiled, IHashTable $aVariables=null)
	{
		$aCompiled->render($aVariables,$this) ;
	}
}

?>