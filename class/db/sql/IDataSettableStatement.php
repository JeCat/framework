<?php
namespace org\jecat\framework\db\sql ;

interface IDataSettableStatement 
{
	public function data($sColumnName) ;
	
	public function setData($sColumnName,$sData=null,$bValueExpr=false) ;
	
	public function removeData($sColumnName) ;
	
	public function clearData() ;
}

?>