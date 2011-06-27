<?php 

namespace jc\db ;

use jc\db\sql\Select;

use jc\lang\Object;
use jc\db\sql\Statement;

class DB extends Object
{
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
			return intval($aRecords->field(0,'rowCount')) ;
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
	
	private $aDriver ;
}

?>