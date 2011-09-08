<?php
namespace jc\lang\compile\object ;

use jc\pattern\iterate\ArrayIterator;

use jc\pattern\composite\Container;

class TokenPool extends Container
{
	public function add($object,$sName=null,$bAdoptRelative=true)
	{
		parent::add($object,$sName,$bAdoptRelative) ;
	}
	
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
		
		if( !$sFuncName = $aFunction->name() )
		{
			$sFuncName = '' ;
		}
		
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
	
	/**
	 * @return Token
	 */
	public function findTokenBySource($sSource,$nSeek=0)
	{
		$nFound = 0 ;
		foreach($this->iterator() as $aToken)
		{
			if( $aToken->sourceCode()===$sSource )
			{
				if($nSeek===$nFound++)
				{
					return $aToken ;
				}
			}
		}
		
		return ;
	}

	public function classIterator()
	{
		return new ArrayIterator($this->arrClasses) ;
	}
	public function functionIterator($sClassName='')
	{
		return isset($this->arrMethods[$sClassName])?
					new ArrayIterator($this->arrMethods[$sClassName]):
					new ArrayIterator() ;
	}
	
	private $arrClasses = array() ;
	private $arrMethods = array() ;
}

?>