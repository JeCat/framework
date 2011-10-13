<?php
namespace jc\db\reflecter;

abstract class AbStractColumnReflecter extends DBStructReflecter
{
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
}