<?php
namespace jc\compile\object ;

class ClosureToken extends Token 
{
	static public $arrClosureObjectBeginTypes = array(
			Token::T_BRACE_OPEN ,
			Token::T_BRACE_SQUARE_OPEN ,
			Token::T_BRACE_ROUND_OPEN ,
			T_OPEN_TAG ,
			T_OPEN_TAG_WITH_ECHO ,
			T_DOLLAR_OPEN_CURLY_BRACES ,		// "ooo{$xxx}ooo"
			T_CURLY_OPEN ,						// "ooo${xxx}ooo"
	) ;	
	
	static public $arrClosureObjectEndTypes = array(
			Token::T_BRACE_CLOSE ,
			Token::T_BRACE_SQUARE_CLOSE ,
			Token::T_BRACE_ROUND_CLOSE ,
			T_CLOSE_TAG ,
	) ;
	
	public function __construct(Token $aToken)
	{
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
	public function setTheOther(self $aToken)
	{
		$this->aTheOther = $aToken ;
	}
	
	private $aTheOther ;
}

?>