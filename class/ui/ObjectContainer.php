<?php
namespace org\jecat\framework\ui ;


class ObjectContainer extends Object
{
	public function __construct($sTemplateName=null,$sNamespace='*')
	{
		$this->sTemplateName = $sTemplateName ;
		$this->sNamespace= $sNamespace ;
	}

	public function templateName() 
	{
		return $this->sTemplateName ;
	}
	
	public function ns() 
	{
		return $this->sNamespace ;
	}
	
	private $sTemplateName ;
	
	private $sNamespace = '*' ;
}

?>