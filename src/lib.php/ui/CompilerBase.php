<?php
namespace jc\ui ;

use jc\fs\File;
use jc\fs\IFile;
use jc\lang\Exception;
use jc\fs\Dir;
use jc\lang\Object;

abstract class CompilerBase extends Object implements ICompiler
{
	public function isCompiledValid($sSourcePath,$sCompiledPath)
	{
		return is_file($sCompiledPath) and filemtime($sSourcePath)<=filemtime($sCompiledPath) ;
	}

	/**
	 * return IFile
	 */
	public function createCompiledFile($sCompiledPath)
	{
		$sCompiledDir = dirname($sCompiledPath) ;
		
		if( !is_dir($sCompiledDir) )
		{
			if( !Dir::mkdir($sCompiledDir,0777,true) )
			{
				throw new Exception("无法创建编译文件目录：%s",array($sCompiledDir)) ;
			}
		}

		return new File($sCompiledPath) ;
	}
	
	public function saveCompiled(IObject $aObject,$sCompiledPath)
	{
		$aFile = $this->createCompiledFile($sCompiledPath) ;
		if(!$aFile)
		{
			return false ;
		}
		
		$aWriter = $aFile->openWriter(false) ;
		if(!$aWriter)
		{
			throw new Exception("保存XHTML模板的编译文件时无法打开文件:%s",$sCompiledPath) ;
		}
		
		$aObject->compile($aWriter,$this) ;
		$aWriter->flush() ;
		$aWriter->close() ;
	}
	
	/**
	 * @return ICompiled
	 */
	public function compile($sSourcePath,$sCompiledPath)
	{
		if( $this->bForceCompile or !$this->isCompiledValid($sSourcePath, $sCompiledPath) )
		{
			$aObjectTree = $this->buildObjectTree($sSourcePath) ;
						
			// save compiled
			$this->saveCompiled($aObjectTree,$sCompiledPath) ;
		}
		
		return $this->loadCompiled($sCompiledPath) ;
	}
	
	/**
	 * @return IObject
	 */
	abstract protected function buildObjectTree($sSourcePath) ;
	
	
	private $bForceCompile = true ;
}

?>