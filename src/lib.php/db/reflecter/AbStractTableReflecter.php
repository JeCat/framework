<?php
namespace jc\db\sql\reflecter;


class AbStractTableReflecter extends DBStructReflecter
{
	abstract function __construct($sDB ,$sTable, $sDBName=null);
	
	abstract public function primaryName();
	
	abstract public function autoIncrement();
	
	abstract public function comment();
	
	/**
	 * 
	 * Enter description here ...
	 * @return \Iterator
	 */
	abstract public function columnNameIterator();
}