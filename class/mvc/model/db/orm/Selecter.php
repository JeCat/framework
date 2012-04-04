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
			
		// query
		$aDB->pdo()->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES,1) ;
		try{
			$aPdoRecordset=$aDB->query($aSelect) ;
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
		
		return $aDB->queryCount($aSelect,$sKey) ;
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
		
		/*$aCriteria = $aPrototype->statementFactory()
						->createCriteria()
						->setLimit(1) ;
		$aSelect->setCriteria($aCriteria) ;*/
		
		foreach($aPrototype->keys() as $sKey)
		{
			$aSelect->where()->eq($sKey,$aModel->data($sKey),$aPrototype->sqlTableAlias()) ;
		}
		
		return $aDB->queryCount($aSelect)>0 ;
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
					, $aFromPrototype->statementFactory()
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
					, $aFromPrototype->statementFactory()
			) ;
		}
		else
		{
			throw new ORMException("what's this?") ;
		}
	
		if( !$aTablesJoin=$aMultitermAssoc->sqlTablesJoin() )
		{
			throw new ORMException("关联对象没有TablesJoin对象") ;
		}
		
		// 设置查询条件
		$aSelect->criteria()->where()->add($aRestraction) ;
		
		// 设置 order by
		$aOriOrders = $aSelect->criteria()->orders(false) ;
		if($aToPrototype->criteria()->orders(false)){
			$aSelect->criteria()->setOrders($aToPrototype->criteria()->orders(false)) ;
		}

		// 新建的一个记录表
		$sheet =& Model::dataSheet($arrDataSheet,$nRowIdx,$aToPrototype->name(),true) ;
		$this->execute($aToPrototype,$sheet,$aSelect,null,true,$aDB) ;
		
		// 清理条件 和 恢复order by
		$aSelect->criteria()->where()->remove($aRestraction) ;
		if($aOriOrders){
			$aSelect->criteria()->setOrders($aOriOrders) ;
		}
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
		
		// 主表的名称
		//$aTable = $aPrototype->createSqlTable() ;
		//$aSelect->addTable($aTable) ;
		
		// criteria
		//$aCriteria = clone $aPrototype->criteria() ;
		//$aSelect->setCriteria( $aCriteria ) ;
		
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
				
				$arrOnTokens =& self::makeResrictionForForeignKey($sFromTableAlias,$sToTableAlias,$aAssoc->fromKeys(), $aAssoc->toKeys()) ;
				array_unshift($arrOnTokens, 'ON', '(') ;
				$arrOnTokens[] = ')' ;
				
				$aSelect->_joinTableRaw(
						$sFromTableAlias
						, $aPrototype->tableName()
						, $sToTableAlias
						, $aAssoc->joinType()
						, $arrOnTokens
				) ;
			}
			
			// 三表关联
			else if( $aAssoc->isType(Association::triplet) )
			{
				$sBridgeTableAlias = $aAssoc->bridgeSqlTableAlias() ;
				
				// 从左表连接到中间表
				$arrOnTokens =& self::makeResrictionForForeignKey($sFromTableAlias,$sBridgeTableAlias,$aAssoc->fromKeys(),$aAssoc->toBridgeKeys()) ;
				array_unshift($arrOnTokens, 'ON', '(') ;
				$arrOnTokens[] = ')' ;
				
				$aSelect->_joinTableRaw(
						$sFromTableAlias
						, $aAssoc->bridgeTableName()
						, $sBridgeTableAlias
						, $aAssoc->joinType()
						, $arrOnTokens
				) ;
				
				// 从中间表连接到右表
				$arrOnTokens =& self::makeResrictionForForeignKey($sBridgeTableAlias,$sToTableAlias,$aAssoc->fromBridgeKeys(),$aAssoc->toKeys()) ;
				array_unshift($arrOnTokens, 'ON', '(') ;
				$arrOnTokens[] = ')' ;
				
				$aSelect->_joinTableRaw(
						$sBridgeTableAlias
						, $aPrototype->tableName()
						, $sToTableAlias
						, $aAssoc->joinType()
						, $arrOnTokens
				) ;
				
			
				// 在桥接表上加入自定义的 join on 条件
				/*if( $aTablesJoinOn = $aAssoc->otherBridgeTableJoinOn(false) )
				{
					$aBridgeTablesJoin->on()->add($aTablesJoinOn) ;
				}*/
			}
			
			else 
			{
				throw new Exception("what's this?") ;
			}
		
			// 加入自定义的 join on 条件
			/*if( $aOtherTablesJoinOn = $aAssoc->otherTableJoinOn(false) )
			{
				$aTablesJoin->on()->add($aOtherTablesJoinOn) ;
			}*/
				
			// 递归
			self::joinTables( $aSelect, $aPrototype ) ;
		}
	}
	
	static private function & makeResrictionForForeignKey($sFromTableName=null,$sToTableName=null,array & $arrFromKeys,array & $arrToKeys)
	{		
		$arrTokens = array() ;
		foreach ($arrFromKeys as $nIdx=>$sFromKey)
		{
			$arrTokens[] = SQL::createRawColumn($sFromTableName, $sFromKey) ;
			$arrTokens[] = '=' ;
			$arrTokens[] = SQL::createRawColumn($sToTableName, $arrToKeys[$nIdx]) ;
		}
		
		return $arrTokens ;
	}
}
?>
