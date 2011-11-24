<?php
namespace org\jecat\framework\bean ;

interface IBean
{
	
	/**
	 * Bean Class 的构造函数必须是一个不要求参数的公共方法
	 */
	public function __construct() ;
	
	public function build(array & $arrConfig,$sNamespace='*') ;
	
	public function beanConfig() ;
}

?>