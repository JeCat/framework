<?php
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\Exception;

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
		return new JointPointMethodDefine($sClassName,$sMethodNamePattern) ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createCallFunction($sCallFunctionNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		return new JointPointCallFunction($sCallFunctionNamePattern,$sWeaveClass,$sWeaveMethodNamePattern) ;
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
		$aJointPoint->setWeaveMethod($sWeaveMethodNamePattern) ;
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
		$aJointPoint->setWeaveMethod($sWeaveMethodNamePattern) ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createNewObject($sNewClassNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		return new JointPointNewObject($sNewClassNamePattern,$sWeaveClass,$sWeaveMethodNamePattern) ;
	}
	
	
	//////////////////////////////////////////////////////////////////
	
	public function __construct($sWeaveClass,$sWeaveMethod='*')
	{
		$this->setWeaveClass($sWeaveClass) ;
		$this->setWeaveMethod($sWeaveMethod) ;
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
	public function setWeaveMethod($sWeaveMethod)
	{
		$this->sWeaveMethod = $sWeaveMethod ;
		$this->sWeaveMethodNameRegexp = self::transRegexp($sWeaveMethod) ;
	}
	public function weaveMethod()
	{
		return $this->sWeaveMethod ;
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
	
	public function weaveMethodIsPattern()
	{
		return preg_match('/^[^\w_]+$/',$this->sWeaveMethod) ;
	}
	
	abstract public function matchExecutionPoint(Token $aToken) ;
	
	private $sWeaveClass ;
	
	private $sWeaveMethod ;
	
	private $sWeaveMethodNameRegexp ;
	
}

?>