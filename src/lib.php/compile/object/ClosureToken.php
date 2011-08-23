<?php
namespace jc\compile\object ;

use jc\lang\Exception;
use jc\compile\ClassCompileException;

class ClosureToken extends Token 
{
	public function __construct(Token $aToken)
	{
		if( !array_key_exists($aToken->tokenType(),self::$arrClosureObjectBeginTypes) and !array_key_exists($aToken->tokenType(),self::$arrClosureObjectEndTypes) )
		{
			throw new ClassCompileException(
				$aToken
				,"参数 \$aToken 传入的不是一个有效的闭合token。该参数只接受以下类型的token:".implode(', ', array_merge(self::$arrClosureObjectBeginTypes,self::$arrClosureObjectEndTypes) )
			) ;
		}
		
		$this->cloneOf($aToken) ;
	}
	
	public function isOpen()
	{
		return array_key_exists($this->tokenType(),self::$arrClosureObjectBeginTypes) ;
	}

	public function theOther()
	{
		return $this->aTheOther ;
	}
	public function setTheOther(self $aToken=null)
	{
		// 先解除原有配对的token关系
		if( $this->aTheOther )
		{
			// nothing to do
			if($this->aTheOther===$aToken)
			{
				return ;
			}
			
			$this->aTheOther->setTheOther(null) ;
			$this->aTheOther = null ;
		}
		
		// 设置一个配对的 闭合token
		if($aToken)
		{
			$thisTokenType = $this->tokenType() ;
			
			if( $this->isOpen() )
			{
				if( !self::isPair($thisTokenType,$aToken->tokenType()) )
				{
					throw new ClassCompileException($aToken,"遇到意外的闭合token类型，“%s”和“%s”类型不匹配。",array($thisTokenType,$aToken->tokenTypeName())) ;
				}
			}
			
			else 
			{
				if( !self::isPair($aToken->tokenType(),$thisTokenType) )
				{
					throw new ClassCompileException($aToken,"遇到意外的闭合token类型，“%s”和“%s”类型不匹配。",array($thisTokenType,$aToken->tokenTypeName())) ;
				}
			}
			
			$this->aTheOther = $aToken ;
			$aToken->aTheOther = $this ;
		}
	}

	static public function openClosureSymbols()
	{
		return call_user_func_array('array_merge',self::$arrClosureObjectBeginTypes) ;
	}
	static public function closeClosureSymbols()
	{
		return call_user_func_array('array_merge',self::$arrClosureObjectEndTypes) ;
	}
	
	static public function openClosureTokens()
	{
		return array_keys(self::$arrClosureObjectBeginTypes) ;
	}
	static public function closeClosureTokens()
	{
		return array_keys(self::$arrClosureObjectEndTypes) ;
	}

	static public function closureTokenPairs()
	{
		return self::$arrClosureTokenPairs ;
	}

	static public function isPair($openTokenType,$closeTokenType)
	{
		foreach(self::$arrClosureTokenPairs as $arrPair)
		{
			if( $arrPair[0]===$openTokenType and $arrPair[1]===$closeTokenType)
			{
				return true ;
			}
		}
		
		return ;
	}
	
	static private $arrClosureObjectBeginTypes = array(
			Token::T_BRACE_OPEN => array('{') ,
			Token::T_BRACE_SQUARE_OPEN => array('[') ,
			Token::T_BRACE_ROUND_OPEN => array('(') ,
			T_OPEN_TAG => array('<?','<?php') ,
			T_OPEN_TAG_WITH_ECHO => array('<?=') ,
			T_DOLLAR_OPEN_CURLY_BRACES => array('${') ,		// "ooo${xxx}ooo"
			T_CURLY_OPEN => array('{') ,					// "ooo{$xxx}ooo"
	/*
			'{' => Token::T_BRACE_OPEN ,
			'[' => Token::T_BRACE_SQUARE_OPEN ,
			'(' => Token::T_BRACE_ROUND_OPEN ,
			'<?' => T_OPEN_TAG ,
			'<?php' => T_OPEN_TAG ,
			'<?=' => T_OPEN_TAG_WITH_ECHO ,
			'{$' => T_DOLLAR_OPEN_CURLY_BRACES ,		
			'{' => T_CURLY_OPEN , */						
	) ;	
	
	static private $arrClosureObjectEndTypes = array(
			Token::T_BRACE_CLOSE => array('{') ,
			Token::T_BRACE_SQUARE_CLOSE => array('[') ,
			Token::T_BRACE_ROUND_CLOSE => array('(') ,
			T_CLOSE_TAG => array('?>') ,
	) ;
	
	static private $arrClosureTokenPairs = array(
			array(Token::T_BRACE_OPEN,Token::T_BRACE_CLOSE) ,					// { & }
			array(Token::T_BRACE_SQUARE_OPEN,Token::T_BRACE_SQUARE_CLOSE) ,		// [ & ]
			array(Token::T_BRACE_ROUND_OPEN,Token::T_BRACE_ROUND_CLOSE) ,		// ( & )
			array(T_OPEN_TAG,T_CLOSE_TAG) ,										// < ? & ? >
			array(T_OPEN_TAG_WITH_ECHO,T_CLOSE_TAG) ,							// < ?= & ? >
			array(T_DOLLAR_OPEN_CURLY_BRACES,Token::T_BRACE_CLOSE) ,			// ${ & }
			array(T_CURLY_OPEN,Token::T_BRACE_CLOSE)  ,							// {$ & }
	) ;
	
	private $aTheOther ;
}

?>