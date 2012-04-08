<?php
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\DB;
use org\jecat\framework\db\reflecter\AbStractIndexReflecter;

class MySQLIndexReflecter extends AbStractIndexReflecter
{
	static public function reflectTableIndexes($sTableName,$sDBName=null,DB $aDB=null)
	{
		if(!$aDB)
		{
			$aDB = DB::singleton() ;
		}
		
		if( !$aResult=$aDB->query(self::makeIndexSql($sTableName,$sDBName)) or $aResult->rowCount()==0 )
		{
			return array() ;
		}
		$arrAllIndexes = array() ;
		foreach( $aResult->fetchAll(\PDO::FETCH_ASSOC) as $arrIndexRow)
		{
			if( !isset($arrAllIndexes[ $arrIndexRow['Key_name'] ]) )
			{
				$arrAllIndexes[ $arrIndexRow['Key_name'] ] = $arrIndexRow ;
			}
			
			$arrAllIndexes[ $arrIndexRow['Key_name'] ]['columns'][] = $arrIndexRow['Column_name'] ;
		}
		
		$arrIndexReflecters = array() ;
		foreach($arrAllIndexes as $sIndexName=>$arrIndex)
		{			
			$aIndexReflecter = new self() ;
			
			// $aIndexReflecter
			$aIndexReflecter->bIsPrimary = $arrIndex['Key_name']=='PRIMARY' ;
			$aIndexReflecter->bIsFullText = $arrIndex['Index_type']=='FULLTEXT';
			$aIndexReflecter->bIsUnique = $arrIndex['Non_unique']=='0' ;
			$aIndexReflecter->sName = $sIndexName ;
			$aIndexReflecter->arrColumnsNames = $arrIndex['columns'] ;
			
			$arrIndexReflecters[$sIndexName] = $aIndexReflecter ;
		}
		
		return $arrIndexReflecters ;
	}
	
	static private function makeIndexSql($sTable,$sDBName=null)
	{
		if($sDBName)
		{
			$sTable = "`{$sDBName}`.`{$sTable}`" ;
		}
		else
		{
			$sTable = "`{$sTable}`" ;
		}
		return "SHOW index FROM " . $sTable ;
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