<?php 

namespace jc\db ;

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
	
	private $aDriver ;
}

?>