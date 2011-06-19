<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\DB;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\AssociationPrototype;
use jc\db\sql\Insert;

class Inserter extends OperationStrategy
{
	public function insert(DB $aDB, IModel $aModel) 
	{
		if( $aModel->isAggregarion() )
		{
			foreach($aModel->childIterator() as $aChildModel)
			{
				if( !$this->insert($aDB, $aChildModel) )
				{
					return false ;
				}
			}
			
			return true ;
		}
		
		else 
		{
			$aPrototype = $aModel->prototype() ;
			$aInsert = new Insert($aPrototype->tableName()) ;
			
			// 从 belongs to model 中设置外键值
			foreach($aPrototype->associations() as $aAssoPrototype)
			{				
				if( $aAssoPrototype->type()==AssociationPrototype::belongsTo )
				{
					$aAssocModel = $aModel->child( $aAssoPrototype->modelProperty() ) ;
					if(!$aAssocModel)
					{
						continue ;
					}
					
					$aAssocModel->save() ;
					
					$this->setAssocModelData($aAssocModel,$aModel,$aAssoPrototype) ;
				}
			}
			
			// -----------------------------------
			// insert 当前model
			foreach($aPrototype->columnIterator() as $sClmName)
			{
				$aInsert->setData($sClmName,$aModel->data($sClmName)) ;
			}
			
			$aDB->execute( $aInsert->makeStatement() ) ;
			
			// 自增形主键
			if( $aModel->data( $aPrototype->devicePrimaryKey() )===null )
			{
				$aModel->setData(
					$aPrototype->devicePrimaryKey()
					, $aDB->lastInsertId()
				) ;
			}
			
			$aModel->setSerialized(true) ;
			
			// -----------------------------------
			// insert 关联model
			foreach($aPrototype->associations() as $aAssoPrototype)
			{
				$aAssocModel = $aModel->child( $aAssoPrototype->modelProperty() ) ;
				if(!$aAssocModel)
				{
					continue ;
				}
				
				switch ( $aAssoPrototype->type() )
				{
				case AssociationPrototype::hasOne :
				case AssociationPrototype::belongsTo :
			
					$this->setAssocModelData($aModel,$aAssocModel,$aAssoPrototype) ;
					if( !$aAssocModel->save() )
					{
						return false ;
					}
					
					break ;
					
				case AssociationPrototype::hasMany :
			
					$this->setAssocModelData($aModel,$aAssocModel,$aAssoPrototype) ;
					if( !$aAssocModel->save() )
					{
						return false ;
					}
				
					break ;
					
				case AssociationPrototype::hasAndBelongsToMany :
					
					if( !$aAssocModel->save() )
					{
						return false ;
					}
					
					// -----------------------
					// insert bridge table
					foreach($aAssocModel->childIterator() as $aOneChildModel)
					{
						$aInsertForBridge = new Insert( $aAssoPrototype->bridgeTableName() ) ;
						
						// from table key vale
						$this->setValue($aInsertForBridge,$aAssoPrototype->bridgeToKeys(),$aAssoPrototype->fromKeys(),$aModel) ;
						
						// to table key vale
						$this->setValue($aInsertForBridge,$aAssoPrototype->bridgeFromKeys(),$aAssoPrototype->toKeys(),$aOneChildModel) ;
						
						$aDB->execute($aInsertForBridge) ;
					}
					
					break ;
				}
			}
		}
		
		return true ;
	}
	
	private function setAssocModelData(IModel $aModel,IModel $aChildModel,AssociationPrototype $aAssoPrototype)
	{
		$arrFromKeys = $aAssoPrototype->fromKeys() ;
		foreach($aAssoPrototype->toKeys() as $nIdx=>$sKey)
		{
			$aChildModel->setData( $sKey, $aModel->data($arrFromKeys[$nIdx]) ) ;
		}
	}
}

?>