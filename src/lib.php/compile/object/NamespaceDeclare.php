<?php
namespace jc\compile\object ;

class NamespaceDeclare extends Token
{
	public function __construct(Token $aToken)
	{
		$this->cloneOf($aToken) ;
		$this->setBelongsNamespace($this) ;
	}
	
	public function addNameToken(Token $aToken)
	{
		$this->arrNameAndSlashes[] = $aToken ;
	}
	
	public function name()
	{
		return implode("\\",$this->arrNameAndSlashes) ;
	}
	
	private $arrNameAndSlashes ;
}

?>