<?php
namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\ui\ICompiler;
use org\jecat\framework\lang\Object as JcObject;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;

class BaseCompiler extends JcObject implements ICompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{	
		if( $aObject instanceof \org\jecat\framework\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			$aDev->write($aObject->source()) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		}
	}
		
	protected function compileChildren(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		foreach ($aObject->iterator() as $aChild)
		{
			if( $aCompiler = $aCompilerManager->compiler($aChild) )
			{
				$aCompiler->compile($aChild,$aDev,$aCompilerManager) ;
			}
		}
	}
	
	
	// sub compiler ---------------------------------------------------------------
	public function addSubCompiler($sName,$sCompilerClass) 
	{
		$sName = strtolower($sName) ;
		if( !isset($this->arrCompilers[ $sName ]) )
		{
			$this->arrCompilers[ $sName ] = $sCompilerClass ;
		}
	}
	public function setSubCompiler($sName,$sCompilerClass) 
	{
		$this->arrCompilers[ strtolower($sName) ] = $sCompilerClass ;
	}
	public function removeSubCompiler($sName)
	{
		unset($this->arrCompilers[ strtolower($sName) ]) ;
	}
	public function clearSubCompiler()
	{
		$this->arrCompilers = array() ;
	}

	/**
	 * @return ICompiler
	 */
	public function subCompiler($sName)
	{
		if( !isset($this->arrCompilers[$sName]) )
		{
			if( !isset($this->arrCompilers['*']) )
			{
				return null ;				
			}
			else 
			{
				$sName = '*' ;
			}
		}
		
		if( is_string($this->arrCompilers[$sName]) )
		{
			$this->arrCompilers[$sName] = new $this->arrCompilers[$sName]() ;
		}
		
		return $this->arrCompilers[$sName] ;
	}
	
	private $arrCompilers = array() ;
}

?>