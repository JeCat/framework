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
		return $aSourceFile->directory()->findFile('compileds/'.$this->compiledSubFolderName().'/'.$aSourceFile->name().'.php') ;	
	}
	
	/**
	 * @return jc\fs\IFile
	 */
	public function createCompiled(IFile $aSourceFile)
	{
		return $aSourceFile->directory()->createFile('compileds/'.$this->compiledSubFolderName().'/'.$aSourceFile->name().'.php') ;		
	}
	
	/**
	 * 这是一个临时方案，如果去要使所有已编译的 template 失效（ui机制发生变化），修改 md5() 中的数字
	 */
	private function compiledSubFolderName()
	{
		if( !$this->sCompiledSubFolderName )
		{
			$this->sCompiledSubFolderName = md5(1) ;
		}
		
		return $this->sCompiledSubFolderName ;
	}  
	

	public function isForceCompile()
	{
		return $this->bForceCompile ;
	}
	
	public function setForceCompile($bForceCompile)
	{
		$this->bForceCompile = $bForceCompile ;
	}
	
	private $sCompiledSubFolderName ;
	
	private $bForceCompile = false ;
}

?>