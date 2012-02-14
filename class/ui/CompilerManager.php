<?php
namespace org\jecat\framework\ui ;

use org\jecat\framework\io\OutputStreamBuffer;

use org\jecat\framework\io\IOutputStream;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object as JcObject;
use org\jecat\framework\fs\IFile;

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
	public function compile(ObjectContainer $aObjectContainer,IOutputStream $aCompiledOutput,$bPHPTag=true)
	{
		$aTargetCodeStream = new TargetCodeOutputStream ;
		$aTargetCodeStream->open($aCompiledOutput,$bPHPTag) ;
		
		// 变量声明 buffer
		$aBuffVarsDeclare = new OutputStreamBuffer() ;
		$aTargetCodeStream->write($aBuffVarsDeclare) ;
		
		// 编译正文
		foreach($aObjectContainer->iterator() as $aObject)
		{
			$aCompiler = $this->compiler($aObject) ;
			if($aCompiler)
			{
				$aCompiler->compile($aObject,$aObjectContainer,$aTargetCodeStream,$this) ;
			}
		}
		
		// 变量声明
		$aObjectContainer->variableDeclares()->make($aBuffVarsDeclare) ;

		$aTargetCodeStream->close($bPHPTag) ;
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

	public function compileStrategySignture()
	{
		$seed = __CLASS__."\r\n" ;
		foreach($this->arrCompilers as $compiler)
		{
			$seed.= (is_object($compiler)? $compiler->compileStrategySignture(): $compiler)."\r\n" ;
		}
		return md5($seed) ;
	}
	
	private $arrCompilers = array() ;
}

?>