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
	 * return jc\fs\IFile
	 */
	public function createCompiledFile($sCompiledPath)
	{
		$sCompiledDir = dirname($sCompiledPath) ;
		if( is_dir($sCompiledDir) )
		{
			if( !Dir::mkdir($sCompiledDir,0777,true) )
			{
				throw new Exception("无法创建编译文件目录：%s",array($sCompiledDir)) ;
			}
			
			return new File($sCompiledPath) ;
		}		
		
		return null ;
	}
	
	/**
	 * @return IObject
	 */
	public function loadCompiled($sCompiledPath)
	{
		$aObject = @include $sCompiledPath ;
		return ($aObject instanceof IObject)? $aObject: null ;
	}
	
	public function saveCompiled(IObject $aObject,$sCompiledPath)
	{
		$aFile = $this->createCompiledFile($sCompiledPath) ;
		if(!$aFile)
		{
			return false ;
		}
		
		$aWriter = $aFile->openWriter(false) ;
		$aWriter->write("<php return unserialize(\"".addslashes(serialize($aObject))."\"); ?>") ;
	}
	
	/**
	 * @return IObject
	 */
	public function compile($sSourcePath,$sCompiledPath)
	{
		if( $this->isCompiledValid($sSourcePath, $sCompiledPath) )
		{
			return $this->loadCompiled($sCompiledPath) ;
		}
		
		else
		{
			$aObject = $this->compileRaw($sCompiledPath) ;
						
			// save compiled
			$this->saveCompiled($aObject,$sCompiledPath) ;
			
			return $aObject ;
		}
	}
	
	/**
	 * @return IObject
	 */
	abstract public function compileRaw($sCompiledPath) ;
}

?>