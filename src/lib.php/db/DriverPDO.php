<?php

namespace jc\db ;

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
	
	public function query($SQLStatement)
	{
		
	}
	
	public function connect($sUrl,$sUsername=null,$sPassword=null,array $arrDriverOptions=array())
	{
		
	}
	
	public function disconnect()
	{
		
	}
}

?>