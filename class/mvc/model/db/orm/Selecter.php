<?php
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\db\sql\SQL;

use org\jecat\framework\db\sql\MultiTableSQL;

use org\jecat\framework\mvc\model\db\Recordset;
use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\db\sql\Table;
use org\jecat\framework\db\sql\Select;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\sql\StatementFactory ;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\sql\Criteria;

class Selecter extends OperationStrategy
{
	public function execute(Prototype $aPrototype,array & $arrDataSheet,Select $aSelect=null, Restriction $aRestriction=null, $list=false,DB $aDB=null)
	{
		if(!$aDB)
		{
			$aDB = DB::singleton() ;
		}
		
		// -----------------
		// step 1. 组装用于查询的 Select 对象
		if(!$aSelect)
		{
			$aSelect = $aPrototype->sharedStatementSelect() ;
		}
		
		if($aRestriction)
		{
			$aSelect->criteria()->where()->add($aRestriction);
		}
		
		// -----------------
		// step 2. query for all one to one association tables 
		$arrMultitermAssociations = array() ;
		$this->addColumnsForOneToOne($aSelect,$aPrototype,$arrMultitermAssociations) ;
		
		// set limit
		if( !$list )
		{
			$aSelect->criteria()->setLimit(1,0) ;
		}
		else
		{
			$aSelect->criteria()->setLimit( $list[0], $list[1] ) ;
		}
				
		// set group by
		$this->setGroupBy($aSelect->criteria(),$aPrototype) ;
		
		// set order by
		$aSelect->criteria()->clearOrders() ;
		$aSelect->setRawClause(SQL::CLAUSE_ORDER,$aPrototype->criteria()->rawClause(SQL::CLAUSE_ORDER,false)) ;
			
		// query
		$aDB->pdo()->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES,1) ;
		try{
			$aPdoRecordset=$aDB->query( $aSelect, null, Prototype::sqlCompiler() ) ;
		}catch (\Exception $e){}
		
		//} final {
			$aDB->pdo()->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES,0) ;
			if($aRestriction)
			{
				$aSelect->criteria()->where()->remove($aRestriction);
			}
		//}
		
		if( isset($e) )
		{
			throw $e ;
		}
		
		if( !$aPdoRecordset or !$aPdoRecordset->rowCount() )
		{
			return false ;
		}
		
		// load data
		$arrDataSheet = $aPdoRecordset->fetchAll(\PDO::FETCH_ASSOC) ;
		
		// -----------------
		// step 2. query alonely for multiterm associated prototype 
		$nTotalRows = count($arrDataSheet) ;
		for($nRowIdx=0;$nRowIdx<$nTotalRows;$nRowIdx++)
		{
			foreach($arrMultitermAssociations as $aMultitermAssoc)
			{
				$this->queryForMultitermAssoc($aDB,$aMultitermAssoc,$arrDataSheet,$nRowIdx,$aSelect) ;
			}
			
		}

		return true ;
	}
	
	public function totalCount(DB $aDB,Prototype $aPrototype,Criteria $aCriteria=null)
	{
		$aSelect = $aPrototype->sharedStatementSelect() ;
		if($aCriteria)
		{
			$aSelect->setCriteria($aCriteria);
		}
		else 
		{
			$aCriteria = $aSelect->criteria() ;
		}
		$aCriteria->setLimit(-1) ;
		$aCriteria->clearGroupBy() ;
		
		$sKey = 'DISTINCT ' ;
		$nKeyIdx = 0 ;
		foreach($aPrototype->keys() as $sClmName)
		{
			if( $nKeyIdx++ )
			{
				$sKey.= ',' ;
			}
			$sKey.= ' `'.$aPrototype->sqlTableAlias().'`.`'.$sClmName.'`' ;
		}
		
		return $aDB->queryCount( $aSelect, $sKey, Prototype::sqlCompiler() ) ;
	}
	
	public function hasExists(Model $aModel,Prototype $aPrototype=null,Select $aSelect=null,DB $aDB=null)
	{
		if(!$aPrototype)
		{
			$aPrototype = $aModel->prototype() ;
		}
		if(!$aSelect)
		{
			$aSelect = $aPrototype->sharedStatementSelect() ;
		}
		if(!$aDB)
		{
			$aDB = DB::singleton() ;
		}

		// 备份原来的 limit
		$arrRawOriLimit =& $aSelect->rawClause(SQL::CLAUSE_LIMIT,false) ;
		
		$aSelect->criteria()->setLimit(1,0) ;
		
		$aRestriction = $aSelect->where()->createRestriction() ;
		foreach($aPrototype->keys() as $nIdx=>$sKey)
		{
			$aRestriction->expression(array(
					SQL::createRawColumn($aPrototype->sqlTableAlias(), $sKey)
					, '=', SQL::transValue($aModel->data($sKey))
			),true,true) ;
		}
		
		// 查询
		$bExists =  $aDB->queryCount($aSelect)>0 ;
		
		// 移除条件
		$aSelect->where()->remove($aRestriction) ;
		
		// 还原原来的 limit
		$aSelect->setRawClause(SQL::CLAUSE_LIMIT,$arrRawOriLimit) ;
		
		return $bExists ;
	}
	
	private function queryForMultitermAssoc(DB $aDB,Association $aMultitermAssoc,array & $arrDataSheet,$nRowIdx,Select $aSelect)
	{
		$aToPrototype = $aMultitermAssoc->toPrototype() ;
		$aFromPrototype = $aMultitermAssoc->fromPrototype() ;
		
		// 清理一些 select 状态
		$aSelect->clearColumns() ;
		
		// 根据上一轮查询设置条件
		if( $aMultitermAssoc->isType(Association::hasMany) )				// hasMany
		{			
			$aRestraction = $this->makeResrictionForAsscotion(
					$arrDataSheet[$nRowIdx]
					, $aFromPrototype->path()
					, $aMultitermAssoc->fromKeys()
					, $aToPrototype->sqlTableAlias()
					, $aMultitermAssoc->toKeys()
			) ;
		}
		else if( $aMultitermAssoc->isType(Association::hasAndBelongsToMany) )	// hasAndBelongsTo
		{
			$aRestraction = $this->makeResrictionForAsscotion(
					$arrDataSheet[$nRowIdx]
					, $aFromPrototype->path()
					, $aMultitermAssoc->fromKeys()
					, $aMultitermAssoc->bridgeSqlTableAlias()
					, $aMultitermAssoc->toBridgeKeys()
			) ;
		}
		else
		{
			throw new ORMException("what's this?") ;
		}
		
		$aSelect->criteria()->where()->add($aRestraction) ;
			
		// 设置 order by
		$rawOriOrder =& $aSelect->rawClause(SQL::CLAUSE_ORDER,false) ;
		$aSelect->setRawClause( SQL::CLAUSE_ORDER, $aToPrototype->criteria()->rawClause(SQL::CLAUSE_ORDER,false) ) ;

		// 新建的一个记录表
		$sheet =& Model::dataSheet($arrDataSheet,$nRowIdx,$aToPrototype->name(),true) ;
		$this->execute(
				$aToPrototype
				, $sheet
				, $aSelect
				, null
				, array($aToPrototype->limitLength(),$aToPrototype->limitFrom())
				, $aDB
		) ;
		
		// 清理条件 和 恢复order by
		$aSelect->criteria()->where()->remove($aRestraction) ;
		$aSelect->setRawClause(SQL::CLAUSE_ORDER,$rawOriOrder) ;
	}

	private function setGroupBy(Criteria $aCriteria,Prototype $aPrototype)
	{
		$aCriteria->clearGroupBy(false) ;
		foreach($aPrototype->keys() as $sClmName)
		{
			$aCriteria->addGroupBy($sClmName,$aPrototype->sqlTableAlias()) ;
		}
	}
	
	private function addColumnsForOneToOne(Select $aSelect,Prototype $aPrototype,& $arrMultitermAssociations)
	{
		// add columns for pass in prototype
		$arrColumns = array_merge($aPrototype->columns() , $aPrototype->keys());
		foreach($arrColumns as $sColumnName)
		{
			$aSelect->addColumn( $sColumnName, null, $aPrototype->sqlTableAlias() ) ;
		}

		// add columns for one to one associated prototypes
		foreach( $aPrototype->associationIterator() as $aAssoc )
		{
			if( $aAssoc->isType(Association::oneToOne) )
			{
				$aToPrototype = $aAssoc->toPrototype() ;

				// add columns recursively
				$this->addColumnsForOneToOne( $aSelect, $aToPrototype, $arrMultitermAssociations ) ;
			}
			
			else
			{
				$arrMultitermAssociations[] = $aAssoc ;
			}
		}
	}

	
	

	// -- Select for Prototype ------------------------------------------------------
	/**
	 * @return org\jecat\framework\db\sql\Select
	 */
	static public function buildSelect(Prototype $aPrototype)
	{
		$aSelect = new Select($aPrototype->tableName(),$aPrototype->sqlTableAlias()) ;//$aPrototype->statementFactory()->createSelect() ;
				
		// where
		if( $arrRawWhere =& $aPrototype->whereRawSql() )
		{
			$aSelect->setRawClause( SQL::CLAUSE_WHERE, $arrRawWhere ) ;
		}
		
		// 递归连接所有关联原型的 table
		self::joinTables( $aSelect, $aPrototype ) ;
		
		return $aSelect ;
	}
	
	static private function joinTables(MultiTableSQL $aSelect,Prototype $aForPrototype)
	{
		$sFromTableAlias = $aForPrototype->sqlTableAlias() ;
			
		foreach( $aForPrototype->associationIterator() as $aAssoc )
		{
			$aPrototype = $aAssoc->toPrototype() ;
			$sToTableAlias = $aPrototype->sqlTableAlias() ;
			
			// 两表关联
			if( $aAssoc->isType(Association::pair) )
			{				
				$aSelect->_joinTableRaw(
						$sFromTableAlias
						, $aPrototype->tableName()
						, $sToTableAlias
						, $aAssoc->joinType()
						, self::makeResrictionForForeignKey(
								$sFromTableAlias
								, $sToTableAlias
								, $aAssoc->fromKeys()
								, $aAssoc->toKeys()
								, $aAssoc->joinOnRawSql()
						)
				) ;
			}
			
			// 三表关联
			else if( $aAssoc->isType(Association::triplet) )
			{
				$sBridgeTableAlias = $aAssoc->bridgeSqlTableAlias() ;
				
				// 从左表连接到中间表
				$aSelect->_joinTableRaw(
						$sFromTableAlias
						, $aAssoc->bridgeTableName()
						, $sBridgeTableAlias
						, $aAssoc->joinType()
						, self::makeResrictionForForeignKey(
								$sFromTableAlias
								, $sBridgeTableAlias
								, $aAssoc->fromKeys()
								, $aAssoc->toBridgeKeys()
								, $aAssoc->joinOnRawSql()
						)
				) ;
				
				// 从中间表连接到右表				
				$aSelect->_joinTableRaw(
						$sBridgeTableAlias
						, $aPrototype->tableName()
						, $sToTableAlias
						, $aAssoc->joinType()
						, self::makeResrictionForForeignKey(
								$sBridgeTableAlias
								, $sToTableAlias
								, $aAssoc->fromBridgeKeys()
								, $aAssoc->toKeys()
								, $aAssoc->joinOnRawSql()
						)
				) ;
			}
			
			else 
			{
				throw new Exception("what's this?") ;
			}
				
			// 递归
			self::joinTables( $aSelect, $aPrototype ) ;
		}
	}
	
	static private function & makeResrictionForForeignKey($sFromTableName=null,$sToTableName=null,array & $arrFromKeys,array & $arrToKeys,array & $arrConditions=null)
	{
		$arrTokens = array('ON','(') ;
		foreach ($arrFromKeys as $nIdx=>$sFromKey)
		{
			$arrTokens[] = SQL::createRawColumn($sFromTableName, $sFromKey) ;
			$arrTokens[] = '=' ;
			$arrTokens[] = SQL::createRawColumn($sToTableName, $arrToKeys[$nIdx]) ;
		}

		if( $arrConditions )
		{
			$arrOnTokens[] = ',' ;
			$arrOnTokens[] = $arrConditions ;
		}
		
		$arrTokens[] = ')' ;
		
		return $arrTokens ;
	}
}
?>
