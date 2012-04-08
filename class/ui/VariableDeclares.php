<?php
namespace org\jecat\framework\ui ;

use org\jecat\framework\io\IOutputStream;

class VariableDeclares
{
	public function declareVarible($sVarName,$sInitExpression=null)
	{
		$this->arrDeclareVariables[$sVarName] = $sInitExpression ;
	}
	
	public function hasDeclared($sVarName)
	{
		return array_key_exists($sVarName,$this->arrDeclareVariables) ;
	}
	
	public function make(IOutputStream $aDev)
	{
		foreach($this->arrDeclareVariables as $sVarName=>&$sInitExpression)
		{
			if($sInitExpression!==null)
			{
				$aDev->write("\${$sVarName} = {$sInitExpression} ;") ;
			}
			else
			{
				$aDev->write("\${$sVarName} ;") ;
			}
		}
	}
	
	public function variableNameIterator()
	{print_r(array_keys($this->arrDeclareVariables)) ;
		return $this->arrDeclareVariables?
					new \ArrayIterator(array_keys($this->arrDeclareVariables)) :
					new \EmptyIterator() ;
	}
	
	private $arrDeclareVariables = array() ;
}
