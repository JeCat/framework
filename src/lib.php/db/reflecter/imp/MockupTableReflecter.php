<?php
namespace jc\db\reflecter\imp;

use jc\db\reflecter\AbStractTableReflecter;

class MockupTableReflecter extends AbStractTableReflecter
{
	function __construct($aDBReflecterFactory, $sTable, $sDBName = null) 
	{
		$this->sName = $sTable;
		$this->sDBName = $sDBName;
	}
	
	public function primaryName()
	{
		return $this->arrMetainfo['primaryName'];
	}
	
	public function autoIncrement()
	{
		return $this->arrMetainfo['autoIncrement'];
	}
	
	public function comment()
	{
		return $this->arrMetainfo['comment'];
	}
	
	/**
	 * @return \Iterator
	 */
	public function columnNameIterator()
	{
		return new \ArrayIterator ( array_keys($this->arrMetainfo['columns']) );
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	public $arrMetainfo = array();
	
	public $sName;
	
	public $sDBName;
	
	public $bIsExist=false;
}

?>