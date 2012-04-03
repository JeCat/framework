<?php
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\lang\Exception ;

class Insert extends SQL
{
	public function __construct($sTableName="")
	{
		$this->arrRawSql = array(
				'expr_type' => 'query' ,
				'subtree' => array() ,
		) ;
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
	
	public function setData($sColumn,$value=null,$bValueExpr=false,$nRow=0)
	{
		$arrRawValues =& $this->rawClauseValue() ;
		
		$sValueRowKey = 'ROW'.$nRow ;
		
		// 更改已经存在的数据
		if( isset($arrRawValues['pretree']['COLUMNS']['subtree'][$sColumn]) )
		{
			$arrRawValues['subtree'][$sValueRowKey]['subtree'][$sColumn]['subtree'] = $bValueExpr? array( $value ): array( $value ) ;
		}
		
		// 插入新数据
		else 
		{
			if(!empty($arrRawValues['pretree']['COLUMNS']['subtree']))
			{
				$arrRawValues['pretree']['COLUMNS']['subtree'][] = ',' ;
			}
			$arrRawValues['pretree']['COLUMNS']['subtree'][$sColumn] = self::createRawColumn(null,$sColumn) ;
			
			// 插入行
			if( !isset($arrRawValues['subtree'][$sValueRowKey]) )
			{
				if( !empty($arrRawValues['subtree']) )
				{
					$arrRawValues['subtree'][] = ',' ;
				}
				$arrRawValues['subtree'][$sValueRowKey] = array(
							'expr_type' => 'values_row' ,
							'subtree' => array() ,
				) ;
			}
			
			// 写入数据
			if( !empty($arrRawValues['subtree'][$sValueRowKey]['subtree']) )
			{
				$arrRawValues['subtree'][$sValueRowKey]['subtree'][] = ',' ;
			}
			
			if($bValueExpr)
			{
				// todo ...
			}
			else
			{
				$arrRawValues['subtree'][$sValueRowKey]['subtree'][$sColumn] = $value ;
			}
		}
		
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

	
	static public function createRawInsertValues()
	{
		return array(
				'expr_type' => 'clause_values' ,
				'pretree' => array(
						'(' ,
						'COLUMNS'=>array(
								'expr_type' => 'values_clmlst' ,
								'subtree' => array() ,
						) ,
						')' ,
						'VALUE' ,
				) ,
				'subtree' => array() ,
		) ;
	}
	
	protected function & rawClauseValue()
	{
		$arrRawInsert =& $this->rawClause(SQL::CLAUSE_INSERT) ;
		
		if( !isset($arrRawInsert['subtree'][SQL::CLAUSE_VALUES]) )
		{
			$arrRawInsert['subtree'][SQL::CLAUSE_VALUES] = self::createRawInsertValues() ;
		}
		return $arrRawInsert['subtree'][SQL::CLAUSE_VALUES] ;
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