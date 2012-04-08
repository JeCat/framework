<?php

namespace org\jecat\framework\lang ;

use org\jecat\framework\locale\ILocale;

class TypeException extends Exception
{
	public function __construct(&$Variable,array $arrRequireTypes=array(),$sVarName=null)
	{
		$this->Variable =& $Variable ;
		$this->sVarName = $sVarName ;
		$this->arrRequireTypes =& $arrRequireTypes ;
		
		parent::__construct("变量%s类型为：%s，不满足要求的类型: %s",array(
			($this->sVarName?:'') ,
			Type::reflectType($this->Variable) ,
			implode(",", $this->arrRequireTypes) ,
		)) ;
	}

	private $Variable ;
	
	private $sVarName ;
	
	private $arrRequireTypes = array() ;
}

?>