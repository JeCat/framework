<?php
namespace jc\ui ;

use jc\lang\Exception;
use jc\lang\Object as JcObject;
use jc\fs\File;
use jc\fs\Dir;
use jc\fs\IFile;

class CompilerManager extends JcObject
{
	public function add($sObjectClass,$sCompilerClass)
	{
		$this->arrCompilers[$sObjectClass] = $sCompilerClass ;
	}
	
	public function remove($sObjectClass)
	{
		unset($this->arrCompilers[$sObjectClass]) ;
	}
	
	public function clear()
	{
		$this->arrCompilers = array() ;
	}
	
	/**
	 * @return ICompiler
	 */
	public function compiler(IObject $aObject)
	{
		$sObjectClass = get_class($aObject) ;
		if( !isset($this->arrCompilers[$sObjectClass]) )
		{
			return null ;
		}
		
		if( is_string($this->arrCompilers[$sObjectClass]) )
		{
			$this->arrCompilers[$sObjectClass] = new $this->arrCompilers[$sObjectClass]() ;
		}
		
		return $this->arrCompilers[$sObjectClass] ;
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
		
		$aCompiler = $this->compiler($aObject) ;
		$aCompiler->compile($aObject,$aWriter,$this) ;
		
		$aWriter->close() ;
	}
	
	protected function compileObject(IObject $aObject,OutputStream $aWriter)
	{
		
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