<?php 
namespace org\jecat\framework\db ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\system\Response;

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
