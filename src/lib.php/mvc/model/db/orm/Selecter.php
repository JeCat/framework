<?php

namespace jc\mvc\model\db\orm;


use jc\mvc\model\db\ModelList;
use jc\db\sql\Table;
use jc\db\sql\Select;
use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;
use jc\lang\Exception;

class Selecter extends Object
{
	public function execute(DB $aDB, IModel $aModel,Select $aSelect=null)
	{
		if( !$aPrototype = $aModel->prototype() )
		{
			throw new Exception("传入了无效的 IModel 对象，\$aModel的 prototype() 方法返回null") ;	
		}
		
		// -----------------
		// step 1. 组装用于查询的 Select 对象
		if(!$aSelect)
		{
			$aSelect = $this->buildSelect($aPrototype) ;
		}
		
		
		// -----------------
		// step 2. query for all one to one association tables 
		$arrMultitermAssociations = array() ;
		$this->addColumnsForOneToOne($aSelect,$aPrototype,$arrMultitermAssociations) ;
		
		// set limit
		if( !($aModel instanceof ModelList) )
		{
			$aSelect->criteria()->setLimitLen(1) ;
		}
		// set group by
		$this->setGroupBy($aSelect,$aPrototype) ;
		
		// query
		if( !$aRecordset = $aDB->query($aSelect) )
		{
			return ;
		}
		$aModel->loadData($aRecordset) ;
		
		
		// -----------------
		// step 2. query alonely for multiterm associated prototype 
		foreach($arrMultitermAssociations as $aMultitermAssoc)
		{
			$this->queryForMultitermAssoc($aDB,$aMultitermAssoc,$aModel,$aSelect) ;
		}
	}
	
	private function queryForMultitermAssoc(DB $aDB,Association $aMultitermAssoc,IModel $aModel,Select $aSelect)
	{	
		$aPrototype = $aMultitermAssoc->toProperty() ;
		
		// 清理一些 select 状态
		$aSelect->clearColumns() ;

		// 根据上一轮查询设置条件
		if( $aMultitermAssoc->isType(Association::hasMany) )
		{
			$this->makeResrictionForAsscotion($aModel,$aPrototype->alias()) ;
		}
		else if( $aMultitermAssoc->isType(Association::hasAndBelongsTo) )
		{
			
		}
		else
		{
			throw new Exception("what's this?") ;
		}
		
		
		// 
		$aChildModel = $aPrototype->createModel(true) ;
		$this->execute($aDB,$aChildModel,$aSelect) ;
	}
	
	private function buildSelect(Prototype $aPrototype)
	{
		$aSqlFactory = StatementFactory::singleton() ;
		$aSqlFactory instanceof StatementFactory ;
		$aSelect = $aSqlFactory->createSelect() ;
		
		// 主表的名称
		$aTable = $aSqlFactory->createTable( $aPrototype->tableName(), $aPrototype->name() ) ;
		$aSelect->addTable($aTable) ;
		
		// criteria
		$aCriteria = clone $aPrototype->criteria() ;
		$aSelect->setCriteria( $aCriteria ) ;
		
		// 递归连接所有关联原型的 table
		$this->joinTables( $aTable, $aPrototype, $aSqlFactory ) ;
		
		return $aSelect ;
	}
	
	private function joinTables(Table $aFromTable,Prototype $aForPrototype, StatementFactory $aSqlFactory)
	{
		$sFromTableAlias = $aFromTable->alias() ;
			
		foreach( $aForPrototype->associationIterator() as $aAssoc )
		{
			$aPrototype = $aAssoc->toPrototype() ;
			$sToTableAlias = $sFromTableAlias.'.'.$aPrototype->name() ;
			
			// 两表关联
			if( $aAssoc->isType(Association::pair) )
			{
				$aTable = $this->joinTwoTables(
						$aFromTable
						, $aPrototype->tableName()
						, $aPrototype->sqlTableAlias()
						, $aAssoc->fromKeys()
						, $aAssoc->toKeys()
						, $aSqlFactory
				) ;
			}
			
			// 三表关联
			else if( $aAssoc->isType(Association::triplet) )
			{
				// 从左表连接到中间表
				$sBridgeTableAlias = $sToTableAlias.'#bridge' ;
				$aBridgeTable = $this->joinTwoTables(
						$aFromTable
						, $aAssoc->bridgeTableName()
						, $sBridgeTableAlias
						, $aAssoc->fromKeys()
						, $aAssoc->toBridgeKeys()
						, $aSqlFactory
				) ;
				
				// 从中间表连接到右表
				$aTable = $this->joinTwoTables(
						$aBridgeTable
						, $aPrototype->tableName()
						, $sToTableAlias
						, $aAssoc->fromBridgeKeys()
						, $aAssoc->toKeys()
						, $aSqlFactory
				) ;
			}
			
			else 
			{
				throw new Exception("what's this?") ;
			}

			// 递归
			$this->joinTables( $aTable, $aPrototype, $aSqlFactory ) ;
		}
	}
	
	private function joinTwoTables(Table $aFromTable,$sToTable,$sToTableAlias,array $arrFromKeys,$arrToKeys,StatementFactory $aSqlFactory)
	{
		// create table
		$aTable = $aSqlFactory->createTable( $sToTable, $sToTableAlias ) ;
		
		// create table join
		$aTablesJoin = $aSqlFactory->createTablesJoin() ;
		$aTablesJoin->addTable(
				$aTable, $this->makeResrictionForForeignKey($aFromTable->alias(),$sToTableAlias,$arrFromKeys,$arrToKeys,$aSqlFactory)
		) ;
		$aFromTable->addJoin($aTablesJoin) ;
		
		return $aTable ;
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
		foreach($aPrototype->columns() as $sColumnName)
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

	private function makeResrictionForAsscotion(Model $aModel,$sToTableName,array $arrFromKeys,array $arrToKeys, StatementFactory $aSqlFactory)
	{
		$aRestriction = $aSqlFactory->createRestriction() ;
		
		foreach($arrFromKeys as $nIdx=>$sFromKey)
		{
			$aRestriction->eq( "`{$sToTableName}`.`{$arrToKeys[$nIdx]}`", $aModel->data($sFromKey) ) ;
		}
		
		return $aRestriction ;
	}

	private function makeResrictionForForeignKey($sFromTableName,$sToTableName,$arrFromKeys,$arrToKeys, StatementFactory $aSqlFactory)
	{
		$aRestriction = $aSqlFactory->createRestriction() ;
		
		foreach ($arrFromKeys as $nIdx=>$sFromKey)
		{
			$aRestriction->eqColumn(
				"`{$sFromTableName}`.`{$sFromKey}`"
				, "`{$sToTableName}`.`{$arrToKeys[$nIdx]}`"
			) ;
		}
		
		return $aRestriction ;
	}
}
?>
