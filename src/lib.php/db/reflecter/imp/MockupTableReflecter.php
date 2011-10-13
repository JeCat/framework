<?php
namespace jc\db\reflecter\imp;

use jc\db\reflecter\AbStractTableReflecter;

class MockupTableReflecter extends AbStractTableReflecter
{
	function __construct($aDBReflecterFactory, $sTable, $sDBName = null) 
	{
		$this->sName = $sTable;
	}
	
	public function primaryName()
	{
		return $this->sPrimaryName;
	}
	
	public function autoIncrement()
	{
		return $this->nAutoIncrement;
	}
	
	public function comment()
	{
		return $this->sComment;
	}
	
	/**
	 * @return \Iterator
	 */
	public function columnNameIterator()
	{
		return new \ArrayIterator ( $this->arrColumnNames );
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	public $sPrimaryName = null;
	
	public $nAutoIncrement;
	
	public $sComment;
	
	public $arrColumnNames;
	
	public $sName;
	
	public $bIsExist=false;
}

?>