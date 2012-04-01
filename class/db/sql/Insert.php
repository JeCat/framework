<?php
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\lang\Exception ;

class Insert extends SQL
{
	public function __construct($sTableName="")
	{
		$this->setTableName($sTableName) ;
	}
	
	public function tableName() 
	{
		$arrRawInto =& $this->rawClause(SQL::CLAUSE_INTO,$this->rawClause(SQL::CLAUSE_INSERT)) ;
		if( empty($arrRawInto['subtree'][0]['table']) or $arrRawInto['subtree'][0]['expr_type']!=='table' )
		{
			return null ;
		}
		return $arrRawInto['subtree'][0]['table'] ;
	}
	
	public function setTableName($sTableName,$sDBName=null) 
	{
		$arrRawInto =& $this->rawClause(SQL::CLAUSE_INTO,$this->rawClause(SQL::CLAUSE_INSERT)) ;
		$arrRawInto['subtree'] = array(
				self::createRawTable($sTableName,null,$sDBName)
		) ;
		
		return $this ;
	}
	
	public function setDatas(array $arrDatas)
	{
		$arrRawValues =& $this->rawClause(SQL::CLAUSE_VALUES,$this->rawClause(SQL::CLAUSE_INSERT)) ;
		$arrRawValues['pretree'] = array('(') ;
		$arrRawValues['subtree'] = array('(') ;
		
		foreach($arrDatas as $sClm=>&$value)
		{
			$arrRawValues['pretree'][$sClm] = self::createRawColumn(null,$sClm) ;
			$arrRawValues['subtree'][$sClm] = self::transValue($value) ;
		}
		$arrRawValues['pretree'][] = ')' ;
		$arrRawValues['pretree'][] = 'VALUES' ;
		$arrRawValues['subtree'][] = ')' ;
		
		return $this ;
			
	}

	public function addRow(array $arrDatas)
	{

		return $this ;
	}
	
	public function data($sColumnName)
	{
		return isset($this->mapData[$sColumnName])? $this->mapData[$sColumnName]: null ;
	}
	
	
	public function clearData()
	{
		$arrRawInsert = $this->rawClause(SQL::CLAUSE_INSERT) ;
		unset($arrRawInsert['subtree'][SQL::CLAUSE_VALUES]) ;
		return $this ;
	}
	/*
	public function removeData($sColumnName)
	{
		unset($this->mapData[$sColumnName]) ;
	}

	public function dataIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->mapData) ;
	}

	public function dataNameIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( array_keys($this->mapData) ) ;
	}*/
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sTableName = "" ;
	
	private $mapData = array() ;
}

?>