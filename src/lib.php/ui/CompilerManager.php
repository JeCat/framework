<?php
namespace jc\ui ;

use jc\lang\Exception;
use jc\lang\Object;
use jc\fs\File;
use jc\fs\Dir;
use jc\fs\IFile;

class CompilerManager extends Object
{
	public function add(ICompiler $aCompiler)
	{
		$this->arrCompilers[] = $aCompiler ;
	}
	
	public function remove(ICompiler $aCompiler)
	{
		for( end($this->arrCompilers); current($this->arrCompilers); prev($this->arrCompilers) )
		{
			if(current($this->arrCompilers)===$aCompiler)
			{
				unset( $this->arrCompilers[ key($this->arrCompilers) ] ) ;
				return true ;
			}
		}
		
		return false ;
	}
	
	public function clear()
	{
		$this->arrCompilers = array() ;
	}
	
	public function iterate()
	{
		return new \ArrayIterator($this->arrCompilers) ;
	}
	
	/**
	 * @return ICompiled
	 */
	public function compile(IObject $aObject,$sCompiledPath)
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
		
		$aObject->compile($aWriter) ;
		$aWriter->flush() ;
		$aWriter->close() ;
		
		return $this->loadCompiled($sCompiledPath) ;
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
	
	
	private $arrCompilers = array() ;
}

?>