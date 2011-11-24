<?php
namespace jc\db\driver ;

use jc\db\reflecter\MySQLReflecterFactory;
use jc\db\DB;
use jc\db\recordset\PDORecordSet;
use jc\db\ExecuteException;
use jc\db\sql\Statement;

class MockupDriver implements IDriver
{
	public function error(){}
	
	public function errorMessage(){}
	
	public function errorSQLState(){}
	
	public function lastInsertId($sName=null){}
	
	public function query($statement){}

	// Transaction 
	public function beginTransaction(){}
	
	public function commit(){}
	
	public function rollBack(){}
	
	
	
	public function selectDB($sName)
	{
		$this->sCurrentDBName = $sName ;
	}
	
	public function currentDBName()
	{
		return $this->sCurrentDBName ;
	}
	
	/**
	 * 获得数据库反射对象工厂
	 * @param DB $aDB
	 * @return AbstractReflecterFactory or null when no database support
	 */
	public function reflecterFactory(DB $aDB)
	{
		return $this->aReflecterFactory ;
	}
	
	public $sCurrentDBName ;
	public $aReflecterFactory ;
}


?>