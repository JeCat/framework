<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\Criteria;
use jc\db\IRecordSet;
use jc\mvc\model\db\Model;
use jc\db\DB;
use jc\mvc\model\db\orm\AssociationPrototype;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\lang\Exception;
use jc\db\sql\Select;

class Selecter extends OperationStrategy
{
	
	public function select( DB $aDB, IModel $aModel, Select $aSelect=null, $sTableAlias=null, array $keyValues=null, $sWhere=null, $nLimitLen=1, $nLimitFrom=0 )
	{
		$aPrototype = $aModel->prototype() ;
		if(!$sTableAlias)
		{
			$sTableAlias = $aPrototype->name() ;
		}
		
		// 对所有1对1关系，进行递归联合查询 
		// ---------------------------
		// 组建一对一联合 sql select
		if(!$aSelect)
		{
			$aSelect = new Select( $aPrototype->tableName(), $sTableAlias ) ;
		}
		$this->makeAssociationQuerySql($sTableAlias,$aPrototype,$aSelect) ;
		$aSelect->setLimit($nLimitLen,$nLimitFrom) ;

		// 设置主键查询条件
		if( $keyValues )
		{
			$aCriteria = $aSelect->criteria() ;
			foreach($keyValues as $sKey=>$sValue)
			{
				$aCriteria->add($sKey,$sValue) ;
			}
		}

		// 设置查询条件
		if( $sWhere )
		{
			$aSelect->criteria()->add($sWhere) ;
		}

		//  执行
		$aRecordSet = $aDB->query($aSelect) ;
		if( !$aRecordSet or !$aRecordSet->rowCount() )
		{
			return false ;
		}
		
		// 加载 sql
		$this->loadModel($aDB, $aModel, $aRecordSet, $sTableAlias.'.') ;
		
		$aModel->setSerialized(true) ;
			
		return true ;
	}

	
	
	protected function makeAssociationQuerySql($sTableName,ModelPrototype $aPrototype,Select $aSelect)
	{
		$aTables = $aSelect->tables() ;
		$aJoin = $aTables->sqlStatementJoin() ;

		foreach($aPrototype->columnIterator() as $sClmName)
		{
			$aSelect->addColumn("`{$sTableName}`.`{$sClmName}`","{$sTableName}.{$sClmName}") ;
		}
		
		// 处理关联表
		foreach($aPrototype->associations() as $aAssoPrototype)
		{
			// 联合sql查询
			if( in_array($aAssoPrototype->type(), array(
					AssociationPrototype::hasOne
					, AssociationPrototype::belongsTo
			)) )
			{
				
				$sAssoTableAlias = $sTableName.'.'.$aAssoPrototype->modelProperty() ;
				$aTables->join( $aAssoPrototype->toPrototype()->tableName(), null, $sAssoTableAlias ) ;
				
				echo $sAssoTableAlias, "<br/>" ;
				$this->setAssociationCriteria($aJoin->criteria(),$sTableName,$sAssoTableAlias, $aAssoPrototype->fromKeys(), $aAssoPrototype->toKeys() ) ;
				
				// 递归关联
				$this->makeAssociationQuerySql($sAssoTableAlias,$aAssoPrototype->toPrototype(),$aSelect) ;
			}
		}
	}
	
	protected function loadModel( DB $aDB, IModel $aModel, IRecordSet $aRecordSet, $sClmPrefix, $nIdx=0 )
	{
		$aPrototype = $aModel->prototype() ;
		
		if(!$sClmPrefix)
		{
			$sClmPrefix = $aPrototype->name().'.' ;
		}
		
		// load 自己
		$aModel->loadData($aRecordSet,$nIdx,$sClmPrefix,true) ;
		
		// load children
		if( $aModel->isAggregation() )
		{
			$models = $aModel->childIterator() ;
		}
		else 
		{
			$models = array($aModel) ;
		}
		
		foreach($models as $nRowIdx=>$aModel)
		{			
			////////////////////////////////////////////////////////////
			// 加载关联model
			foreach($aPrototype->associations() as $aAssoPrototype)
			{
				// 关联模型原型
				$aToPrototype = $aAssoPrototype->toPrototype() ;
				$sChildModelName = $aAssoPrototype->modelProperty() ;
				
				// 通过关联原型 创建子模型
				$aChildModel = $aModel->child( $sChildModelName ) ;
				if(!$aChildModel)
				{
					$aChildModel = $aToPrototype->createModel() ;
					$aModel->addChild($aChildModel,$sChildModelName) ;
				}
							
				// -------------------------------------------------------------
				// 一对一关联（从传入的recordset里加载数据）
				if( in_array($aAssoPrototype->type(), array(
						AssociationPrototype::hasOne
						, AssociationPrototype::belongsTo
				)) )
				{
					$this->loadModel($aDB,$aChildModel,$aRecordSet,$sClmPrefix.$sChildModelName.'.',$nRowIdx) ;
					$aChildModel->setSerialized(true) ;
				}
				
				// -------------------------------------------------------------
				// 多项关系（独立查询）
				else 
				{
						
					// 一对多关联
					if( $aAssoPrototype->type()==AssociationPrototype::hasMany )
					{
						$arrFromKeys = $aAssoPrototype->fromKeys() ;
						$arrKeyValues = array() ;
						foreach($aAssoPrototype->toKeys() as $nIdx=>$sKey)
						{
							$arrKeyValues["`{$sChildModelName}`.`{$sKey}`"] = $aModel->data($arrFromKeys[$nIdx]) ;
						}
						
						// 加载 child 类
						$this->select($aDB, $aChildModel, null, null, $arrKeyValues, null, 30 ) ;
						$aChildModel->setSerialized(true) ;
					}
					
					// 多对多关联
					else if( $aAssoPrototype->type()==AssociationPrototype::hasAndBelongsToMany )
					{
						$aSelect = new Select( $aToPrototype->tableName(), $sChildModelName ) ;
						
						// bridge 表到 to表的关联条件
						$sBridgeTable = $aAssoPrototype->bridgeTableName() ;
						$aSelect->tables()->join($sBridgeTable) ;
						$this->setAssociationCriteria(
								$aSelect->tables()->sqlStatementJoin()->criteria()
								, $sBridgeTable, $sChildModelName
								, $aAssoPrototype->bridgeFromKeys(), $aAssoPrototype->toKeys()
						) ;
						
						// from 表到 bridge 表的关联条件
						$arrFromKeys = $aAssoPrototype->fromKeys() ;
						$arrKeyValues = array() ;
						foreach($aAssoPrototype->bridgeToKeys() as $nIdx=>$sKey)
						{
							$arrKeyValues["`{$sBridgeTable}`.`{$sKey}`"] = $aModel->data($arrFromKeys[$nIdx]) ;
						}
						
						// 加载 child 类
						$this->select($aDB, $aChildModel, $aSelect, $sChildModelName, $arrKeyValues, null, 30 ) ;
						$aChildModel->setSerialized(true) ;
					}
				}
			}
		}
	}

}

?>