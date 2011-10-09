<?php
namespace jc\db\reflecter;


class AbStractTableReflecter extends DBStructReflecter
{
<<<<<<< HEAD
	abstract function __construct($aDBReflecterFactory ,$sTable, $sDBName=null);
	
	/**
	 * @return string 如果主键不存在返回null
	 */
	abstract public function primaryName();
=======
	function __construct($sDB ,$sTable, $sDBName=null)
	{
		
	}
	
	public function primaryName()
	{
		
	}
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
	
	public function autoIncrement() {
		;
	}
	
	public function comment() {
		;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return \Iterator
	 */
<<<<<<< HEAD
	abstract public function columnNameIterator();
	
	/**
	 * 表是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	abstract public function isExist();
=======
	public function columnNameIterator() {
		;
	}
	
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
}