<?php
namespace org\jecat\framework\lang\compile\object ;

use org\jecat\framework\lang\compile\ClassCompileException;

class UseDeclare extends NamespaceString
{
	public function __construct(Token $aToken)
	{
		if( $aToken->tokenType()!=T_USE )
		{
			throw new ClassCompileException(null,$aToken,"参数 \$aToken 必须是一个 T_USE 类型的Token对象") ;
		}
		$this->cloneOf($aToken) ;
	}
	
	public function setAsNameToken(Token $aAsNameToken)
	{
		if( $aAsNameToken->tokenType()!=T_STRING )
		{
			throw new ClassCompileException(null,$aAsNameToken,"参数 \$aToken 必须是一个 T_STRING 类型的Token对象") ;
		}
		$this->aAsNameToken = $aAsNameToken ;
	}
	
	public function asNameToken()
	{
		return $this->aAsNameToken ;
	}
	
	public function fullName()
	{
		return parent::name() ;
	}
	
	public function name()
	{
		if($this->aAsNameToken)
		{
			return $this->aAsNameToken->sourceCode() ; 
		}
		else
		{
			$aLastToken = end($this->arrNameAndSlashes) ;
			return $aLastToken? $aLastToken->sourceCode(): null ;
		}
	}
	
	private $aAsNameToken ;
}
