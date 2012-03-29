<?php 
namespace org\jecat\framework\db\sql2 ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Type;

abstract class MultiTableSQL extends SQL
{
	function __construct($sTableName=null,$sTableAlias=null)
	{
		if($sTableName)
		{
			$this->addTable($sTableName,$sTableAlias) ;
		}
	}
	
	function __clone()
	{}
		
	// -- from --
	
	/**
	 * 参数 $table 可以是一个表示表名的字符串，也可以是一个 Table 对像 
	 * 
	 * @param $table	string,Table
	 */
	public function addTable($table,$sAlias=null)
	{
		if( is_string($table) )
		{
			$arrRawTable = self::createRawTable($table,$sAlias) ;
		}
		
		else if( $table instanceof Table )
		{
			$sAlias = $table->alias() ;
			$arrRawTable =& $table->rawSql() ;
		}
		
		else if( $table instanceof Select )
		{
			
		}
		
		// 未知类型
		else
		{
			throw new Exception("参数类型无效") ;
		}
		
		$arrRawFrom =& $this->rawClause(self::CLAUSE_FROM) ;
		
		if($sAlias)
		{
			$arrRawFrom['subtree'][$sAlias] =& $arrRawTable ;
		}
		else
		{
			$arrRawFrom['subtree'][] =& $arrRawTable ;
		}
	}
	
	static private function tableFromRaw(array & $arrRawTable)
	{
		if(!isset($arrRawTable['object']))
		{
			switch ( $arrRawTable['expr_type'] )
			{
				case 'table':
					$arrRawTable['object'] = new Table() ;
					break ;
				case 'subquery':
					$arrRawTable['object'] = new Select() ;
					break ;
				default :
					throw new Exception("遇到无效的 table 类型： %s",$arrRawTable['expr_type']) ;
				break ;
			}
	
			$arrRawTable['object']->setRawSql($arrRawTable) ;
		}
	
		return $arrRawTable['object'] ;
	}
	
	/**
	 * 返回指定数据表名所属的subtree数组，并加索引改为表名
	 */
	protected function & findTableRaw($sTableName,array&$arrRawTree)
	{		
		// 先用序号直接查找
		if( isset($this->arrRawSql['tables'][$sTableName]) )
		{
			return $this->arrRawSql['tables'][$sTableName] ;
		}
		
		foreach($arrRawTree as &$rawToken)
		{
			if( !is_array($rawToken) )
			{
				continue ;
			}
			else if( $rawToken['expr_type'] == 'table' )
			{
				if( $sTableName === (isset($rawToken['as'])? $rawToken['as']: $rawToken['table']) )
				{
					$this->arrRawSql['tables'][$sTableName] =& $rawToken ;
					return $rawToken ;
				}
			}
			else if( !empty($rawToken['subtree']) )
			{
				if( $arrNameToken =& $this->findNameRaw($sName,$rawToken['subtree'],$sType) )
				{
					return $arrNameToken ;
				}
			}
		}
		
		return $null = null ;
	}

	public function joinTable($sFromTable,$sToTable,$on=null,$using=null,$sAlias=null,$sJoinType='LEFT')
	{
		$arrRawFrom =& $this->rawClause(self::CLAUSE_FROM) ;
		if( !$arrFromTableToken=&$this->findTableRaw($sFromTable,$arrRawFrom['subtree']) )
		{
			throw new Exception("名为 %s 的数据表不存在，无法在该数据表上 join 另一个数据表。",$sFromTable) ;
		}
		
		$arrFromTableToken['subtree'][] = $sJoinType ;
		$arrFromTableToken['subtree'][] = 'JOIN' ;
		$arrFromTableToken['subtree'][] = '(' ;
		$arrFromTableToken['subtree'][] =  self::createRawTable($sToTable,$sAlias) ;
		$arrFromTableToken['subtree'][] = ')' ;
		
		if($on)
		{
			$arrFromTableToken['subtree'][] = 'ON' ;
			$arrFromTableToken['subtree'][] = '(' ;
			$arrFromTableToken['subtree'][] =  $on ;
			$arrFromTableToken['subtree'][] = ')' ;
		}
		if($using)
		{
			$arrFromTableToken['subtree'][] = 'USING' ;
			$arrFromTableToken['subtree'][] = '(' ;
			$arrFromTableToken['subtree'][] =  $using ;
			$arrFromTableToken['subtree'][] = ')' ;
		}
		
		/*$arrRawFromTable =& $arrFromTableList[$sFromTable] ;
		$arrRawToTable = self::createRawTable($sToTable,$sAlias) ;
		
		// from表 没有被其它表连接
		if( in_array( $arrRawFromTable['join_type'], array('JOIN','CROSS') ) )
		{
			// 在 from 表后面 插入 join 表
			$pos = array_search($arrRawFromTable,$arrFromTableList) ;
			array_splice($arrFromTableList,$pos,0,$arrRawToTable) ;
		}
		
		// from表 已经被其它表连接
		else
		{
			// 将 from 表替换成 table_expression 类型
			$arrNewRawFromTable = $arrRawFromTable ;
			$arrNewRawFromTable['join_type'] = 'JOIN' ;
			$arrRawToTable['join_type'] = $sJoinType ;
			
			unset($arrRawFromTable['table']) ;
			unset($arrRawFromTable['alias']) ;
			$arrRawFromTable['expr_type'] = 'table_expression' ;
			$arrRawFromTable['sub_tree'] = array() ;
			
			$arrRawFromTable['sub_tree'][] = $arrNewRawFromTable ;
			$arrRawFromTable['sub_tree'][] =& $arrRawToTable ;
		}
		*/
		return $this ;
	}
	
	public function clearTables()
	{
		unset($this->arrRawSql[self::CLAUSE_FROM]) ;
	}
	
	/**
	 * @return array[Table]
	 */
	public function tableIterator()
	{
		return isset($this->arrRawSql[self::CLAUSE_FROM])?
				new \ArrayIterator(self::allTables($this->arrRawSql[self::CLAUSE_FROM])) :
				new \EmptyIterator() ;
	}
	
	static private function allTables(&$arrTableList)
	{
		$arrTables = array() ;
		foreach($arrTableList as &$arrRawTable)
		{
			if( $arrRawTable['expr_type'] == 'table_expression' )
			{
				$arrTables = array_merge($arrTables,self::allTables($arrRawTable['sub_tree'])) ;
			}
			else
			{
				$arrTables[] = self::tableFromRaw($arrRawTable) ;
			}
		}
		
		return $arrTables ;
	}
	
	/**
	 * @return array[Table]
	 */
	public function table($sAlias)
	{
		if( !$arrTableList =& self::findTableList($sAlias,$this->rawClause(self::CLAUSE_FROM)) )
		{
			return null ;
		}
		return self::tableFromRaw($arrTableList[$sAlias]) ;
	}
	
	// -- limit --
	
	/**
	 *  设置limit条件
	 * @param int $nLimitLen limit 长度
	 * @param int $sLimitFrom limit 开始
	 */
	public function setLimit($nLimitLen , $limitFrom = null)
	{
		$arrRawLimit =& $this->rawClause(self::CLAUSE_LIMIT) ;
		
		if( $limitFrom!==null )
		{
			$arrRawLimit['subtree'][] = $limitFrom ;
			$arrRawLimit['subtree'][] = ',' ;
			$arrRawLimit['subtree'][] = $nLimitLen ;
		}
		else 
		{
			$arrRawLimit['subtree'][] = $nLimitLen ;
		}
		
		return $this ;
	}
	
	// -- where --	
	public function setWhere(Restriction $aWhere){
		$this->aWhere = $aWhere;
		$this->setRawWhere( $aWhere->rawSql() ) ;
		return $this ;
	}
	/**
	 * @return Restriction
	 */
	public function where($bAutoCreate=true){
		if( !isset($this->arrRawSql[self::CLAUSE_WHERE]) )
		{
			$this->arrRawSql[self::CLAUSE_WHERE]['subtree'] = array() ;
		}
		
		if( !$this->aWhere )
		{
			$this->aWhere = new Restriction() ;
			$this->aWhere->setRawSql($this->arrRawSql[self::CLAUSE_WHERE]['subtree']) ;
		}
		
		return $this->aWhere ;
	}
	
	
	// -- order by --
	public function addOrderBy($sColumn,$bDesc=true)
	{
		$arrRawOrder =& $this->rawClause(self::CLAUSE_ORDER) ;
		
		$arrRawOrder['subtree'][] = self::createRawColumn(null, $sColumn) ;
		$arrRawOrder['subtree'][] = $bDesc? 'DESC': 'ASC' ;
		
		return $this ;
	}
	
	public function clearOrders()
	{
		unset($this->arrRawSql[self::CLAUSE_ORDER]) ;
		return $this ;
	}
	
	// -- group by --
	public function addGroupBy($columns)
	{
		$columns = Type::toArray($columns,Type::toArray_emptyForNull) ;
		if($columns)
		{
			$arrGroupBy =& $this->rawClause(self::CLAUSE_GROUP) ;
			
			foreach($columns as $sColumn)
			{
				array_unshift($arrGroupBy['subtree'],array(
					'type' => 'expression' ,
					'base_expr' => $sColumn ,
				)) ;
			}
		}
		return $this ;
	}
	
	public function clearGroupBy()
	{
		unset($this->arrRawSql[self::CLAUSE_GROUP]) ;
		return $this ;
	}
}

?>