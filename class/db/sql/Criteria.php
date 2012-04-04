<?php
namespace org\jecat\framework\db\sql ;

class Criteria extends SQL
{	
	// -- limit --
	
	/**
	 *  设置limit条件
	 * @param int $nLimitLen limit 长度
	 * @param int $sLimitFrom limit 开始
	 */
	public function setLimit($nLimitLen,$limitFrom = null)
	{
		if($nLimitLen<0)
		{
			$this->clearLimit() ;
		}
		else 
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
		}
	
		return $this ;
	}
	
	public function clearLimit()
	{
		unset($this->arrRawSql[self::CLAUSE_LIMIT]) ;
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
	public function where($bAutoCreate=true)
	{
		$arrRawWhere =& $this->rawClause(self::CLAUSE_WHERE) ;
	
		if( !$this->aWhere and $bAutoCreate )
		{
			$this->aWhere = new Restriction() ;
			$this->aWhere->setRawSql($arrRawWhere) ;
		}
	
		return $this->aWhere ;
	}
	
	
	// -- order by --
	public function addOrderBy($sColumn,$bDesc=true,$sTable=null)
	{
		$arrRawOrder =& $this->rawClause(self::CLAUSE_ORDER) ;
	
		$arrRawOrder['subtree'][] = self::createRawColumn($sTable, $sColumn) ;
		$arrRawOrder['subtree'][] = $bDesc? 'DESC': 'ASC' ;
	
		return $this ;
	}
	
	public function clearOrders()
	{
		unset($this->arrRawSql[self::CLAUSE_ORDER]) ;
		return $this ;
	}
	
	// -- group by --
	public function addGroupBy($sColumn,$sTable=null,$sDB=null)
	{
		$arrGroupBy =& $this->rawClause(self::CLAUSE_GROUP) ;
		if( !empty($arrGroupBy['subtree']) )
		{
			$arrGroupBy['subtree'][] = ',' ;
		}
		$arrGroupBy['subtree'][] = self::createRawColumn($sTable, $sColumn, null, $sDB) ;
	
		return $this ;
	}
	
	public function clearGroupBy($bRemoveCluuse=true)
	{
		if($bRemoveCluuse)
		{
			unset($this->arrRawSql['subtree'][self::CLAUSE_GROUP]) ;
		}
		else if(isset($this->arrRawSql['subtree'][self::CLAUSE_GROUP]))
		{
			$this->arrRawSql['subtree'][self::CLAUSE_GROUP]['subtree'] = array() ;
		}
		return $this ;
	}
	
	public function attache(array & $arrRawSql)
	{
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_WHERE]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_WHERE] =& $this->arrRawSql['subtree'][self::CLAUSE_WHERE] ;
		}
		
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_GROUP]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_GROUP] =& $this->arrRawSql['subtree'][self::CLAUSE_GROUP] ;
		}
		
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_ORDER]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_ORDER] =& $this->arrRawSql['subtree'][self::CLAUSE_ORDER] ;
		}
		
		if( isset($this->arrRawSql['subtree'][self::CLAUSE_LIMIT]) )
		{
			$arrRawSql['subtree'][self::CLAUSE_LIMIT] =& $this->arrRawSql['subtree'][self::CLAUSE_LIMIT] ;
		}
	}
	
	private $aWhere ;
}

?>