<?php
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\reflecter\AbstractReflecterFactory;
use org\jecat\framework\db\reflecter\AbStractTableReflecter;

class MySQLTableReflecter extends AbStractTableReflecter
{
	function __construct(AbstractReflecterFactory $aDBReflecterFactory, $sTable, $sDBName = null) 
	{
		$aDB = $aDBReflecterFactory->db();
		
		if( !$aResult=$aDB->query($this->makeGetColumnsSql($sTable,$sDBName)) or $aResult->rowCount()==0 )
		{
			$this->bIsExist = false;
			return ;
		}
		
		// 反射字段
		$arrColumnNames = $aResult->fetchAll(\PDO::FETCH_ASSOC) ;
		foreach ( $arrColumnNames as $arrColumn )
		{
			$this->arrColumnNames [] = $arrColumn['Field'] ;
		}
		
		if($aResult=$aDB->query($this->makeTableStatusSql($sTable)))
		{
			$arrRow = $aResult->fetch(\PDO::FETCH_ASSOC) ;
			$this->sComment = $arrRow['Comment'] ;
			$this->nAutoINcrement = $arrRow['Auto_increment'] ;
		}
		
		// 反射所有索引
		$this->arrIndexes = MySQLIndexReflecter::reflectTableIndexes($sTable,$sDBName,$aDB) ;
		
		// 主键
		if(isset($this->arrIndexes['PRIMARY']))
		{
			$this->sPrimaryName = reset($this->arrIndexes['PRIMARY']->columnNames()) ;
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
	
	public function columns()
	{
		return $this->arrColumnNames ;
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	public function indexIterator()
	{
		return new \ArrayIterator($this->arrIndexes) ;
	}
	public function indexNameIterator()
	{
		return new \ArrayIterator(array_keys($this->arrIndexes)) ;
	}
	public function indexReflecter($sIndexName)
	{
		return $this->arrIndexes[$sIndexName] ;
	}
	
	private $sPrimaryName = null;
	
	private $nAutoINcrement;
	
	private $sComment;
	
	private $arrColumnNames;
	
	private $arrIndexes;
	
	private $sName;
	
	private $bIsExist=true;
}
