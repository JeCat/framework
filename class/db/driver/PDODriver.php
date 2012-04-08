<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/

namespace org\jecat\framework\db\driver ;

use org\jecat\framework\db\sql\StatementState;
use org\jecat\framework\db\reflecter\imp\MySQLReflecterFactory;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\ExecuteException;
use org\jecat\framework\db\sql\Statement;

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
	 * @return org\jecat\framework\db\sql\StatementState
	 */
	public function sharedStatementState()
	{
		if(!$this->aSharedStatementState)
		{
			$this->aSharedStatementState = new StatementState() ;
		}
		return $this->aSharedStatementState ;
	}

	/**
	 * @return \PDOStatement
	 */
	public function query($sql)
	{
		$arrLog['sql'] = ($sql instanceof Statement)?
					$sql->makeStatement($this->sharedStatementState()): strval($sql) ;

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
		
		return $result ;
	}
	
	public function execute($sql)
	{		
		$arrLog['sql'] = ($sql instanceof Statement)?
					$sql->makeStatement($this->sharedStatementState()): strval($sql) ;

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
	
	public function reflecterFactory(DB $aDB)
	{
		//识别数据库的名称(mysql,oracle)
		$sDBType = substr($this->sDsn, 0 ,stripos($this->sDsn, ':') );
		//如果是mysql,创建mysql的工厂
		if($sDBType == 'mysql')
		{
			return new MySQLReflecterFactory($aDB);
		}
		
		return null;
	}
	
	private $arrExecuteLog = array() ;
	
	private $sCurrentDBName ; 
	
	private $sDsn ; 
	
	private $aSharedStatementState ;
}
