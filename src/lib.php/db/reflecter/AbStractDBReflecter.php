<?php
namespace jc\db\reflecter;


class AbStractDBReflecter extends DBStructReflecter
{
	abstract function __construct($aDBReflecterFactory , $sDBName);
	
	/**
	 * 
	 * Enter description here ...
	 * @return \iterator
	 */
	abstract public function tableNameIterator();
	
	/**
	 * 库是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	abstract public function isExist();
}