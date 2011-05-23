<?php

namespace jc\db ;

interface IDriver
{
	public function error() ;
	
	public function errorMessage() ;
	
	public function errorSQLState() ;
	
	public function lastInsertId($sName=null) ;
	
	public function query($SQLStatement) ;
	
	public function connect($sUrl,$sUsername=null,$sPassword=null,array $arrDriverOptions=array()) ;
	
	public function disconnect() ;
	
	
	
	// Transaction 
	public function beginTransaction() ;
	
	public function commit() ;
	
	public function rollBack() ;
	
}

?>