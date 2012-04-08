<?php

namespace org\jecat\framework\ui ;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\fs\File;
use org\jecat\framework\resrc\ResourceManager;

class SourceFileManager extends ResourceManager
{
	public function setCompiledFolderPath($sPath)
	{
		$this->sCompiledFolderPath = $sPath ;
	}
	public function compiledFolderPath()
	{
		return $this->sCompiledFolderPath ;
	}
	
	public function isCompiledValid(File $aSourceFile,File $aCompiledFile)
	{
		if($this->bForceCompile)
		{
			return false ;
		} 
		
		return $aCompiledFile->exists() and $aSourceFile->modifyTime()<=$aCompiledFile->modifyTime() ;
	}

	/**
	 * @return org\jecat\framework\fs\File
	 */
	public function findCompiled($sSourceFile,$sNamespace,$bAutoCreate=false)
	{
		$sPath = $this->compiledFolderPath() . '/' . $this->compileStrategySignture() . '/' . $sNamespace . '/' . $sSourceFile . '.php' ; 
		return Folder::singleton()->findFile($sPath,$bAutoCreate?Folder::FIND_AUTO_CREATE:0) ;
	}
	
	public function setCompileStrategySignture($sCompileStrategySignture)
	{
		$this->sCompileStrategySignture = $sCompileStrategySignture ;
	} 
	public function compileStrategySignture()
	{
		if(!$this->sCompileStrategySignture)
		{
			$this->sCompileStrategySignture = md5(__CLASS__) ;
		}
		return $this->sCompileStrategySignture ;
	}
	
	public function isForceCompile()
	{
		return $this->bForceCompile ;
	}
	
	public function setForceCompile($bForceCompile)
	{
		$this->bForceCompile = $bForceCompile ;
	}
	
	private $sCompileStrategySignture ;
	
	private $sCompiledFolderPath = '/data/compiled/template' ;
	
	private $bForceCompile = false ;
	
}

?>