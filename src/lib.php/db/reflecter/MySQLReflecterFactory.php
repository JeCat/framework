<?php
namespace jc\db\reflecter;

class MySQLReflecterFactory extends AbstractReflecterFactory
{
	public function createDBReflecter($sDBName)
	{
		return new MySQLDBReflecter ( $this, $sDBName );
	}
	
	public function createTableReflecter($sTable, $sDBName = null)
	{
		return new MySQLTableReflecter ( $this, $sTable, $sDBName );
	}
	
	public function createColumnReflecter($sTable, $sColumn, $sDBName = null)
	{
		return new MySQLColumnReflecter ( $this, $sTable, $sColumn );
	}
	
	public function createIndexReflecter($sTable, $sIndexName, $sDBName = null)
	{
		return new MySQLIndexReflecter ( $this, $sTable, $sIndexName );
	}
}
?>