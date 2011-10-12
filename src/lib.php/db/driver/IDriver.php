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
	 * 获得数据库反射对象工厂
	 * @param DB $aDB
	 * @return AbstractReflecterFactory or null when no database support
	 */
	public function reflecterFactory(DB $aDB);
}

?>
