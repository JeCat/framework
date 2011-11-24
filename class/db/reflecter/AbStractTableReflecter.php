<?php
namespace org\jecat\framework\db\reflecter;

abstract class AbStractTableReflecter extends DBStructReflecter
{
	abstract function __construct($aDBReflecterFactory ,$sTable, $sDBName=null);
	
	/**
	 * @return string 如果主键不存在返回null
	 */
	abstract public function primaryName();
	
	abstract public function autoIncrement();
	
	abstract public function comment();
	
	/**
	 * 
	 * Enter description here ...
	 * @return \Iterator
	 */
	abstract public function columns();
	
	/**
	 * 表是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	abstract public function isExist();
}