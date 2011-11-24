<?php
namespace jc\mvc\model\db\orm;

use jc\db\sql\Restriction;

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
		
		$aModel->setSerialized(true) ;
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
		
		$aCriteria = $aSelect->criteria() ;
		$aCriteria->setLimit(-1) ;
		$aCriteria->clearGroupBy() ;
		foreach($aPrototype->keys() as $sClmName)
		{
			$aCriteria->addGroupBy('`'.$aPrototype->sqlTableAlias().'`.`'.$sClmName.'`') ;
		}
		
		return $aDB->queryCount($aSelect) ;
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
		
		$aSelect->criteria()->where()->add($aRestraction) ;
			
		// 
		$aChildModel = $aToPrototype->createModel(true) ;
		$aModel->addChild($aChildModel,$aToPrototype->name()) ;
		$this->execute($aChildModel,$aSelect,null,true,$aDB) ;
		
		// 清理条件
		$aSelect->criteria()->where()->remove($aRestraction) ;
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
	 * @return jc\db\sql\Select
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
	
	static private function joinTwoTables( $aFromTable,Table $aTable,array $arrFromKeys,$arrToKeys,StatementFactory $aSqlFactory)
	{
		// create table join
		$aTablesJoin = $aSqlFactory->createTablesJoin() ;
		
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