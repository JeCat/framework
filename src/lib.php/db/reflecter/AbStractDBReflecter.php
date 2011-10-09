<?php
namespace jc\db\reflecter;




class AbStractDBReflecter extends DBStructReflecter
{
<<<<<<< HEAD
	abstract function __construct($aDBReflecterFactory , $sDBName);
=======
	function __construct($sDB , $sDBName)
	{
	}
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
	
	/**
	 * 
	 * Enter description here ...
	 * @return \iterator
	 */
<<<<<<< HEAD
	abstract public function tableNameIterator();
	
	/**
	 * 库是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	abstract public function isExist();
=======
	public function tableNameIterator()
	{
		
	}
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
}