<?php
namespace jc\db\reflecter\imp;

use jc\db\reflecter\AbStractIndexReflecter;

class MySQLIndexReflecter extends AbStractIndexReflecter
{
	function __construct($aDBReflecterFactory, $sTable, $sIndexName, $sDBName = null)
	{
		$aDB = $aDBReflecterFactory->db();
		$aIterIndex = $aDB->query ($this->makeIndexSql($sTable, $sIndexName, $sDBName));
		
		if($aIterIndex->rowCount() == 0)
		{
			$this->bIsExist = false;
			return;
		}
		
		for($i = 0; $i < $aIterIndex->rowCount (); $i ++)
		{
			$this->arrColumnsNames [] = $aIterIndex->field ( 'Column_name', $i );
		}
		
		if ($aIterIndex->field ( 'Key_name', 0 ) === 'PRIMARY')
		{
			$this->bIsPrimary = true;
		}
		
		if ($aIterIndex->field ( 'Index_type', 0 ) === 'FULLTEXT')
		{
			$this->bIsFullText = true;
		}
		
		if ($aIterIndex->field ( 'Non_unique', 0 ) == '0')
		{
			$this->bIsUnique = true;
		}
		
		$this->sName = $sIndexName;
	}
	
	private function makeIndexSql($sTable, $sIndexName, $sDBName) {
		 return "SHOW index FROM `" . $sDBName . "`.`" . $sTable . "` " . "WHERE `Key_name`='" . $sIndexName . "'" ;
	}
	
	public function isPrimary()
	{
		return $this->bIsPrimary;
	}
	
	public function isUnique()
	{
		return $this->bIsUnique;
	}
	
	public function isFullText()
	{
		return $this->bIsFullText;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function columnNames()
	{
		return $this->arrColumnsNames;
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	private $bIsExist = true;
	
	private $bIsPrimary = false;
	
	private $bIsUnique = false;
	
	private $bIsFullText = false;
	
	private $arrColumnsNames = array ();
	
	private $sName;
}