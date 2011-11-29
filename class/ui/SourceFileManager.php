<?php

namespace org\jecat\framework\ui ;

use org\jecat\framework\fs\FileSystem;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\fs\IFolder;
use org\jecat\framework\resrc\ResourceManager;

class SourceFileManager extends ResourceManager
{
	public function addFolder(IFolder $aFolder,IFolder $aCompiled=null,$sNamespace='*')
	{		
		$aFolder->setProperty('compiled',$aCompiled) ;
		
		parent::addFolder($aFolder,$sNamespace) ;
	}
	
	public function isCompiledValid(IFile $aSourceFile,IFile $aCompiledFile)
	{
		if($this->bForceCompile)
		{
			return false ;
		} 
		
		return $aCompiledFile->exists() and $aSourceFile->modifyTime()<=$aCompiledFile->modifyTime() ;
	}

	/**
	 * @return org\jecat\framework\fs\IFile
	 */
	public function findCompiled(IFile $aSourceFile)
	{
		$aFolder = $aSourceFile->directory() ;
		
		if( $aCompiledFolder = $aFolder->property('compiled') )
		{
			return $aCompiledFolder->findFile($this->compileStrategySignture().'/'.$aSourceFile->name().'.php') ;
		}
		else
		{
			return $aSourceFile->directory()->findFile("compileds/".$this->compileStrategySignture()."/".$aSourceFile->name().'.php') ;	
		}
	}
	
	/**
	 * @return org\jecat\framework\fs\IFile
	 */
	public function createCompiled(IFile $aSourceFile)
	{
		$aFolder = $aSourceFile->directory() ;
		
		if( $aCompiledFolder = $aFolder->property('compiled') )
		{
			return $aCompiledFolder->createFile($this->compileStrategySignture().'/'.$aSourceFile->name().'.php') ;
		}
		else
		{
			return $aSourceFile->directory()->createFile("compileds/".$this->sCompileStrategySignture."/".$aSourceFile->name().'.php') ;		
		}
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
	public function serialize()
	{
		$arrData = array(
			'arrFolders' => array() ,
		) ;
		
		foreach($this->folderNamespacesIterator() as $sNamespace)
		{
			foreach($this->folderIterator($sNamespace) as $aFolder)
			{
				$aCompiled = $aFolder->property('compiled') ;
				$arrData['arrFolders'][$sNamespace][] = array( $aFolder->path(), $aCompiled?$aCompiled->path():null ) ;
			}
		}
		
		return serialize($arrData) ;
	}

	public function unserialize($serialized)
	{
		$this->__construct() ;
		
		$aFileSystem = FileSystem::singleton() ;
		$arrData = unserialize($serialized) ;
		foreach($arrData['arrFolders'] as $sNamespace=>&$arrFolders)
		{
			foreach($arrFolders as &$array)
			{
				$this->addFolder(
					$aFileSystem->findFolder($array[0])
					, $aFileSystem->findFolder($array[1])
					, $sNamespace
				) ;
			}
		}
	}
	
	private $sCompileStrategySignture ;
	
	private $bForceCompile = false ;
}

?>