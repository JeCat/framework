<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\object\NamespaceString;

use org\jecat\framework\lang\compile\ClassCompileException;

use org\jecat\framework\lang\compile\object\UseDeclare;

use org\jecat\framework\lang\compile\object\NamespaceDeclare;
use org\jecat\framework\lang\compile\object\ClassDefine;
use org\jecat\framework\lang\compile\object\FunctionDefine;

class State 
{
	/**
	 * @return bool
	 **/
	public function isPHPCode()
	{
		return $this->bPHPCode ;
	}
	
	public function setPHPCode($bPHPCode)
	{
		$this->bPHPCode = $bPHPCode? true: false ;
	}
	
	/**
	 * @return NamespaceDeclare
	 **/
	public function currentNamespace()
	{
		return $this->aCurrentNamespace ;
	}
	
	public function setCurrentNamespace(NamespaceDeclare $aCurrentNamespace=null)
	{
		$this->aCurrentNamespace = $aCurrentNamespace ;
	}
	
	/**
	 * @return FunctionDefine
	 **/
	public function currentFunction()
	{
		return $this->aCurrentFunction ;
	}
	
	public function setCurrentFunction(FunctionDefine $aCurrentFunction=null)
	{
		$this->aCurrentFunction = $aCurrentFunction ;
	}
	
	/**
	 * @return ClassDefine
	 **/
	public function currentClass()
	{
		return $this->aCurrentClass ;
	}
	
	public function setCurrentClass(ClassDefine $aCurrentClass=null)
	{
		$this->aCurrentClass = $aCurrentClass ;
	}
	
	public function addUseDeclare(UseDeclare $aUseToken)
	{
		if( !$sName = $aUseToken->name() )
		{
			throw new ClassCompileException(null,$aUseToken,"编译class时遇到无效的 use 关键词") ;
		}
		
		$this->arrNamespaces[$sName] = $aUseToken->fullName() ;
	}
	
	public function findName($sName)
	{
		if( isset($this->arrNamespaces[$sName]) )
		{
			return $this->arrNamespaces[$sName] ;
		}
		else if( $this->aCurrentNamespace )
		{
			return $this->aCurrentNamespace->name() . '\\' . $sName ;
		}
		else
		{
			return $sName ;
		}
	}
	
	/**
	 * @var bool
	  **/
	private $bPHPCode  = false ;
	
	/**
	 * @var ClassDefine
	  **/
	private $aCurrentClass  ;
	
	/**
	 * @var FunctionDefine
	  **/
	private $aCurrentFunction  ;
	
	/**
	 * @var NamespaceDeclare
	  **/
	private $aCurrentNamespace  ;
	
	private $arrNamespaces  ;
}

?>