<?php
namespace jc\db\driver ;


use jc\db\sql\reflecter\AbstractReflecterFactory;
use jc\db\DB;

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
	
	/**
	 * 
	 * Enter description here ...
	 * @param DB $aDB
	 * @return AbstractReflecterFactory
	 */
	public function reflectFactory(DB $aDB);
}

?>