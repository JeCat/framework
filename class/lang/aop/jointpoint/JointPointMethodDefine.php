<?php
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\compile\object\ClosureToken;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\Token ;
use org\jecat\framework\bean\BeanFactory;

class JointPointMethodDefine extends JointPoint
{
	public function __construct($sClassName=null,$sMethodNamePattern='*')
	{
		parent::__construct($sClassName,$sMethodNamePattern) ;
	}
	
	public function exportDeclare($bWithClass=true)
	{
		return '[define method]'.($bWithClass?$this->weaveClass():'')."::".$this->weaveMethod().'() ;' ;
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
			
			if( $aClass->fullName()!=$this->weaveClass() )
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
			
			if( $aClass->fullName()!=$this->weaveClass() )
			{
				return false ;
			}
			
			return true ;
		}
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',BeanFactory $aBeanFactory=null)
	{
		$sTarget = str_replace('(','',$arrConfig['target']) ;
		$sTarget = str_replace(')','',$sTarget) ;
		list($sClass,$sMethod) = explode('::',$sTarget) ;
		$this->setWeaveClass(trim($sClass)) ;
		$this->setWeaveMethod(trim($sMethod)) ;
		
		// empty($arrConfig['derived'])? false: true ;
	}
}
