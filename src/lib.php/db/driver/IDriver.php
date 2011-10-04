<?php

namespace jc\db\driver ;

interface IDriver
{
	public function error() ;
	
	public function errorMessage() ;
	
	public function errorSQLState() ;
	
	public function lastInsertId($sName=null) ;
	
	public function query($statement) ;
	
	
	
	// Transaction 
	public function beginTransaction() ;
	
	public function commit() ;
	
	public function rollBack() ;
	
	public function selectDB($sName) ;
	
	public function currentDBName() ;
	
}

?>