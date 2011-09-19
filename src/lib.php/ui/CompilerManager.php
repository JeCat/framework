<?php
namespace jc\ui ;

use jc\io\IOutputStream;

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
	public function compile(ObjectContainer $aObjectContainer,IOutputStream $aCompiledOutput)
	{
		$aTargetCodeStream = new TargetCodeOutputStream ;
		$aTargetCodeStream->open($aCompiledOutput) ;
		
		foreach($aObjectContainer->iterator() as $aObject)
		{
			$aCompiler = $this->compiler($aObject) ;
			if($aCompiler)
			{
				$aCompiler->compile($aObject,$aTargetCodeStream,$this) ;
			}
		}
				
		$aTargetCodeStream->close() ;
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
	
	private $arrCompilers = array() ;
}

?>