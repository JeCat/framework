<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\DB;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\Association;
use jc\db\sql\Insert;

class Inserter extends OperationStrategy
{
	public function insert(DB $aDB, IModel $aModel) 
	{
		$aPrototype = $aModel->prototype() ;
		$aInsert = new Insert($aPrototype->tableName()) ;
		
		// 从 belongs to model 中设置外键值
		foreach($aPrototype->associations() as $aAssociation)
		{				
			if( $aAssociation->type()==Association::belongsTo )
			{
				$aAssocModel = $aModel->child( $aAssociation->modelProperty() ) ;
				if(!$aAssocModel)
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