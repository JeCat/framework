<?php
namespace jc\lang\compile\interpreters\oop ;

use jc\lang\compile\object\NamespaceDeclare;
use jc\lang\compile\object\ClassDefine;
use jc\lang\compile\object\FunctionDefine;

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
}

?>