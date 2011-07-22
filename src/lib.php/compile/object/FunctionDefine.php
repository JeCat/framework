<?php
namespace jc\compile\object ;

class FunctionDefine extends Token
{
	public function __construct(
			Token $aToken
			, $aTokenName=null
			, Token $aTokenArgList=null
			, Token $aTokenBody=null
	)
	{
		$this->cloneOf($aToken) ;
		
		$this->aTokenName = $aTokenName ;
		$this->aTokenArgList = $aTokenArgList ;
		$this->aTokenBody = $aTokenBody ;
		
		$this->setBelongsFunction($this) ;
	}

	public function name()
	{
		return $this->aTokenName->sourceCode() ;
	}
	
	public function nameToken()
	{
		return $this->aTokenName ;
	}
	public function argListToken()
	{
		return $this->aTokenArgList ;
	}
	public function bodyToken()
	{
		return $this->aTokenBody ;
	}

	public function setNameToken(Token $aTokenName)
	{
		$this->aTokenName = $aTokenName ;
	}
	public function setArgListToken(ClosureToken $aTokenArgList)
	{
		$this->aTokenArgList = $aTokenArgList ;
	}
	public function setBodyToken(ClosureToken $aTokenBody)
	{
		$this->aTokenBody = $aTokenBody ;
	}
	
	public function setClassDefine(ClassDefine $aToken)
	{
		$this->aClass = $aToken ;
	}
	
	public function classDefine()
	{
		return $this->aClass ;
	}
	
	private $aTokenName ;
	private $aTokenArgList ;
	private $aTokenBody ;
	private $aClass ;
}

?>