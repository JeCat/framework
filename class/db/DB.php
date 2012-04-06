<?php 
namespace org\jecat\framework\db ;

use org\jecat\framework\db\sql\parser\BaseParserFactory;

use org\jecat\framework\db\sql\compiler\SqlCompiler;

use org\jecat\framework\db\sql\SQL;

use org\jecat\framework\db\sql\StatementState;
use org\jecat\framework\db\reflecter\imp\MySQLReflecterFactory;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\system\Application;
use org\jecat\framework\db\driver\IDriver;
use org\jecat\framework\db\sql\Select;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\sql\Statement;

class DB extends Object
{
	public function __construct($sDsn, $sUsername, $sPasswd, array $arrOptions=null)
	{
		$this->sPDODsn =& $sDsn ;
		$this->sPDOUsername =& $sUsername ;
		$this->sPDOPassword =& $sPasswd ;
		$this->arrPDOOptions =& $arrOptions ;
	}
	
	public function connect($sDsn=null, $sUsername=null, $sPasswd=null, array $arrOptions=null,$bReconnect=false)
	{
		if( !$this->aPDO or $bReconnect )
		{
			$sDsn = $sDsn?:$this->sPDODsn ;
			
			$this->aPDO = new \PDO(
					$sDsn
					, $sUsername?:$this->sPDOUsername
					, $sPasswd?:$this->sPDOPassword
					, $arrOptions?:$this->arrPDOOptions
			) ;
			
			if( preg_match('/dbname=(.+?)(;|$)/i',$sDsn,$arrRes) )
			{
				$this->sCurrentDBName = $arrRes[1] ;
			}
		}
		return $this->aPDO ;
	}
	
	public function disconnect()
	{
		$this->aPDO = null ;
	}
	
	public function hasConnected()
	{
		return $this->aPDO? true: false ;
	}
	
	/**
	 * @return \PDO
	 */
	public function pdo($bAutoConnect=true)
	{
		if( !$this->aPDO and $bAutoConnect )
		{
			$this->connect() ;
		}
		return $this->aPDO ;
	}
	
	public function error()
	{
		$arrErr = $this->pdo()->errorInfo() ;
		return isset($arrErr[1])? $arrErr[1]: 0 ;
	}
	
	public function errorMessage()
	{
		$arrErr = $this->pdo()->errorInfo() ;
		return isset($arrErr[2])? $arrErr[2]: '' ;
	}
	
	public function errorSQLState()
	{
		$arrErr = $this->pdo()->errorInfo() ;
		return isset($arrErr[0])? $arrErr[0]: 0 ;
	}
	
	public function lastInsertId($sName=null)
	{
		return $this->pdo()->lastInsertId($sName) ;
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
	public function query($sql,$factors=null,SqlCompiler $aSqlCompiler=null)
	{
		$arrLog['sql'] = $this->makeSql($sql,$factors,$aSqlCompiler) ;
	
		$fBefore = microtime(true) ;
		$result = $this->pdo()->query($arrLog['sql'],\PDO::FETCH_ASSOC) ;
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
	
	private function makeSql($inputSql,$factors=null,SqlCompiler $aSqlCompiler=null)
	{
		if( is_string($inputSql) )
		{
			$aSql = SQL::make($inputSql,$factors) ;
		}
		
		else if( $inputSql instanceof SQL )
		{
			$aSql = $inputSql ;
			$aSql->addFactors($factors) ;
		}
		
		else
		{
			throw new ExecuteException($this,null,0,"DB::query() 输入的参数 \$sql 类型无效。") ;
		}
		
		if($aSql)
		{
			return $aSql->toString($aSqlCompiler) ;
		}
		else
		{
			return $inputSql ;
		}		
	}
	
	public function queryCount(Select $aSelect,$sColumn='*',SqlCompiler $aSqlCompiler=null)
	{
		$arrRawSelect =& $aSelect->rawClause(SQL::CLAUSE_SELECT) ;
		$arrReturnsBak =& $arrRawSelect['subtree'] ;
		
		$arrTmp = array("count({$sColumn}) as rowCount") ;
		$arrRawSelect['subtree'] =& $arrTmp ;
		try{
			$aRecords = $this->query($aSelect,null,$aSqlCompiler) ;
		}catch(\Exception $e){}
		//} final {
			$arrRawSelect['subtree'] =& $arrReturnsBak ;
		//}
		if(isset($e))
		{
			throw $e ;
		}
		
		if( $aRecords )
		{
			$arrRow = $aRecords->fetch(\PDO::FETCH_ASSOC) ;
			return intval($arrRow['rowCount']) ;
		}
		
		else 
		{
			return 0 ;
		}
	}
	
	public function execute($inputSql,$factors=null,SqlCompiler $aSqlCompiler=null)
	{
		$arrLog['sql'] = $this->makeSql($inputSql,$factors,$aSqlCompiler) ;
	
		$fBefore = microtime(true) ;
		$ret = $this->pdo()->exec($arrLog['sql']) ;
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
	
	public function selectDB($sName)
	{
		$this->execute("USE {$sName} ;") ;
	
		$this->sCurrentDBName = $sName ;
	}
	
	public function currentDBName()
	{
		return $this->sCurrentDBName ;
	}
	
	/**
	 * @return org\jecat\framework\db\sql\reflecter\AbstractReflecterFactory
	 */
	public function reflecterFactory()
	{
		// 自动链接到数据库
		if( !$this->hasConnected() )
		{
			try{
				$this->connect() ;
			} catch (\Exception $e) {
				return null ;
			}
		}
		
		//识别数据库的名称(mysql,oracle)
		$sDBType = substr($this->sPDODsn, 0 ,stripos($this->sPDODsn, ':') );
		//如果是mysql,创建mysql的工厂
		if($sDBType == 'mysql')
		{
			return new MySQLReflecterFactory($this);
		}
	
		return null;
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
				"<pre>\r\n".print_r($this->arrExecuteLog,true)."\r\n</pre>"
			) ;
		}
		else
		{
			return $this->arrExecuteLog ;
		}
	}
	
	/**
	 * @return org\jecat\framework\db\DB
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		return parent::singleton($bCreateNew,$createArgvs,$sClass?:__CLASS__) ;
	}
	
	private $aPDO ;
	private $sPDODsn ;
	private $sPDOUsername ;
	private $sPDOPassword ;
	private $arrPDOOptions ;
	
	private $sCurrentDBName ;
	private $arrExecuteLog ;
	private $aSharedStatementState ;
	
}

?>
