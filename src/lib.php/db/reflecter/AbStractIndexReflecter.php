<?php
namespace jc\db\sql\reflecter;

class AbStractIndexReflecter extends DBStructReflecter
{
	abstract function __construct($sDB, $sTable, $sIndexName, $sDBName = null);
	
	abstract public function isPrimary();
	
	abstract public function isUnique();
	
	abstract public function isFullText();
	
	/**
	 * 
	 * Enter description here ...
	 * @return array
	 */
	abstract public function columnNames();
}