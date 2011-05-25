<?php

namespace jc\db ;

use jc\db\sql\Statement;

class DriverPDO extends \PDO implements IDriver
{
	public function error()
	{
		$arrErr = \PDO::errorInfo() ;
		return isset($arrErr[1])? $arrErr[1]: 0 ;		
	}
	
	public function errorMessage()
	{
		$arrErr = \PDO::errorInfo() ;
		return isset($arrErr[2])? $arrErr[2]: '' ;		
	}
	
	public function errorSQLState()
	{
		$arrErr = \PDO::errorInfo() ;
		return isset($arrErr[0])? $arrErr[0]: 0 ;		
	}
	
	public function lastInsertId($sName=null)
	{
		return \PDO::lastInsertId($sName) ;
	}

	/**
	 * @return jc\db\recordset\IRecordSet
	 */
	public function query($sql)
	{
		$sSql = ($sql instanceof Statement)?
					$sql->makeStatement(): strval($sql) ;

		return new PDORecordSet( \PDO::query($sSql) ) ;
	}
	/**
	 * @return jc\db\recordset\IRecordSet
	 */
	public function execute($sql)
	{
		$sSql = ($sql instanceof Statement)?
					$sql->makeStatement(): strval($sql) ;

		return new PDORecordSet( \PDO::exec($sSql) ) ;
	}
}

?>