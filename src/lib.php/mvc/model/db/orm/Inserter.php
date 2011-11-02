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
