<?php
namespace jc\compile\object ;

use jc\pattern\composite\Container;

class TokenPool extends Container
{
	public function addClass(ClassDefine $aClass)
	{
		$this->arrClassesMethds[$aClass->fullName()] = $aClass ;
	}
	
	public function addFunction(FunctionDefine $aFunction)
	{
		if( $aClass=$aFunction->classDefine() )
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
	
	private $arrClasses = array() ;
	private $arrMethods = array() ;
}

?>