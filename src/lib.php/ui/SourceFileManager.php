<?php

namespace jc\ui ;

use jc\fs\FileSystem;

use jc\fs\IFile;
use jc\resrc\ResourceManager;

class SourceFileManager extends ResourceManager
{
	public function isCompiledValid(IFile $aSourceFile,IFile $aCompiledFile)
	{
		if($this->bForceCompile)
		{
			return false ;
		} 
		
		return $aCompiledFile->exists() and $aSourceFile->modifyTime()<=$aCompiledFile->modifyTime() ;
	}
	
	/**
	 * @return jc\fs\IFile
	 */
	public function findCompiled(IFile $aSourceFile)
	{
		return $aSourceFile->directory()->createFile(
			'compileds/'.$aSourceFile->name().'.php'
			, FileSystem::CREATE_FILE_DEFAULT | FileSystem::CREATE_ONLY_OBJECT
		) ;		
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