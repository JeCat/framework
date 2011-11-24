<?php
namespace org\jecat\framework\lang\compile\object ;

class CallFunction extends Token
{
	
	public function __construct(Token $aFunctionName,Token $aArgv,Token $aClass=null,Token $aAccessSymbol=null)
	{
		$this->cloneOf($aFunctionName) ;

		$this->setArgvToken($aArgv) ;
		$this->setClassToken($aClass) ;
		$this->setAccessToken($aAccessSymbol) ;
	}
	
	public function setArgvToken(Token $aArgv)
	{
		$this->aArgv = $aArgv ;
	}
	public function argvToken()
	{
		return $this->aArgv;
	}
	
	public function setClassToken($aClass)
	{
		$this->aClass = $aClass;
	}
	public function classToken()
	{
		return $this->aClass;
	}
	
	public function setAccessToken($aAccessSymbol)
	{
		$this->aAccessSymbol = $aAccessSymbol;
	}
	public function accessToken()
	{
		return $this->aAccessSymbol;
	}
	
	private $aArgv ;
	private $aClass ;
	private $aAccessSymbol ;
}

?>