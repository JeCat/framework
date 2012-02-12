<?php
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\db\sql\Table;
use org\jecat\framework\db\sql\Select;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\IModel ;
use org\jecat\framework\db\sql\StatementFactory ;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\sql\Criteria;

class Selecter extends OperationStrategy
{
	public function execute(IModel $aModel,Select $aSelect=null,Criteria $aCriteria=null,$bList=false,DB $aDB=null)
	{
		if( !$aPrototype = $aModel->prototype() )
		{
			throw new ORMException("传入了无效的 IModel 对象，\$aModel的 prototype() 方法返回null") ;	
		}
		
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
		
		if($aCriteria)
		{
			$aSelect->setCriteria($aCriteria);
		}
		
		// -----------------
		// step 2. query for all one to one association tables 
		$arrMultitermAssociations = array() ;
		$this->addColumnsForOneToOne($aSelect,$aPrototype,$arrMultitermAssociations) ;
		
		// set limit
		if( !$bList )
		{
			$aSelect->criteria()->setLimitLen(1) ;
		}
		else
		{
			$aSelect->criteria()->setLimit( $aPrototype->criteria()->limitLen(), $aPrototype->criteria()->limitFrom() ) ;
		}
		
		// set group by
		$this->setGroupBy($aSelect,$aPrototype) ;
		
		// query
		if( !$aRecordset=$aDB->query($aSelect) or !$aRecordset->rowCount() )
		{
			return false ;
		}
		$aModel->loadData($aRecordset,true) ;
		
		$aModel->clearChanged() ;

		// -----------------
		// step 2. query alonely for multiterm associated prototype 
		if($bList)
		{
			$modelIter = $aModel->childIterator() ;
		}
		else 
		{
			$modelIter = array($aModel) ;
		}
		foreach($modelIter as $aModel)
		{
			foreach($arrMultitermAssociations as $aMultitermAssoc)
			{
				$this->queryForMultitermAssoc($aDB,$aMultitermAssoc,$aModel,$aSelect) ;
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
	
	public function hasExists(IModel $aModel,Prototype $aPrototype=null,Select $aSelect=null,DB $aDB=null)
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
		
		$aCriteria = $aPrototype->statementFactory()
						->createCriteria()
						->setLimit(1) ;
		$aSelect->setCriteria($aCriteria) ;
		
		foreach($aPrototype->keys() as $sKey)
		{
			$aCriteria->where()->eq($sKey,$aModel->data($sKey)) ;
		}
		
		return $aDB->queryCount($aSelect)>0 ;
	}
	
	private function queryForMultitermAssoc(DB $aDB,Association $aMultitermAssoc,IModel $aModel,Select $aSelect)
	{
		$aToPrototype = $aMultitermAssoc->toPrototype() ;
		$aFromPrototype = $aMultitermAssoc->fromPrototype() ;
		
		// 清理一些 select 状态
		$aSelect->clearColumns() ;
		
		// 被直接关联的model
			$aFromModel = $aModel ;/*
		if( $aFromPrototype->associatedBy() )
		{
			if( !$aFromModel = $aModel->child($aFromPrototype->path(false)) )
			{
				throw new Exception("系统遇到意外的错误：查询多属关联子模型的时候，缺失被关联模型。") ;
			}
		}
		else
		{
			$aFromModel = $aModel* ;
		}*/

		// 根据上一轮查询设置条件
		if( $aMultitermAssoc->isType(Association::hasMany) )				// hasMany
		{
			$aRestraction = $this->makeResrictionForAsscotion(
					$aFromModel
					, $aMultitermAssoc->fromKeys()
					, $aToPrototype->sqlTableAlias()
					, $aMultitermAssoc->toKeys()
					, $aFromPrototype->statementFactory()
			) ;
		}
		else if( $aMultitermAssoc->isType(Association::hasAndBelongsToMany) )	// hasAndBelongsTo
		{
			$aRestraction = $this->makeResrictionForAsscotion(
					$aFromModel
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
			
		// 
		$aChildModel = $aToPrototype->createModel(true) ;
		$aModel->addChild($aChildModel,$aToPrototype->name()) ;
		$this->execute($aChildModel,$aSelect,null,true,$aDB) ;
		
		// 清理条件 和 恢复order by
		$aSelect->criteria()->where()->remove($aRestraction) ;
		if($aOriOrders){
			$aSelect->criteria()->setOrders($aOriOrders) ;
		}
	}

	private function setGroupBy(Select $aSelect,Prototype $aPrototype)
	{
		$aCriteria = $aSelect->criteria() ;
		$aCriteria->clearGroupBy() ;
		
		foreach($aPrototype->keys() as $sClmName)
		{
			$aCriteria->addGroupBy('`'.$aPrototype->sqlTableAlias().'`.`'.$sClmName.'`') ;
		}
	}
	
	private function addColumnsForOneToOne(Select $aSelect,Prototype $aPrototype,& $arrMultitermAssociations)
	{
		// add columns for pass in prototype
		$arrColumns = array_merge($aPrototype->columns() , $aPrototype->keys());
		foreach($arrColumns as $sColumnName)
		{
			$aSelect->addColumn(
				'`'.$aPrototype->sqlTableAlias().'`.`'.$sColumnName.'`'
				, $aPrototype->sqlColumnAlias($sColumnName)
			) ;
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
		$aSelect = $aPrototype->statementFactory()->createSelect() ;
		
		// 主表的名称
		$aTable = $aPrototype->createSqlTable() ;
		$aSelect->addTable($aTable) ;
		
		// criteria
		$aCriteria = clone $aPrototype->criteria() ;
		$aSelect->setCriteria( $aCriteria ) ;
		
		// 递归连接所有关联原型的 table
		self::joinTables( $aTable, $aPrototype ) ;
		
		return $aSelect ;
	}
	
	static private function joinTables(Table $aFromTable,Prototype $aForPrototype)
	{
		$sFromTableAlias = $aFromTable->alias() ;
			
		foreach( $aForPrototype->associationIterator() as $aAssoc )
		{
			$aPrototype = $aAssoc->toPrototype() ;
			$sToTableAlias = $aPrototype->sqlTableAlias() ;
			
			// 两表关联
			if( $aAssoc->isType(Association::pair) )
			{
				// create table for toPrototype
				$aTable = $aPrototype->createSqlTable() ;
		
				$aTablesJoin = self::joinTwoTables(
						$aFromTable
						, $aTable
						, $aAssoc->fromKeys()
						, $aAssoc->toKeys()
						, $aPrototype->statementFactory()
						, $aAssoc->joinType()
				) ;
				
				// 记录 TablesJoin 对象
				$aAssoc->setSqlTablesJoin($aTablesJoin) ;
			}
			
			// 三表关联
			else if( $aAssoc->isType(Association::triplet) )
			{
				// 从左表连接到中间表
				$aBridgeTable = $aPrototype->statementFactory()->createTable( $aAssoc->bridgeTableName(), $aAssoc->bridgeSqlTableAlias() ) ;
				$aTablesJoin = self::joinTwoTables(
						$aFromTable
						, $aBridgeTable
						, $aAssoc->fromKeys()
						, $aAssoc->toBridgeKeys()
						, $aPrototype->statementFactory()
						, $aAssoc->joinType()
				) ;
				
				// 记录中间表的 TablesJoin 对象
				$aAssoc->setSqlTablesJoin($aTablesJoin) ;
				
				// 从中间表连接到右表
				$aTable = $aPrototype->createSqlTable() ;
				$aBridgeTablesJoin = self::joinTwoTables(
						$aBridgeTable
						, $aTable
						, $aAssoc->fromBridgeKeys()
						, $aAssoc->toKeys()
						, $aPrototype->statementFactory()
						, $aAssoc->joinType()
				) ;
			
				// 在桥接表上加入自定义的 join on 条件
				if( $aTablesJoinOn = $aAssoc->otherBridgeTableJoinOn(false) )
				{
					$aBridgeTablesJoin->on()->add($aTablesJoinOn) ;
				}
			}
			
			else 
			{
				throw new Exception("what's this?") ;
			}
		
			// 加入自定义的 join on 条件
			if( $aOtherTablesJoinOn = $aAssoc->otherTableJoinOn(false) )
			{
				$aTablesJoin->on()->add($aOtherTablesJoinOn) ;
			}
				
			// 递归
			self::joinTables( $aTable, $aPrototype ) ;
		}
	}
	
	static private function joinTwoTables( $aFromTable,Table $aTable,array $arrFromKeys,$arrToKeys,StatementFactory $aSqlFactory,$sJoinType)
	{
		// create table join
		$aTablesJoin = $aSqlFactory->createTablesJoin($sJoinType) ;
		
		$aTablesJoinOn = $aSqlFactory->createRestriction() ;
		self::makeResrictionForForeignKey($aFromTable->alias(),$aTable->alias(),$arrFromKeys,$arrToKeys,$aTablesJoinOn) ;
		
		$aTablesJoin->addTable( $aTable, $aTablesJoinOn ) ;
		$aFromTable->addJoin($aTablesJoin) ;
		
		return $aTablesJoin ;
	}

	static private function makeResrictionForForeignKey($sFromTableName=null,$sToTableName=null,$arrFromKeys,$arrToKeys,Restriction $aRestriction)
	{
		if($sToTableName)
		{
			$sToTableName = "`{$sToTableName}`." ;
		}
		if($sFromTableName)
		{
			$sFromTableName = "`{$sFromTableName}`." ;
		}
		
		foreach ($arrFromKeys as $nIdx=>$sFromKey)
		{
			$aRestriction->eqColumn(
				"{$sFromTableName}`{$sFromKey}`"
				, "{$sToTableName}`{$arrToKeys[$nIdx]}`"
			) ;
		}
	}
}
?>
