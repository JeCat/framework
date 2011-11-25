<?php
namespace org\jecat\framework\bean ;

interface IBean
{
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,BeanFactory $aBeanFactory=null) ;
	
	public function buildBean(array & $arrConfig,$sNamespace='*',BeanFactory $aBeanFactory=null) ;
	
	public function beanConfig() ;
}

?>