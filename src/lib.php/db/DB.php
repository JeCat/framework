<?php 
namespace jc\db ;

use jc\db\driver\IDriver;
use jc\db\sql\Select;
use jc\lang\Object;
use jc\db\sql\Statement;

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
	 * @return jc\db\recordset\IRecordSet
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
		$aRecords = $this->query(
			$aSelect->makeStatementForCount('rowCount')
		) ;
		
		if( $aRecords )
		{
			return intval($aRecords->field('rowCount',0)) ;
		}
		
		else 
		{
			return 0 ;
		}
	}
	
	public function executeLog()
	{
		return $this->driver()->executeLog() ;
	}
	
	public function lastInsertId()
	{
		return $this->driver()->lastInsertId() ;
	}
	
	/**
	 * @return jc\db\sql\reflecter\AbstractReflecterFactory 
	 */
	public function reflecterFactory()
	{
		return $this->aDriver->reflecterFactory($this);
	}
	
	private $aDriver ;
}

?>
