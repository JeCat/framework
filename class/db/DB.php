<?php 
namespace org\jecat\framework\db ;

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
	public function driver()
	{
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
		return $this->driver()->query($sql) ;
	}
	
	public function execute($sql)
	{
		return $this->driver()->execute($sql) ;
	}
	
	public function queryCount(Select $aSelect)
	{		
		$aRecords = $this->query( $aSelect->makeStatementForCount('rowCount') ) ;
		
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
				"<pre>\r\n".print_r($this->driver()->executeLog(),true)."\r\n</pre>"
			) ;
		}
		else
		{
			return $this->driver()->executeLog() ;
		}
	}
	
	public function lastInsertId()
	{
		return $this->driver()->lastInsertId() ;
	}
	
	/**
	 * @return org\jecat\framework\db\sql\reflecter\AbstractReflecterFactory 
	 */
	public function reflecterFactory()
	{
		return $this->aDriver->reflecterFactory($this);
	}
	
	private $aDriver ;
}

?>
