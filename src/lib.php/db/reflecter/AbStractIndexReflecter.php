<?php
namespace jc\db\reflecter;

class AbStractIndexReflecter extends DBStructReflecter
{
	abstract function __construct($aDBReflecterFactory, $sTable, $sIndexName, $sDBName = null);
	
	abstract public function isPrimary();
	
	abstract public function isUnique();
	
	abstract public function isFullText();
	
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
	abstract public function columnNames();
}