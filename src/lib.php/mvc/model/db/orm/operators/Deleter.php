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
			$aDelete = new Delete($aPrototype->tableName(),$aPrototype->name()) ;
			
			// -----------------
			// 联合表 删除
			$this->makeAssociation($aDelete,$aPrototype,array(AssociationPrototype::hasOne)) ;
			
			// 主键条件
			$this->setCondition($aDelete->criteria(),$aPrototype->primaryKeys(),null,$aModel,$aPrototype->name()) ;
			
			// -----------------
			foreach($aPrototype->associations() as $aAssoPrototype)
			{
				// 多属关系
				if( in_array($aAssoPrototype->type(),array(AssociationPrototype::hasMany,AssociationPrototype::hasAndBelongsMany)) )
				{
					$aChildModel = $aModel->child($aAssoPrototype->modelProperty()) ;
				
					// 多对多，删除桥接表记录
					if( $aAssoPrototype->type()==AssociationPrototype::hasAndBelongsMany )
					{
						$sBridgeTable = $aAssoPrototype->bridgeTableName() ;
						$aDeleteForBridge = new Delete($sBridgeTable) ;
						
						// from表 条件
						$this->setCondition($aDeleteForBridge->criteria(),$aPrototype->fromKeys(),$aPrototype->bridgeToKeys(),$aModel,$sBridgeTable) ;
						
						// to表 条件
						$this->setCondition($aDeleteForBridge->criteria(),$aPrototype->bridgeFromKeys(),$aPrototype->toKeys(),$aModel,$sBridgeTable) ;
						
						if( $aDB->execute($aDelete->makeStatement())===false )
						{
							return false ;
						}
					}
					
					// 删除子model
					if( !$aChildModel->delete() )
					{
						return false ;
					}
				}
			}
			
			return $aDB->execute($aDelete->makeStatement())!==false ;
		}
	}
}

?>