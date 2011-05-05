<?php
namespace jc\ui\xhtml ;

use jc\ui\ICompiler;
use jc\io\IOutputStream;

class TransparentObject extends ObjectBase 
{
	public function compile(IOutputStream $aDev,ICompiler $aCompiler)
	{
		foreach($this->childrenIterator() as $aObject)
		{
			$aObject->compile($aDev,$aCompiler) ;
		}
	}
}

?>