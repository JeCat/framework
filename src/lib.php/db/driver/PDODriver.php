<?php

namespace jc\db\driver ;

use jc\db\reflecter\MySQLReflecterFactory;
use jc\db\DB;
use jc\db\recordset\PDORecordSet;
use jc\db\ExecuteException;
use jc\db\sql\Statement;

class PDODriver extends \PDO implements IDriver
{
	public function __construct ($sDsn, $sUsername, $sPasswd, $sOptions=null)
	{
		parent::__construct($sDsn, $sUsername, $sPasswd, $sOptions) ;
	
		$this->sDsn = $sDsn ;
		if( preg_match('/dbname=(.+?)(;|$)/i',$sDsn,$arrRes) )
		{
			$this->sCurrentDBName = $arrRes[1] ;
		}
	}

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
	
	public function selectDB($sName)
	{
		$this->execute("USE {$sName} ;") ;
		
		$this->sCurrentDBName = $sName ;
	}
	
	public function currentDBName()
	{
		return $this->sCurrentDBName ;
	}
	
	public function reflectFactory(DB $aDB)
	{
		//识别数据库的名称(mysql,oracle)
		$sDBType = substr($this->sDsn, 0 ,stripos($this->sDsn, ':')-1 );
		
		//如果是mysql,创建mysql的工厂
		if($sDBType == 'Mysql')
		{
			return new MySQLReflecterFactory($aDB);
		}
		
		return null;
	}
	
	private $arrExecuteLog = array() ;
	
	private $sCurrentDBName ; 
	
	private $sDsn ; 
}

?>