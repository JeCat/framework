<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/

namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\db\sql\Insert;
use org\jecat\framework\db\sql\Select;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\Model;

class Inserter extends OperationStrategy
{
    public function execute(DB $aDB, Model $aModel)
	{
		$aPrototype = $aModel->prototype() ;
		$aInsert = new Insert($aPrototype->tableName()) ;
		
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
					$aInsert->setData($sClmName,$value) ;
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
		
	protected function buildBridge(DB $aDB,Association $aAssociation,Model $aFromModel,Model $aToModel)
	{
		$aFromPrototype = $aAssociation->fromPrototype() ;
		$aSelect = new Select($aAssociation->bridgeTableName()) ;
		
		$aRestraction = $aSelect->criteria()->where() ;
		$this->makeResrictionForAsscotion($aRestraction,$aFromPrototype->path(),$aAssociation->fromKeys(),null,$aAssociation->toBridgeKeys()) ;
		$this->makeResrictionForAsscotion($aRestraction,$aAssociation->toPrototype()->path(),$aAssociation->toKeys(),null,$aAssociation->fromBridgeKeys()) ;
		
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

	private function setAssocModelData(Model $aModel,Model $aChildModel,array $arrFromKeys,array $arrToKeys)
	{
		foreach($arrToKeys as $nIdx=>$sKey)
		{
			if( $aChildModel->isList() )
			{
				$value = $aModel->data($arrFromKeys[$nIdx]) ;
				foreach ($aChildModel as $aChildChildModel)
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

