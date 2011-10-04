<?php
namespace jc\db\sql\reflecter;

class MySQLReflecterFactory extends AbstractReflecterFactory
{
	public function createDBReflecter($sDBName)
	{
		return new MySQLDBReflecter ( $this->db (), $sDBName );
	}
	
	public function createTableReflecter($sTable, $sDBName = null)
	{
		return new MySQLTableReflecter ( $this->db (), $sTable, $sDBName );
	}
	
	public function createColumnReflecter($sTable, $sColumn, $sDBName = null)
	{
		return new MySQLColumnReflecter ( $this->db (), $sTable, $sColumn );
	}
	
	public function createIndexReflecter($sTable, $sIndexName, $sDBName = null)
	{
		return new MySQLIndexReflecter ( $this->db (), $sTable, $sIndexName );
	}
}
?>