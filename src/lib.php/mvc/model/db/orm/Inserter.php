<?php

namespace jc\mvc\model\db\orm;

use jc\mvc\model\IModelList;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;

class Inserter extends OperationStrategy
{
    public function execute(DB $aDB, IModel $aModel)
	{
		$aPrototype = $aModel->prototype() ;
		$aInsert = $aPrototype->statementFactory()->createInsert($aPrototype->tableName()) ;
		
		// 从 belongs to model 中设置外键值
		foreach($aPrototype->associations() as $aAssociation)
		{				
			if( $aAssociation->isType(Association::belongsTo) )
			{
				if( !$aAssocModel=$aModel->child($aAssociation->name()) )
				{
					continue ;
				}
				
				if( !$aAssocModel->save() )
				{
					return false ;
				}
				
				$this->setAssocModelData($aAssocModel,$aModel,$aAssociation->toKeys(),$aAssociation->fromKeys()) ;
			}
		}
		
		// -----------------------------------
		// insert 当前model
		foreach($aPrototype->columnIterator() as $sClmName)
		{
			$aInsert->setData('`'.$sClmName.'`',$aModel->data($sClmName)) ;
		}
		
		$aDB->execute( $aInsert->makeStatement() ) ;
		
		// 自增形主键
		if( $sDevicePrimaryKey=$aPrototype->devicePrimaryKey() and $aModel->data($sDevicePrimaryKey)===null )
		{
			$aModel->setData( $sDevicePrimaryKey, $aDB->lastInsertId() ) ;
		}
		
		
		$aModel->setSerialized(true) ;
		
		// -----------------------------------
		// insert 关联model
		foreach($aPrototype->associations() as $aAssociation)
		{
			$aAssocModel = $aModel->child( $aAssociation->modelProperty() ) ;
			if(!$aAssocModel)
			{
				continue ;
			}
			
			switch ( $aAssociation->type() )
			{
			case Association::hasOne :
		
				$this->setAssocModelData($aModel,$aAssocModel,$aAssociation->fromKeys(),$aAssociation->toKeys()) ;
				
				if( !$aAssocModel->save() )
				{
					return false ;
				}
				
				break ;
				
			case Association::belongsTo :
				// nothing todo ...
				break ;
				
			case Association::hasMany :
		
				$this->setAssocModelData($aModel,$aAssocModel,$aAssociation->fromKeys(),$aAssociation->toKeys()) ;
				if( !$aAssocModel->save() )
				{
					return false ;
				}
			
				break ;
				
			case Association::hasAndBelongsToMany :
				
				if( !$aAssocModel->save() )
				{
					return false ;
				}
				
				// -----------------------
				// insert bridge table
				foreach($aAssocModel->childIterator() as $aOneChildModel)
				{
					$this->buildBridge($aDB,$aAssociation,$aModel,$aOneChildModel) ;
				}
				
				break ;
			}
		}
		
		return true ;
	}

	protected function setCondition(Restriction $aRestriction,$keys,$names=null,IModel $aDataSource,$sTableName='')
	{
		$keys = array_values((array)$keys) ;
		if($names)
		{
			$names = array_values((array)$names) ;
		}
		else 
		{
			$names = $keys ;
		}
	
		if($sTableName)
		{
			$sTableName = "`{$sTableName}`." ;
		}
		
		foreach($keys as $idx=>$sKey)
		{
			$aRestriction->eq(
				"{$sTableName}`{$sKey}`"
				, $aDataSource->data($names[$idx])
			) ;
		}
	}
	
	protected function buildBridge(DB $aDB,Association $aAssociation,IModel $aFromModel,IModel $aToModel)
	{
		$aStatementFactory = $aAssociation->fromPrototype()->statementFactory() ;
		$aSelect = $aStatementFactory->createSelect($aAssociation->bridgeTableName()) ;
		$aSelect->criteria()->restrication()->add(
			$this->makeResrictionForAsscotion($aFromModel,$aAssociation->fromKeys(),null,$aAssociation->toBridgeKeys(),$aStatementFactory)
		) ;
		$aSelect->criteria()->restrication()->add(
			$this->makeResrictionForAsscotion($aToModel,$aAssociation->toKeys(),null,$aAssociation->fromBridgeKeys(),$aStatementFactory)
		) ;
		
		// 检查对应的桥接表记录是否存在
		if( !$aDB->queryCount($aSelect) )
		{
			$aInsertForBridge = $aStatementFactory->createInsert($aAssociation->bridgeTableName()) ;
			
			// from table key vale
			$this->setValue($aInsertForBridge,$aAssociation->bridgeToKeys(),$aAssociation->fromKeys(),$aFromModel) ;
			
			// to table key vale
			$this->setValue($aInsertForBridge,$aAssociation->bridgeFromKeys(),$aAssociation->toKeys(),$aToModel) ;
			
			$aDB->execute($aInsertForBridge) ;
		}
	}

	private function setAssocModelData(IModel $aModel,IModel $aChildModel,array $arrFromKeys,array $arrToKeys)
	{
		foreach($arrToKeys as $nIdx=>$sKey)
		{
			if( $aChildModel->isAggregation() )
			{
				$value = $aModel->data($arrFromKeys[$nIdx]) ;
				foreach ($aChildModel->childIterator() as $aChildChildModel)
				{
					$aChildChildModel->setData( $sKey, $value ) ;
				}
			}
			else 
			{
				$aChildModel->setData( $sKey, $aModel->data($arrFromKeys[$nIdx]) ) ;			
			}
		}
	}
}

?>
