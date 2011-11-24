<?php
namespace jc\db\sql ;

interface IDataSettableStatement 
{
	
	public function data($sColumnName) ;
	
	public function setData($sColumnName,$sData=null) ;
	
	public function removeData($sColumnName) ;
	
	public function clearData() ;
	
	public function dataIterator() ;
	
	public function dataNameIterator() ;
	
}

?>