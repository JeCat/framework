<?php
namespace jc\db\reflecter;


class MockupTableReflecter extends AbStractTableReflecter
{
	function __construct($aDBReflecterFactory, $sTable, $sDBName = null) 
	{
		$aDB = $aDBReflecterFactory->db();
		$aIterResults = $aDB->query ( $this->makeGetColumnsSql($sTable, $sDBName) );
		
		if($aIterResults->rowCount() == 0)
		{
			$this->bIsExist = false;
			return ;
		}
		
		foreach ( $aIterResults as $aResult )
		{
			$this->arrColumnNames [] = $aResult['Field'];
		}
		
		$aIterStatus = $aDB->query ( $this->makeTableStatusSql($sTable) );
		$this->sComment = $aIterStatus->field ( 'Comment', 0 );
		$this->nAutoINcrement = $aIterStatus->field ( 'Auto_increment', 0 );
		
		$aIndexReflecter = $aDBReflecterFactory->indexReflecter($sTable, 'PRIMARY', $sDBName);
		if($aIndexReflecter->isExist())
		{
			$arrNames = $aIndexReflecter->columnNames();
			$this->sPrimaryName = $arrNames[0];
		}
		
		$this->sName = $sTable;
	}
	
	private function makeGetColumnsSql($sTable, $sDBName)
	{
		return "show columns from `" . $sDBName . "`.`" . $sTable . "`";
	}
	
	private function makeTableStatusSql($sTable)
	{
		return "show table status where name ='" . $sTable . "'";
	}
	
	public function primaryName()
	{
		return $this->sPrimaryName;
	}
	
	public function autoIncrement()
	{
		return $this->nAutoINcrement;
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
	
	private $sPrimaryName = null;
	
	private $nAutoINcrement;
	
	private $sComment;
	
	private $arrColumnNames;
	
	private $sName;
	
	private $bIsExist=true;
}

?>