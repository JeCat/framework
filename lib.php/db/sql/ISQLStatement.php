<?php 

namespace jc\db\sql ;

interface ISQLStatement
{
	public function makeStatement($bFormat=false) ;
	
	public function checkValid($bThrowException=true) ;
	
	public function tableNameFactory() ;
	
	public function setTableNameFactory(ITableNameFactory $aFactory) ;
	
}


?>