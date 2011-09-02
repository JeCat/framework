<?php
namespace jc\lang\aop ;

use jc\lang\Object;
use jc\lang\compile\object\Token;
use jc\lang\Exception;

abstract class JointPoint extends Object
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
		
		$aJointPoint->setExecutionPattern("{$sClassName}::{$sMethodNamePattern}()") ;
		
		$aJointPoint->setWeaveClass($sClassName) ;
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
	
	public function __construct($sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		$this->setWeaveClass($sWeaveClass) ;
		$this->setWeaveMethodNamePattern($sWeaveMethodNamePattern) ;
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
	
	public function setWeaveMethodNamePattern($sWeaveMethodNamePattern)
	{
		$this->sWeaveMethodNamePattern = $sWeaveMethodNamePattern ;
		$this->sWeaveMethodNameRegexp = self::transRegexp($sWeaveMethodNamePattern) ;
	}
	public function weaveMethodNamePattern()
	{
		return $this->sWeaveMethodNamePattern ;
	}
	public function weaveMethodNameRegexp()
	{
		return $this->sWeaveMethodNameRegexp ;
	}

	public function matchWeaveMethod(Token $aToken)
	{
		if( !$aClass=$aToken->belongsClass() or $aMethod=$aToken->belongsFunction() )
		{
			return false ;
		}
		
		if( $aClass->fullName()!=$this->weaveClass() )
		{
			return false ;
		}
		
		return preg_match( $this->weaveMethodNameRegexp(), $aMethod->name() ) ;
	}
	
	abstract public function matchExecutionPoint(Token $aToken) ;
	
	private $sWeaveClass ;
	
	private $sWeaveMethodNamePattern ;
	
	private $sWeaveMethodNameRegexp ;
	
}

?>