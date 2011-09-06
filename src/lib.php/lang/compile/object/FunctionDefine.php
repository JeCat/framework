<?php
namespace jc\lang\compile\object ;

use jc\lang\compile\ClassCompileException;

class FunctionDefine extends StructDefine
{
	public function __construct( Token $aToken, $aTokenName=null, Token $aTokenArgList=null, Token $aTokenBody=null )
	{
		parent::__construct($aToken,$aTokenName,$aTokenBody) ;
		
		if($aTokenArgList)
		{
			$this->setArgListToken($aTokenArgList) ;
		}
		
		$this->setBelongsFunction($this) ;
	}

	public function argListToken()
	{
		return $this->aTokenArgList ;
	}
	public function setArgListToken(ClosureToken $aTokenArgList)
	{
		if( $aTokenArgList->tokenType()!=Token::T_BRACE_ROUND_OPEN or $aTokenArgList->sourceCode()!='(' )
		{
			throw new ClassCompileException(null,$aTokenArgList,"参数 \$aTokenArgList 必须是一个内容为 “(” 的Token对象") ;
		}
		
		$this->aTokenArgList = $aTokenArgList ;
	}

	public function accessToken()
	{
		return $this->aAccessToken ;		
	}
	public function setAccessToken(Token $aAccessToken)
	{
		if( !in_array($aAccessToken->tokenType(),array(T_PRIVATE,T_PROTECTED,T_PUBLIC)) )
		{
			throw new ClassCompileException(null,$aAccessToken,"参数 \$aAccessToken 必须为 T_PRIVATE, T_PROTECTED 或 T_PUBLIC 类型的Token对象") ;
		} 
		
		$this->aAccessToken = $aAccessToken ;
	}
	public function staticToken()
	{
		return $this->aStaticToken ;		
	}
	public function setStaticToken(Token $aStaticToken)
	{
		if( $aStaticToken->tokenType()!==T_STATIC )
		{
			throw new ClassCompileException(null,$aStaticToken,"参数 \$aStaticToken 必须为 T_STATIC 类型的Token对象") ;
		} 
		
		$this->aStaticToken = $aStaticToken ;
	}
	public function abstractToken()
	{
		return $this->aAbstractToken ;		
	}
	public function setAbstractToken(Token $aAbstractToken)
	{
		if( $aAbstractToken->tokenType()!==T_ABSTRACT )
		{
			throw new ClassCompileException(null,$aAbstractToken,"参数 \$aAbstractToken 必须为 T_ABSTRACT 类型的Token对象") ;
		}
		$this->aAbstractToken = $aAbstractToken ;
	}
	private $aTokenArgList ;
	private $aAccessToken ;
	private $aStaticToken ;
	private $aAbstractToken ;
}

?>