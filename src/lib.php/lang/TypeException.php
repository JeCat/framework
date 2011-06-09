<?php

namespace jc\lang ;

use jc\locale\ILocale;

class TypeException extends Exception
{
	public function __construct(&$Variable,array $arrRequireTypes=array(),$sVarName=null)
	{
		$this->Variable =& $Variable ;
		$this->sVarName = $sVarName ;
		$this->arrRequireTypes =& $arrRequireTypes ;
	}
	
	public function message(ILocale $aLocale=null)
	{
		if( !$aLocale )
		{
			$aLocale = $this->application(true)->localeManager()->locale() ;
		}
		
		return $aLocale->trans("变量%s类型为：%s，不满足要求的类型: %s",array(
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