<?php
namespace jc\mvc\model\db\orm;

use jc\mvc\model\db\Model;
use jc\mvc\model\IModelList;
use jc\db\sql\Table;
use jc\db\sql\Select;
use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;
use jc\lang\Exception;
use jc\db\sql\Criteria;

class Selecter extends OperationStrategy
{
	public function execute(DB $aDB, IModel $aModel,Select $aSelect=null,Criteria $aCriteria=null)
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
		
		if($aCriteria){
			$aSelect ->setCriteria($aCriteria);
		}
		
		// -----------------
		// step 2. query for all one to one association tables 
		$arrMultitermAssociations = array() ;
		$this->addColumnsForOneToOne($aSelect,$aPrototype,$arrMultitermAssociations) ;
		
		// set limit
		if( !($aModel instanceof IModelList) )
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
		if( !$aRecordset = $aDB->query($aSelect) )
		{
			return ;
		}
		$aModel->loadData($aRecordset) ;
		
		
		// -----------------
		// step 2. query alonely for multiterm associated prototype 
		if($aModel instanceof IModelList)
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
	}
	
	private function queryForMultitermAssoc(DB $aDB,Association $aMultitermAssoc,IModel $aModel,Select $aSelect)
	{
		$aToPrototype = $aMultitermAssoc->toPrototype() ;
		$aFromPrototype = $aMultitermAssoc->fromPrototype() ;
		
		// 清理一些 select 状态
		$aSelect->clearColumns() ;
		
		// 被直接关联的model
		if( $aFromPrototype->associatedBy() )
		{
			$aFromModel = $aModel->child($aFromPrototype->path(false)) ;
		}
		else
		{
			$aFromModel = $aModel ;
		}

		// 根据上一轮查询设置条件
		if( $aMultitermAssoc->isType(Association::hasMany) )				// hasMany
		{
			$aRestraction = $this->makeResrictionForAsscotion(
					$aFromModel
					, $aToPrototype->fromKeys()
					, $aToPrototype->full(false)
					, $aMultitermAssoc->toKeys()
					, $aFromPrototype->statementFactory()
			) ;
		}
		else if( $aMultitermAssoc->isType(Association::hasAndBelongsTo) )	// hasAndBelongsTo
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
			throw new Exception("what's this?") ;
		}
	
		if( !$aTablesJoin=$aMultitermAssoc->sqlTablesJoin() )
		{
			throw new Exception("关联对象没有TablesJoin对象") ;
		}
		
		$aSelect->criteria()->restriction()->add($aRestraction) ;
			
		// 
		$aChildModel = $aToPrototype->createModel(true) ;
		$aModel->addChild($aChildModel,$aToPrototype->name()) ;
		$this->execute($aDB,$aChildModel,$aSelect) ;
		
		// 清理条件
		$aSelect->criteria()->restriction()->remove($aRestraction) ;
	}
	
	private function buildSelect(Prototype $aPrototype)
	{
		$aSqlFactory = $aPrototype->statementFactory() ;
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
			$sToTableAlias = $aPrototype->sqlTableAlias() ;
			
			// 两表关联
			if( $aAssoc->isType(Association::pair) )
			{
				// create table for toPrototype
				$aTable = $aSqlFactory->createTable( $aPrototype->tableName(), $sToTableAlias ) ;
		
				 $aTablesJoin = $this->joinTwoTables(
						$aFromTable
						, $aTable
						, $aAssoc->fromKeys()
						, $aAssoc->toKeys()
						, $aSqlFactory
				) ;
				
				// 记录 TablesJoin 对象
				$aAssoc->setSqlTablesJoin($aTablesJoin) ;
			}
			
			// 三表关联
			else if( $aAssoc->isType(Association::triplet) )
			{
				// 从左表连接到中间表
				$aBridgeTable = $aSqlFactory->createTable( $aAssoc->bridgeTableName(), $aAssoc->bridgeSqlTableAlias() ) ;
				$aTablesJoin = $this->joinTwoTables(
						$aFromTable
						, $aBridgeTable
						, $aAssoc->fromKeys()
						, $aAssoc->toBridgeKeys()
						, $aSqlFactory
				) ;
				
				// 记录中间表的 TablesJoin 对象
				$aAssoc->setSqlTablesJoin($aTablesJoin) ;
				
				// 从中间表连接到右表
				$aTable = $aSqlFactory->createTable( $aPrototype->tableName(), $sToTableAlias ) ;
				$this->joinTwoTables(
						$aBridgeTable
						, $aTable
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
	
	private function joinTwoTables( $aFromTable,Table $aTable,array $arrFromKeys,$arrToKeys,StatementFactory $aSqlFactory)
	{
		// create table join
		$aTablesJoin = $aSqlFactory->createTablesJoin() ;
		$aTablesJoin->addTable(
				$aTable, $this->makeResrictionForForeignKey($aFromTable->alias(),$aTable->alias(),$arrFromKeys,$arrToKeys,$aSqlFactory)
		) ;
		$aFromTable->addJoin($aTablesJoin) ;
		
		return $aTablesJoin ;
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

}
?>
