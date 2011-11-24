<?php
namespace org\jecat\framework\db\driver ;

use org\jecat\framework\db\reflecter\MySQLReflecterFactory;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\recordset\PDORecordSet;
use org\jecat\framework\db\ExecuteException;
use org\jecat\framework\db\sql\Statement;

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