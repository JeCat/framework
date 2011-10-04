<?php
namespace jc\db\sql\reflecter;

use jc\lang\Object;

class AbstractReflecterFactory extends Object
{
	function __construct($aDB)
	{
	
	}
	
	function createDBReflecter($sDBName)
	{
	
	}
	
	function createTableReflecter($sTable, $sDBName = null)
	{
	
	}
	
	function createColumnReflecter($sTable, $sColumn, $sDBName = null)
	{
	
	}
	
	function createIndexReflecter($sTable, $sIndexName, $sDBName = null)
	{
	
	}
	
	public function cache()
	{
	
	}
	
	public function setCache()
	{
	
	}
	
	public function db()
	{
	
	}
	
	static public function dbCachePath()
	{
		;
	}
	
	static public function tableCachePath()
	{
		;
	}
	
	static public function columnCachePath()
	{
		;
	}
	
	static public function indexCachePath()
	{
		;
	}
}
?>