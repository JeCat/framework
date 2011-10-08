<?php
namespace jc\db\reflecter;

class AbStractColumnReflecter extends DBStructReflecter
{
<<<<<<< HEAD
	abstract function __construct($aDBReflecterFactory, $sTable, $sColumn, $sDBName = null);
	
	abstract public function type();
	
	abstract public function isString();
	
	abstract public function isBool();
	
	abstract public function isInts();
	
	abstract public function isFloat();
	
	abstract public function length();
	
	abstract public function allowNull();
	
	abstract public function defaultValue();
	
	abstract public function comment();

	abstract public function isAutoIncrement();
	
	/**
	 * 列是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	abstract public function isExist();
=======
	function __construct($sDB, $sTable, $sColumn, $sDBName = null)
	{
	
	}
	
	public function type()
	{
	
	}
	
	public function isString()
	{
		;
	}
	
	public function isBool()
	{
		;
	}
	
	public function isInts()
	{
		;
	}
	
	public function isFloat()
	{
		;
	}
	
	public function length()
	{
		;
	}
	
	public function allowNull()
	{
		;
	}
	
	public function defaultValue()
	{
		;
	}
	
	public function comment()
	{
		;
	}
	public function isAutoIncrement()
	{
		;
	}
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
}