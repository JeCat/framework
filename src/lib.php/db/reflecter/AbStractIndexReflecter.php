<?php
namespace jc\db\reflecter;




class AbStractIndexReflecter extends DBStructReflecter
{
<<<<<<< HEAD
	abstract function __construct($aDBReflecterFactory, $sTable, $sIndexName, $sDBName = null);
=======
	function __construct($sDB ,$sTable,$sIndexName, $sDBName = null)
	{
	}
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
	
	public function isPrimary()
	{
		
	}
	
	public function isUnique() {
		;
	}
	
	public function isFullText() {
		;
	}
	
	/**
	 * 索引是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	abstract public function isExist();
	
	/**
	 * 
	 * Enter description here ...
	 * @return array
	 */
	public function columnNames() {
		;
	}
}