<?php
namespace jc\db\sql\reflecter;

class AbStractColumnReflecter extends DBStructReflecter
{
	abstract function __construct($sDB, $sTable, $sColumn, $sDBName = null);
	
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
}