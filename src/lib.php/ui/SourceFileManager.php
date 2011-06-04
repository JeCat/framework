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

	public function isForceCompile()
	{
		return $this->bForceCompile ;
	}
	
	public function setForceCompile($bForceCompile)
	{
		$this->bForceCompile = $bForceCompile ;
	}
	
	private $bForceCompile = false ;
}

?>