<?php
namespace jc\compile\object ;

use jc\lang\Exception;

use jc\compile\ClassCompileException;

class ClosureToken extends Token 
{
	public function __construct(Token $aToken)
	{
		if( !in_array($aToken->tokenType(),self::$arrClosureObjectBeginTypes,true) and !in_array($aToken->tokenType(),self::$arrClosureObjectEndTypes,true) )
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
		return in_array($this->tokenType(),self::$arrClosureObjectBeginTypes) ;
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
				if( !isset(self::$arrClosureTokenPairs[ $thisTokenType ]) )
				{
					throw new Exception("类型无效:%s，无法检查对应的闭合类型",$thisTokenType) ;
				}
				
				if( $aToken->tokenType()!==self::$arrClosureTokenPairs[ $thisTokenType ] )
				{
					throw new Exception("遇到意外的闭合token类型，“%s”和“%s”类型不匹配。",array($thisTokenType,$aToken->tokenType())) ;
				}
			}
			
			else 
			{
				//if( !isset(self::$arrClosureTokenPairs[ $thisTokenType ]) )
				if( !$openTokenType=array_search($thisTokenType,self::$arrClosureTokenPairs) )
				{
					throw new Exception("类型无效:%s，无法检查对应的闭合类型",$thisTokenType) ;
				}
				
				if( $aToken->tokenType()!==$openTokenType )
				{
					throw new Exception("遇到意外的闭合token类型，“%s”和“%s”类型不匹配。",array($thisTokenType,$aToken->tokenType())) ;
				}
			}
			
			$this->aTheOther = $aToken ;
			$aToken->aTheOther = $this ;
		}
	}

	static public function openClosureSymbols()
	{
		return array_keys(self::$arrClosureObjectBeginTypes) ;
	}
	static public function closeClosureSymbols()
	{
		return array_keys(self::$arrClosureObjectEndTypes) ;
	}
	
	static public function openClosureTokens()
	{
		return array_unique(self::$arrClosureObjectBeginTypes) ;
	}
	static public function closeClosureTokens()
	{
		return array_unique(self::$arrClosureObjectEndTypes) ;
	}

	static public function closureTokenPairs()
	{
		return self::$arrClosureTokenPairs ;
	}
	
	static private $arrClosureObjectBeginTypes = array(
			'{' => Token::T_BRACE_OPEN ,
			'[' => Token::T_BRACE_SQUARE_OPEN ,
			'(' => Token::T_BRACE_ROUND_OPEN ,
			'<?' => T_OPEN_TAG ,
			'<?php' => T_OPEN_TAG ,
			'<?=' => T_OPEN_TAG_WITH_ECHO ,
			'{$' => T_DOLLAR_OPEN_CURLY_BRACES ,		// "ooo{$xxx}ooo"
			'${' => T_CURLY_OPEN ,						// "ooo${xxx}ooo"
	) ;	
	
	static private $arrClosureObjectEndTypes = array(
			'}' => Token::T_BRACE_CLOSE ,
			']' => Token::T_BRACE_SQUARE_CLOSE ,
			')' => Token::T_BRACE_ROUND_CLOSE ,
			'?>' => T_CLOSE_TAG ,
	) ;
	
	static private $arrClosureTokenPairs = array(
			Token::T_BRACE_OPEN => Token::T_BRACE_CLOSE ,					// { & }
			Token::T_BRACE_SQUARE_OPEN => Token::T_BRACE_SQUARE_CLOSE ,		// [ & ]
			Token::T_BRACE_ROUND_OPEN => Token::T_BRACE_ROUND_CLOSE ,		// 
			T_OPEN_TAG => T_CLOSE_TAG ,						
			T_OPEN_TAG_WITH_ECHO => T_CLOSE_TAG ,				
			T_DOLLAR_OPEN_CURLY_BRACES => Token::T_BRACE_CLOSE ,		// {$ & }
			T_CURLY_OPEN => Token::T_BRACE_CLOSE  ,			// ${ & }
	) ;
	
	private $aTheOther ;
}

?>