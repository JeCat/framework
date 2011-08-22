<?php
namespace jc\compile\object ;

use jc\pattern\composite\Container;

class TokenPool extends Container
{
	public function addClass(ClassDefine $aClass)
	{
		$this->arrClasses[$aClass->fullName()] = $aClass ;
	}
	
	public function addFunction(FunctionDefine $aFunction)
	{
		if( $aClass=$aFunction->belongsClass() )
		{
			$sClassName = $aClass->fullName() ;
		}
		else
		{
			$sClassName = '' ;
		}
		
		$sFuncName = $aFunction->name() ; 
		
		$this->arrMethods[$sClassName][$sFuncName] = $aFunction ;
	}

	public function findClass($sClassName)
	{
		return isset($this->arrClasses[$sClassName])? $this->arrClasses[$sClassName]: null ;
	}
	
	public function findFunction($sFunctionName,$sClassName='')
	{
		return isset($this->arrMethods[$sClassName][$sFunctionName])? $this->arrMethods[$sClassName][$sFunctionName]: null ;
	}
	
	private $arrClasses = array() ;
	private $arrMethods = array() ;
}

?>