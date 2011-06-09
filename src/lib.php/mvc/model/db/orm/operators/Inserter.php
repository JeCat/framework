<?php
namespace jc\mvc\model\db\orm\operators ;

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
			
			// -----------------------------------
			// insert 当前model
			foreach($aPrototype->columnIterator() as $sClmName)
			{
				$aInsert->setData($sClmName,$aModel->data($sClmName)) ;
			}
			
			if( $aDB->execute( $aInsert->makeStatement() )===false )
			{
				return false ;
			}
			
			// 自增形主键
			if( $aModel->data( $aPrototype->devicePrimaryKey() )===null )
			{
				$aModel->setData(
					$aPrototype->devicePrimaryKey()
					, $aDB->lastInsertId()
				) ;
			}
			
			
			// -----------------------------------
			// insert 关联model
			foreach($aPrototype->associations() as $aAssoPrototype)
			{
				// has one 
				if($aAssoPrototype->type()==AssociationPrototype::hasOne)
				{
					$aChildModel = $aModel->child( $aAssoPrototype->propertyName() ) ;
					if(!$aChildModel)
					{
						continue ;
					}
		
					if( !$this->insertDirectAssocModel($aModel,$aChildModel,$aAssoPrototype) )
					{
						return false ;
					}
				}
				
				// has many
				else if($aAssoPrototype->type()==AssociationPrototype::hasMany)
				{
					foreach($aModel->childIterator() as $aChildModel)
					{
						if( !$this->insertDirectAssocModel($aModel,$aChildModel,$aAssoPrototype) )
						{
							return false ;
						}
					}
				}
				
				// has and belongs many
				else if($aAssoPrototype->type()==AssociationPrototype::hasAndBelongsMany)
				{
					if( !$aChildModel->save() )
					{
						return false ;
					}
					
					// -----------------------
					// insert bridge table
					foreach($aChildModel->childIterator() as $aOneChildModel)
					{
						$aInsertForBridge = new Insert( $aAssoPrototype->bridgeTableName() ) ;
						
						// from table key vale
						$this->setValue($aInsertForBridge,$aAssoPrototype->bridgeToKeys(),$aAssoPrototype->fromKeys(),$aModel) ;
						
						// to table key vale
						$this->setValue($aInsertForBridge,$aAssoPrototype->bridgeFromKeys(),$aAssoPrototype->toKeys(),$aOneChildModel) ;
						
						if( $aDB->execute($aInsertForBridge)===false )
						{
							return false ;
						}
					}
				}
			}
		}
		
		return true ;
	} 
	
	private public function insertDirectAssocModel(IModel $aModel,IModel $aChildModel,AssociationPrototype $aAssoPrototype)
	{
		$arrFromKeys = $aAssoPrototype->fromKeys() ;
		foreach($aAssoPrototype->toKeys() as $nIdx=>$sKey)
		{
			$aChildModel->setData( $sKey, $aModel->data($arrFromKeys[$nIdx]) ) ;
		}
		
		return $aChildModel->save() ;
	}
}

?>