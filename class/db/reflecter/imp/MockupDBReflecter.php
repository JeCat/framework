<?php
namespace jc\db\reflecter\imp;

use jc\db\reflecter\AbStractDBReflecter;

class MockupDBReflecter extends AbStractDBReflecter
{
	function __construct($aDBReflecterFactory,$sDBName)
	{
		$this->sName = $sDBName;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return \iterator
	 */
	public function tableNameIterator()
	{
		return new \ArrayIterator ( $this->arrTableNames );
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	public $arrTableNames = array() ;
	
	public $sName;
	
	public $bIsExist = false ;
}