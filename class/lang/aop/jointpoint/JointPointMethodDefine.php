<?php
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\compile\object\ClosureToken;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\Token ;
use org\jecat\framework\bean\BeanFactory;

class JointPointMethodDefine extends JointPoint
{
	public function __construct($sClassName=null,$sMethodNamePattern='*',$bMatchDerivedClass=false)
	{
		parent::__construct($sClassName,$sMethodNamePattern,$bMatchDerivedClass) ;
	}
	
	static public function createFromDeclare($sDeclare)
	{
		if( !preg_match('/^([\\w\\\\_]+)::([\\w_]+)(\[derived\])?$/i',$sDeclare,$arrRes) )
		{
			return null ;
		}
		return new self( $arrRes[1], $arrRes[2], @$arrRes[3]?true:false ) ;
	}
	
	public function exportDeclare($bWithClass=true)
	{
		return ($bWithClass?$this->weaveClass():'')."::".$this->weaveMethod() ;
	}
	
	public function matchExecutionPoint(Token $aToken)
	{		
		$bIsPattern = $this->weaveMethodIsPattern() ;
		
		// 模糊匹配每个方法
		if( $bIsPattern and ($aToken instanceof FunctionDefine) )
		{
			// 必须是一个类方法
			if( !$aClass=$aToken->belongsClass() )
			{
				return false ;
			}
			
			if( !$this->matchClass($aClass->fullName()) )
			{
				return false ;
			}
			
			return preg_match( $this->weaveMethodNameRegexp(),$aToken->name() )? true: false ;
		}
		
		// 精确匹配 class token
		else if( !$bIsPattern and ($aToken instanceof ClosureToken) )
		{
			// 必须是一个 "}" 
			if( $aToken->tokenType()!=Token::T_BRACE_CLOSE)
			{
				return false ;
			}
			
			// 必须成对
			if( !$aToken->theOther() ){
				return false;
			}
			
			$aClass=$aToken->theOther()->belongsClass();
			if( null === $aClass ){
				return false;
			}
			
			// 必须做为 class 的结束边界
			if($aToken->theOther()!==$aClass->bodyToken() )
			{
				return false ;
			}
			
			if( !$this->matchClass($aClass->fullName()) )
			{
				return false ;
			}
			
			return true ;
		}
	}
	
}
