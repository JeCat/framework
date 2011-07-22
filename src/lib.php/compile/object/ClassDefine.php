<?php
namespace jc\compile\object ;

class ClassDefine extends Token
{
	public function __construct(
			Token $aToken
			, $aTokenName=null
			, Token $aTokenBody=null
	)
	{
		$this->cloneOf($aToken) ;
		
		$this->aTokenName = $aTokenName ;
		$this->aTokenBody = $aTokenBody ;
		
		$this->setBelongsClass($this) ;
	}

	public function name()
	{
		return $this->aTokenName->sourceCode() ;
	}
	
	public function nameToken()
	{
		return $this->aTokenName ;
	}
	public function setNameToken(Token $aTokenName)
	{
		$this->aTokenName = $aTokenName ;
	}
	public function bodyToken()
	{
		return $this->aTokenBody ;
	}
	public function setBodyToken(ClosureToken $aTokenBody)
	{
		$this->aTokenBody = $aTokenBody ;
	}
	
	private $aTokenName ;
	private $aTokenBody ;
}

?>