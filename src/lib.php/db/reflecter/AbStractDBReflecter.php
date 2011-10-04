<?php
namespace jc\db\sql\reflecter;


class AbStractDBReflecter extends DBStructReflecter
{
	abstract function __construct($sDB , $sDBName);
	
	/**
	 * 
	 * Enter description here ...
	 * @return \iterator
	 */
	abstract public function tableNameIterator();
}