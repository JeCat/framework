<?php
namespace org\jecat\framework\bean ;

interface IBean
{
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce) ;
	
	public function buildBean(array & $arrConfig,$sNamespace='*') ;
	
	public function beanConfig() ;
}

?>