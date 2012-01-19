<?php 
namespace org\jecat\framework\db ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\mvc\controller\Response;

use org\jecat\framework\system\Application;

use org\jecat\framework\db\driver\IDriver;
use org\jecat\framework\db\sql\Select;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\sql\Statement;

class DB extends Object
{
	public function __construct(IDriver $aDriver=null)
	{
		$this->aDriver = $aDriver ;
	}
	
	/**
	 * @return IDriver
	 */
	public function driver($bRequire=false)
	{
		if( $bRequire and !$this->aDriver )
		{
			throw new Exception("DB对像缺少Driver配置") ;
		}
		return $this->aDriver ;
	}
	public function setDriver(IDriver $aDriver)
	{
		$this->aDriver = $aDriver ;
	}
	
	/**
	 * @return org\jecat\framework\db\recordset\IRecordSet
	 */
	public function query($sql)
	{
		return $this->driver(true)->query($sql) ;
	}
	
	public function execute($sql)
	{
		return $this->driver(true)->execute($sql) ;
	}
	
	public function queryCount(Select $aSelect,$sColumn='*')
	{		
		$aRecords = $this->query( $aSelect->makeStatementForCount('rowCount',$sColumn,$this->driver(true)->sharedStatementState()) ) ;
		
		if( $aRecords )
		{
			return intval($aRecords->field('rowCount',0)) ;
		}
		
		else 
		{
			return 0 ;
		}
	}
	
	/**
	 * @wiki /数据库/数据库调试
	 * 
	 * 每次调用 org\jecat\framework\db\DB::query() 或 DB::execute() 函数后，执行的sql都会记录在 DB 对像中，
	 * 通过 DB::executeLog() 函数打印这些记录。
	 * 
	 * [example lang="php"]
	 * // 打印数据库执行日志
	 * DB::singleton()->executeLog() ;
	 * [/example]
	 * 
	 * 如果 executeLog() 的参数为 false ， 则返回一个包含sql执行日志的数组，并且不会打印到浏览器中。
	 * [example lang="php"]
	 * // 取得数据库执行日志，不会立刻输出到浏览器
	 * $arrSqlLog = DB::singleton()->executeLog(false) ;
	 * [/example]
	 * 
	 */
	public function executeLog($bPrint=true)
	{
		if($bPrint)
		{
			Response::singleton()->printer()->write(
				"<pre>\r\n".print_r($this->driver(true)->executeLog(),true)."\r\n</pre>"
			) ;
		}
		else
		{
			return $this->driver(true)->executeLog() ;
		}
	}
	
	public function lastInsertId()
	{
		if(!$this->aDriver)
		{
			throw new Exception("DB对像缺少Driver配置") ;
		}
		return $this->driver(true)->lastInsertId() ;
	}
	
	/**
	 * @return org\jecat\framework\db\sql\reflecter\AbstractReflecterFactory 
	 */
	public function reflecterFactory()
	{
		return $this->driver(true)->reflecterFactory($this);
	}
	
	/**
	 * @return org\jecat\framework\db\DB
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		return parent::singleton($bCreateNew,$createArgvs,$sClass?:__CLASS__) ;
	}
	
	private $aDriver ;
}

?>
