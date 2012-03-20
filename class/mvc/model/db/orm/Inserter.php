<?php

namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\Model ;
use org\jecat\framework\db\sql\StatementFactory ;

class Inserter extends OperationStrategy
{
    public function execute(DB $aDB, Model $aModel)
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
		$bTableLocked = false ;
		$sTableName = $aPrototype->tableName() ;
		$e = null ;
		try{
			// 检查/自动生成 主键值
			if( $arrKey = $aPrototype->keys() )
			{
				foreach($arrKey as $sKeyName)
				{
					if( $aModel->data($sKeyName)===null )
					{
						$aClmRef = $aPrototype->columnReflecter($sKeyName) ;
						if( !$aClmRef->isAutoIncrement() )
						{
							// 锁表
							if(!$bTableLocked)
							{
								$aDB->execute("LOCK TABLES `{$sTableName}` WRITE ;") ;
								$bTableLocked = true ;
							}
							
							// 生成并设置主键值
							$keyValue = AutoPrimaryGenerator::singleton()->generate($aClmRef,$sTableName,$aDB) ;
							$aModel->setData($sKeyName,$keyValue) ;
						}
					}
					
				}
			}
			
			// 设置数据
			foreach($aModel->dataNameIterator() as $sClmName)
			{
				$value = $aModel->data($sClmName) ;
				if($value!==null)
				{
					$aInsert->setData('`'.$sClmName.'`',$value) ;
				}
			}
			
			// 执行 insert
			$aDB->execute( $aInsert ) ;
			
		}catch (\Exception $e)
		{}
		
		// 解锁后再抛出异常
		if($bTableLocked)
		{
			$aDB->execute("UNLOCK TABLES ;") ;
		}
		if($e)
		{
			throw $e ;
		}
		
		// 自增形主键
		if( $sDevicePrimaryKey=$aPrototype->devicePrimaryKey() and $aModel->data($sDevicePrimaryKey)===null )
		{
			$aModel->setData( $sDevicePrimaryKey, $aDB->lastInsertId(), false ) ;
		}
		
		// -----------------------------------
		// insert 关联model
		foreach($aPrototype->associations() as $aAssociation)
		{
			$aAssocModel = $aModel->child( $aAssociation->name() ) ;
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
		
	protected function buildBridge(DB $aDB,Association $aAssociation,IModel $aFromModel,IModel $aToModel)
	{
		$aFromPrototype = $aAssociation->fromPrototype() ;
		$aStatementFactory = $aFromPrototype->statementFactory() ;
		$aSelect = $aStatementFactory->createSelect($aAssociation->bridgeTableName()) ;
		$aSelect->criteria()->where()->add(
			$this->makeResrictionForAsscotion($aFromModel,$aFromPrototype->path(),$aAssociation->fromKeys(),null,$aAssociation->toBridgeKeys(),$aStatementFactory)
		) ;
		$aSelect->criteria()->where()->add(
			$this->makeResrictionForAsscotion($aToModel,$aAssociation->toPrototype()->path(),$aAssociation->toKeys(),null,$aAssociation->fromBridgeKeys(),$aStatementFactory)
		) ;
		
		// 检查对应的桥接表记录是否存在
		if( !$aDB->queryCount($aSelect) )
		{
			$aInsertForBridge = $aStatementFactory->createInsert($aAssociation->bridgeTableName()) ;
			
			// from table key vale
			$this->setValue($aInsertForBridge,$aAssociation->toBridgeKeys(),$aAssociation->fromKeys(),$aFromModel) ;
			
			// to table key vale
			$this->setValue($aInsertForBridge,$aAssociation->fromBridgeKeys(),$aAssociation->toKeys(),$aToModel) ;
			
			$aDB->execute($aInsertForBridge) ;
		}
	}

	private function setAssocModelData(IModel $aModel,IModel $aChildModel,array $arrFromKeys,array $arrToKeys)
	{
		foreach($arrToKeys as $nIdx=>$sKey)
		{
			if( $aChildModel->isList() )
			{
				$value = $aModel->data($arrFromKeys[$nIdx]) ;
				foreach ($aChildModel->childIterator() as $aChildChildModel)
				{
					$aChildChildModel->setData( $sKey, $value ,false) ;
				}
			}
			else 
			{
				$aChildModel->setData( $sKey, $aModel->data($arrFromKeys[$nIdx]) ,false) ;
			}
		}
	}
}

?>
