<?php
namespace jc\ui ;

use jc\lang\Exception;
use jc\lang\Object as JcObject;
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
	public function compilerByName($sName)
	{
		return isset($this->arrCompilers[$sName])? $this->arrCompilers[$sName]: null ;
	}
	
	/**
	 * @return ICompiler
	 */
	public function compiler(IObject $aObject)
	{
		for(end($this->arrCompilers);$Compiler=current($this->arrCompilers);prev($this->arrCompilers))
		{
			$sObjectClass = key($this->arrCompilers) ;
			
			if( $aObject instanceof $sObjectClass )
			{
				return is_string($Compiler)?
						$this->arrCompilers[$sObjectClass]=new $Compiler(): $Compiler ;
			}
		}
	}
	
	/**
	 * @return ICompiled
	 */
	public function compile(IObject $aObjectContainer,CompilingStatus $aCompilingStatus)
	{
		$this->aCompilingStatus = $aCompilingStatus ;
		
		$aFile = $aCompilingStatus->compiledFile() ;
		if( !$aFile->exists() )
		{
			if( !$aFile->create() )
			{
				throw new Exception("无法为 UI 创建编译文件：%s",$aFile->url()) ;
			}
		}

		$aWriter = $aFile->openWriter(false) ;
		if(!$aWriter)
		{
			throw new Exception("保存XHTML模板的编译文件时无法打开文件:%s",$aCompilingStatus->compiledFilepath()) ;
		}
		
		foreach($aObjectContainer->iterator() as $aObject)
		{
			$aCompiler = $this->compiler($aObject) ;
			if($aCompiler)
			{
				$aCompiler->compile($aObject,$aWriter,$this) ;
			}
		}
		
		$aWriter->close() ;
		
		$this->aCompilingStatus = null ;
	}
	
	public function createCompiledFile(IFile $aCompiledFile)
	{
		$aCompiledsDir = $aCompiledFile->directory() ;
		if( !$aCompiledsDir->exists() )
		{
			if( !$aCompiledsDir->create() )
			{
				throw new Exception("无法创建编译文件目录：%s",$aCompiledsDir->url()) ;
			}
		}

		if( !$aCompiledFile->exists() )
		{
			$aCompiledFile->create() ;
		}
	}
	
	/**
	 * @return CompilingStatus
	 */
	public function compilingStatus()
	{
		return $this->aCompilingStatus ;
	}
	
	private $arrCompilers = array() ;
	
	private $aCompilingStatus ;
}

?>