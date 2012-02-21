<?php
namespace org\jecat\framework\ui ;


class ObjectContainer extends Object
{
	public function __construct($sTemplateName=null,$sNamespace='*')
	{
		$this->sTemplateName = $sTemplateName ;
		$this->sNamespace= $sNamespace ;
	}
	
	public function clear()
	{
		$this->arrDeclareVariables = array() ;
		
		return parent::clear() ;
	}

	public function templateName() 
	{
		return $this->sTemplateName ;
	}
	
	public function ns() 
	{
		return $this->sNamespace ;
	}
	
	/**
	 * @return VariableDeclares
	 */
	public function variableDeclares()
	{
		if(!$this->aDeclareVariables)
		{
			$this->aDeclareVariables = new VariableDeclares() ;
		}
		return $this->aDeclareVariables ;
	}
	public function setVariableDeclares(VariableDeclares $aDeclareVariables)
	{
		$this->aDeclareVariables = $aDeclareVariables ;
	}
	
	private $aDeclareVariables ;
	
	// private $aDeclareNamespace ;
	
	// private $aDeclareNameUses ;
	
	private $sTemplateName ;
	
	private $sNamespace = '*' ;
}

