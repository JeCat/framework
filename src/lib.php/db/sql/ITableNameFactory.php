<?php 

namespace jc\db\sql ;

interface ITableNameFactory
{
	public function tableName($sInputName) ;
	
	
	public function tableAlias($sTalbeName) ;
	
	public function setTableAlias($sTalbeName,$sAlias) ;
	
	public function removeAlias($sTalbeName) ;
	
	public function clearAliases() ;
	
	
}

?>