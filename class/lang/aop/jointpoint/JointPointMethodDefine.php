<?php
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\Token ;

class JointPointMethodDefine extends JointPoint
{
	public function __construct($sClassName,$sMethodNamePattern='*')
	{
		parent::__construct($sClassName,$sMethodNamePattern) ;
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
}
?>