<?php
namespace jc\db\reflecter;

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
<<<<<<< HEAD
		return $sDBName . '/' . $sTable . '/column_' .$sColumn;
=======
		;
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
	}
	
	static public function indexCachePath()
	{
<<<<<<< HEAD
		return $sDBName . '/' . $sTable . '/index_' .$sIndex;
=======
		;
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
	}
}
?>