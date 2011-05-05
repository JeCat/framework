<?php

namespace jc\ui ;

use jc\util\ResourceManager;

class SourceFileManager extends ResourceManager
{
	public function isCompiledValid($sSourcePath,$sCompiledPath)
	{
		if($this->bForceCompile)
		{
			return false ;
		} 
		
		return is_file($sCompiledPath) and filemtime($sSourcePath)<=filemtime($sCompiledPath) ;
	}
	
	public function compiledPath($sSourcePath)
	{
		return dirname($sSourcePath).'/compileds/'.basename($sSourcePath).'.php' ;
	}
	
	private $bForceCompile = true ;
}

?>