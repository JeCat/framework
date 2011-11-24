<?php
namespace jc\db\reflecter\imp;

use jc\db\reflecter\AbStractIndexReflecter;

class MockupIndexReflecter extends AbStractIndexReflecter
{
	
	function __construct($aDBReflecterFactory, $sTable, $sIndexName, $sDBName = null)
	{
		$this->sName = $sIndexName;
	}
	
	public function isPrimary()
	{
		if (! isset ( $this->arrMetainfo ['isPrimary'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['isPrimary'];
	}
	
	public function isUnique()
	{
		if (! isset ( $this->arrMetainfo ['isUnique'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['isUnique'];
	}
	
	public function isFullText()
	{
		if (! isset ( $this->arrMetainfo ['isFullText'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['isFullText'];
	}
	
	/**
	 * 索引是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return array
	 */
	public function columnNames()
	{
		if (! isset ( $this->arrMetainfo ['columns'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['columns'];
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public $arrMetainfo = array ();
	public $bIsExist = false;
	
	public $sDBName;
	public $sTable;
	public $sName;

}

?>