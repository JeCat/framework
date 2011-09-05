<?php
namespace jc\lang\aop\jointpoint ;

use jc\lang\compile\object\FunctionDefine;
use jc\lang\compile\object\Token ;

class JointPointCallFunction extends JointPoint
{
	public function __construct($sCallFunctionNamePattern,$sWeaveClass,$sWeaveMethodNamePattern='*')
	{
		parent::__construct($sWeaveClass,$sWeaveMethodNamePattern) ;
		
		$this->sCallFunctionNamePattern = $sCallFunctionNamePattern ;
		$this->sCallFunctionNameRegexp = self::transRegexp($sCallFunctionNamePattern) ;
	}
	
	public function matchExecutionPoint(Token $aToken)
	{		
		// 必须是一个类方法
		if( !($aToken instanceof FunctionDefine) or !$aClass=$aToken->belongsClass() )
		{
			return false ;
		}
		
		if( $aClass->fullName()!=$this->weaveClass() )
		{
			return false ;
		}
		
		return preg_match( $this->weaveMethodNameRegexp(),$aToken->name() )? true: false ;
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
	
	
	private $sCallFunctionNamePattern ;
	
	private $sCallFunctionNameRegexp ;
}

?>