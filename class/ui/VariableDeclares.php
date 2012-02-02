<?php
namespace org\jecat\framework\ui ;

class VariableDeclares
{
	public function declareVarible($sVarOriginName,$sVarNewName)
	{
		$this->arrDeclareVariables[$sVarOriginName] = $sVarNewName ;
	}
	public function declaredVaribles()
	{
		return $this->arrDeclareVariables ;
	}
	
	private $arrDeclareVariables = array() ;
}
