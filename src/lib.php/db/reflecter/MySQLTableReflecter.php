<?php
namespace jc\db\sql\reflecter;


class MySQLTableReflecter extends AbStractTableReflecter
{
	function __construct($sDB ,$sTable, $sDBName=null)
	{
		
	}
	
	public function primaryName()
	{
		
	}
	
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
	public function columnNameIterator() {
		;
	}
	
}