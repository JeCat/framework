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
	public function execute(DB $aDB, IModel $aModel)
	{
		$aPrototype = $aModel->prototype() ;
		
		// -----------------
		// step 1. 组装用于查询的 Select 对象
		$aSelect = $this->buildSelect($aPrototype) ;
		
		
		// -----------------
		// step 2. query for all one to one association tables 
		$arrMultitermAssociations = array() ;
		$this->addColumnsForOneToOne($aSelect,$aPrototype,$aPrototype->name(),$arrMultitermAssociations) ;
		
		if( !($aModel instanceof ModelList) )
		{
			$aSelect->criteria()->setLimitLen(1) ;
		}
		if( !$aRecordset = $aDB->query($aSelect) )
		{
			return ;
		}
		$aModel->loadData($aRecordset) ;
		
		
		// -----------------
		// step 2. query alonely for multiterm associated prototype 
		foreach($arrMultitermAssociations as $aMultitermAssoc)
		{
		}
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
	}
	
	private function joinTables(Table $aFromTable,Prototype $aForPrototype, StatementFactory $aSqlFactory)
	{
		foreach( $aForPrototype->associationIterator() as $aAssoc )
		{
			$aPrototype = $aAssoc->toPrototype() ;
			
			// create table
			$aTable = $aSqlFactory->createTable(
				$aPrototype->tableName()
				, $aFromTable->alias().'.'.$aPrototype->alias()
			) ;
						
			// create table join
			$aTablesJoin = $aSqlFactory->createTablesJoin() ;
			$aTablesJoin->addTable($aTable) ;
			
			$aFromTable->addJoin(
				$aTablesJoin
				, $this->makeResrictionForForeignKey($aFromTable->alias(),$aTable->alias(),$aAssoc,$aSqlFactory)
			) ;

			// 递归
			$this->joinTables( $aTable, $aPrototype ) ;
		}
	}
	
	private function addColumnsForOneToOne(Select $aSelect,Prototype $aPrototype,$sPrototypeTableAlias,& $arrMultitermAssociations)
	{		
		// add columns for pass in prototype 
		foreach($aPrototype->columns() as $sColumnName)
		{
			$aSelect->addColumn($sColumnName,"{$sPrototypeTableAlias}.{$sColumnName}") ;
		}

		// add columns for one to one associated prototypes
		foreach( $aPrototype->associationIterator() as $aAssoc )
		{
			if( $aAssoc->isOneToOne() )
			{
				$aToPrototype = $aAssoc->toPrototype() ;
				
				// add columns recursively
				$this->addColumnsForOneToOne( $aSelect, $aToPrototype, $sPrototypeTableAlias.'.'.$aToPrototype->alias() ) ;
			}
			
			else
			{
				$arrMultitermAssociations[] = $aAssoc ;
			}
		}
	}
		
	private function makeResrictionForForeignKey(Table $sFromTableName,Table $sToTableName,Association $aAssociation, StatementFactory $aSqlFactory)
	{
		$aRestriction = $aSqlFactory->createRestriction() ;
		$arrToKeys = $aAssociation->toKeys() ;
		
		foreach ($aAssociation->fromKeys() as $nIdx=>$sFromKey)
		{
			$aRestriction->eqColumn(
				"{$sFromTableName}.{$sFromKey}"
				, "{$sToTableName}.{$arrToKeys[$nIdx]}"
			) ;
		}
		
		return $aRestriction ;
	}

}
?>
