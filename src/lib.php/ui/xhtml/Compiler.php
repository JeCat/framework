<?php

namespace jc\ui\xhtml ;


use jc\fs\File;
use jc\lang\Type;
use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\IObject;
use jc\ui\CompilerBase;
use jc\util\match\RegExp;

class Compiler extends CompilerBase
{
	/**
	 * @return jc\ui\ICompiled
	 */
	public function loadCompiled($sCompiledPath)
	{
		return new Compiled($sCompiledPath) ;
	}
}

?>