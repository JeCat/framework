<?php
namespace jc\lang\aop ;

use jc\lang\Exception;

class JointPoint
{
	const ACCESS_SET = 'set' ;
	const ACCESS_GET = 'get' ;
	const ACCESS_ANY = '*' ;

	/**
	 * @return JointPoint
	 */
	static public function createDefineMethod($sClassName,$sMethodNamePattern='*')
	{		
		$aJointPoint = new self() ;
		$sClass = get_called_class();
		$aJointPoint->setExecutionPattern("{$sClassName}::{$sMethodNamePattern}()") ;
		$aJointPoint->setWeaveClass($sClass) ;
		$aJointPoint->setWeaveFunctionNamePattern($sMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createCallFunction($sCallFunctionNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		$aJointPoint = new self() ;
		$aJointPoint->setExecutionPattern("{$sCallFunctionNamePattern}()") ;
		$aJointPoint->setWeaveClass($sWeaveClass) ;
		$aJointPoint->setWeaveFunctionNamePattern($sWeaveMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createAccessProperty($sCallPropertyNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*',$sAccess=self::ACCESS_ANY)
	{
		if( !in_array($sAccess, array(self::ACCESS_SET,self::ACCESS_GET,self::ACCESS_ANY)) )
		{
			throw new Exception('参数$sAccess值不合法，必须为：%s，输入值为“%s”',array(implode(',', array(self::ACCESS_SET,self::ACCESS_GET,self::ACCESS_ANY)),$sAccess)) ;
		}
		
		$aJointPoint = new self() ;
		$aJointPoint->setExecutionPattern("->\${$sCallPropertyNamePattern} {$sAccess}") ;
		$aJointPoint->setWeaveClass($sWeaveClass) ;
		$aJointPoint->setWeaveFunctionNamePattern($sWeaveMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createThrowException($sThrowClassNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		$aJointPoint = new self() ;
		$aJointPoint->setExecutionPattern("throw {$sThrowClassNamePattern}") ;
		$aJointPoint->setWeaveClass($sWeaveClass) ;
		$aJointPoint->setWeaveFunctionNamePattern($sWeaveMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createNewObject($sNewClassNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		$aJointPoint = new self() ;
		$aJointPoint->setExecutionPattern("new {$sNewClassNamePattern}") ;
		$aJointPoint->setWeaveClass($sWeaveClass) ;
		$aJointPoint->setWeaveFunctionNamePattern($sWeaveMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	
	//////////////////////////////////////////////////////////////////
	
	public function setExecutionPattern($sPartten)
	{
		$this->setExecutionRegexp(self::transRegexp($sPartten)) ;
	}
	
	public function setExecutionRegexp($sRegexp)
	{
		$this->sExecutionRegexp = $sRegexp ;
	}
	
	public function executionRegexp()
	{
		return $this->sExecutionRegexp ;
	}
	
	public function setCallTrac($sPartten=null)
	{
		if($sPartten)
		{
			$this->sCallTracRegexp = self::transRegexp($sPartten) ;
		}
		else 
		{
			$this->sCallTracRegexp = null ;
		}
	}
	
	public function callTracRegexp()
	{
		return $this->sCallTracRegexp ;
	}
	
	static public function transRegexp($sPartten)
	{
		$sPartten = preg_quote($sPartten) ;
		$sPartten = str_replace('\\*', '.*', $sPartten) ;
		
		return '`' . $sPartten . '`is' ;
	}

	
	public function setWeaveClass($sWeaveClass)
	{
		$this->sWeaveClass = $sWeaveClass ;
	}
	public function weaveClass()
	{
		return $this->sWeaveClass ;
	}
	
	public function setWeaveFunctionNamePattern($sWeaveClass)
	{
		$this->sWeaveClass = $sWeaveClass ;
	}
	public function weaveFunctionNamePattern()
	{
		return $this->sWeaveClass ;
	}
	
	private $sExecutionRegexp ;
	
	private $sCallTracRegexp ;
	
	private $sWeaveClass ;
	
	private $sWeaveFunction ;
	
}

?>