<?php
namespace jc\lang\compile\object ;

class CallFunction extends Token
{
	
	public function __construct(Token $aFunctionName,Token $aArgv,Token $aClass=null,Token $aAccessSymbol=null)
	{
		$aFunctionName->cloneOf($this) ;

		$this->setArgvToken($aArgv) ;
		$this->setClassToken($aClass) ;
		$this->setAccessToken($aAccessSymbol) ;
	}
	
	
	
	private $aArgv ;
	private $aClass ;
	private $aAccessSymbol ;
}

?>