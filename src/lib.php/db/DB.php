<?php 

namespace jc\db ;

use jc\db\sql\Statement;

class DB
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
	
	public function query($sql)
	{
		$sSql = ($sql instanceof Statement)?
					$sql->makeStatement(): strval($sql) ;
	}
	
	public function execute($sql)
	{
		$sSql = ($sql instanceof Statement)?
					$sql->makeStatement(): strval($sql) ;
		
	}
	
	private $aDriver ;
}

?>