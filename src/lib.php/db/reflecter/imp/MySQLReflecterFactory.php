<?php
namespace jc\db\reflecter;

use jc\db\reflecter\AbstractReflecterFactory;
use jc\db\reflecter\imp\MySQLColumnReflecter;
use jc\db\reflecter\imp\MySQLDBReflecter;
use jc\db\reflecter\imp\MySQLIndexReflecter;
use jc\db\reflecter\imp\MySQLTableReflecter;

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
		return new MySQLColumnReflecter ( $this, $sTable, $sColumn , $sDBName);
	}
	public function createIndexReflecter($sTable, $sIndexName, $sDBName = null)
	{
		return new MySQLIndexReflecter ( $this, $sTable, $sIndexName , $sDBName);
	}
}
?>
