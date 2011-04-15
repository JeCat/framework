<?php

namespace jc\lang ;

class TypeException extends Exception
{
	public function __construct($Variable,array $arrRequireTypes=array(),$sVarName=null)
	{
		$this->Variable =& $Variable ;
		$this->sVarName = $sVarName ;
		$this->arrRequireTypes =& $arrRequireTypes ;
		
		Exception::__construct() ;
	}
	
	public function message($sLanguage)
	{
		return array(
			"变量%s类型为：%s，不满足要求的类型: %s" ,
			($this->sVarName?:'') ,
			Type::reflectType($this->Variable) ,
			implode(",", $this->arrRequireTypes) ,
		) ;
	}

	private $Variable ;
	
	private $sVarName ;
	
	private $arrRequireTypes = array() ;
}

?>