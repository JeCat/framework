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

	public function setAccessToken($aAccessToken)
	{
		$this->aAccessToken = $aAccessToken ;
	}
	public function accessToken()
	{
		return $this->aAccessToken ;		
	}
	public function setStaticToken($aStaticToken)
	{
		$this->aStaticToken = $aStaticToken ;
	}
	public function staticToken()
	{
		return $this->aStaticToken ;		
	}
	public function setAbstractToken($aAbstractToken)
	{
		$this->aAbstractToken = $aAbstractToken ;
	}
	public function abstractToken()
	{
		return $this->aAbstractToken ;		
	}
	public function setDocToken($aDocToken)
	{
		$this->aDocToken = $aDocToken ;
	}
	public function docToken()
	{
		return $this->aDocToken ;		
	}
	
	private $aTokenName ;
	private $aTokenArgList ;
	private $aTokenBody ;
	private $aClass ;
	
	private $aAccessToken ;
	private $aStaticToken ;
	private $aAbstractToken ;
	private $aDocToken ;
}

?>