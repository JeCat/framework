<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\mvc\model\db\orm\AssociationPrototype;
use jc\db\sql\Delete;
use jc\mvc\model\db\IModel;
use jc\db\DB;

class Deleter extends OperationStrategy
{
	public function delete(DB $aDB, IModel $aModel)
	{
		if( $aModel->isAggregarion() )
		{
			foreach($aModel->childIterator() as $aChildModel)
			{
				if( !$this->delete($aDB, $aChildModel) )
				{
					return false ;
				}
			}
			
			return true ;
		}
		
		else 
		{
			$aPrototype = $aModel->prototype() ;
			$aDelete = new Delete($aPrototype->tableName()) ;
			
			// -----------------
			// 联合表 删除
			$this->makeAssociation($aDelete,$aPrototype,array(AssociationPrototype::hasOne)) ;
			
			// 主键条件
			$this->setCondition($aDelete->criteria(),$aPrototype->primaryKeys(),null,$aModel,$aPrototype->tableName()) ;
			
			// -----------------
			foreach($aPrototype->associations() as $aAssoPrototype)
			{
				$aChildModel = $aModel->child($aAssoPrototype->modelProperty()) ;
					
				// 多对多，删除桥接表记录
				if( $aAssoPrototype->type()==AssociationPrototype::hasAndBelongsToMany )
				{
					$sBridgeTable = $aAssoPrototype->bridgeTableName() ;
					$aDeleteForBridge = new Delete($sBridgeTable) ;
					
					// from表 条件
					$this->setCondition($aDeleteForBridge->criteria(),$aAssoPrototype->fromKeys(),$aAssoPrototype->bridgeToKeys(),$aModel,$sBridgeTable) ;
					
					$aDB->execute($aDeleteForBridge->makeStatement()) ;
				}
				
				// 删除 hasOne, hasMany 关联模型 
				if( in_array($aAssoPrototype->type(),array(AssociationPrototype::hasOne,AssociationPrototype::hasMany)) )
				{
					// 删除子model
					if( !$aChildModel->delete() )
					{
						return false ;
					}
					$aChildModel->setSerialized(false) ;
				}
			}
			
			$aDB->execute($aDelete->makeStatement()) ;
			
			$aModel->setSerialized(false) ;
			
			return true ;
		}
	}
}

?>