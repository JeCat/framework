<?php

namespace jc\db ;

use jc\db\sql\Statement;

class PDODriver extends \PDO implements IDriver
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
		$arrLog['sql'] = ($sql instanceof Statement)?
					$sql->makeStatement(): strval($sql) ;

		$fBefore = microtime(true) ;
		$result = \PDO::query($arrLog['sql'],\PDO::FETCH_ASSOC) ;
		$arrLog['time'] = microtime(true) - $fBefore ;
					
		$this->arrExecuteLog[] = $arrLog ;
		
		if( !$result )
		{
			throw new ExecuteException(
					$this
					, $arrLog['sql']
					, $this->error()
					, $this->errorMessage()
			) ;
		}
		
		return new PDORecordSet($result) ;
	}
	
	public function execute($sql)
	{		
		$arrLog['sql'] = ($sql instanceof Statement)?
					$sql->makeStatement(): strval($sql) ;

		$fBefore = microtime(true) ;
		$ret = \PDO::exec($arrLog['sql']) ;
		$arrLog['time'] = microtime(true) - $fBefore ;
		
		if( $ret===false )
		{
			$this->arrExecuteLog[] = $arrLog ;
			
			throw new ExecuteException(
					$this
					, $arrLog['sql']
					, $this->error()
					, $this->errorMessage()
			) ;
		}
		
		$arrLog['affected'] = $ret ;
		$this->arrExecuteLog[] = $arrLog ;
		
		return $arrLog['affected'] ;
	}
	
	public function executeLog()
	{
		return $this->arrExecuteLog ;
	}
	
	private $arrExecuteLog = array() ;
}

?>