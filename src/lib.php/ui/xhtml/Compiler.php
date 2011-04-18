<?php

namespace jc\ui\xhtml ;


use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\IObject;
use jc\ui\CompilerBase;

class Compiler extends CompilerBase
{
	public function __construct()
	{
		$this->aTagLibrary = new TagLibrary() ;
	}

	public function tagLibrary()
	{
		return $this->aTagLibrary ;
	}
	public function setTagLibrary(TagLibrary $aTagLibrary)
	{
		$this->aTagLibrary = $aTagLibrary ;
	}

	/**
	 * @return IObject
	 */
	public function compileRaw($sCompiledPath)
	{
		
	}
	
	
	
	private $aTagLibrary ;
}

?>