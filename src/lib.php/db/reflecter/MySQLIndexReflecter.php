<?php
namespace jc\db\sql\reflecter;




class MySQLIndexReflecter extends AbStractIndexReflecter 
{
	function __construct($sDB ,$sTable,$sIndexName, $sDBName = null)
	{
	}
	
	public function isPrimary()
	{
		
	}
	
	public function isUnique() {
		;
	}
	
	public function isFullText() {
		;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return array
	 */
	public function columnNames() {
		;
	}
}