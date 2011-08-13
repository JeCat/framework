<?php
namespace jc\ui\xhtml\compiler ;

use jc\ui\ICompiler;
use jc\lang\Object as JcObject;
use jc\lang\Type;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class BaseCompiler extends JcObject implements ICompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{	
		if( $aObject instanceof \jc\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			$aDev->write($aObject->source()) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		}
	}
		
	protected function compileChildren(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
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