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

use org\jecat\framework\db\sql\SQL;
use org\jecat\framework\db\sql\Delete;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\Model;

class Deleter extends Object{
    public function execute(DB $aDB, Model $aModel)
    {
        $aPrototype = $aModel->prototype();
        $aDelete = new Delete($aPrototype ->tableName());
                
        // 设置limit
        $aDelete->criteria()->setLimit(1);
        
        // delete 当前model
        foreach($aPrototype->keys() as $sClmName)
        {
			if($aModel->changed($sClmName))
			{//主键发生修改
				throw new ORMException('org\jecat\framework\mvc\model\db\orm\Updater : Key 有修改，无法进行Delete操作');
			}
			else
			{//用主键作为查询条件
				$aDelete->criteria()->where()->expression(array(
						SQL::createRawColumn(null,$sClmName) ,
						'=' , SQL::transValue($aModel->data($sClmName))
				),true,true) ;
			}
        }
        $aDB->execute($aDelete);
        
        // 仅delete hasAndBelongsTo的桥表
		foreach($aPrototype->associations() as $aAssociation){
			$aAssociatedModel=$aModel->child($aAssociation->name());
			if(!$aAssociatedModel){
				continue;
			}
			
			switch($aAssociation->type()){
			case Association::hasOne :
				$this->setAssociatedModelData($aModel,$aAssociatedModel,$aAssociation->fromKeys(),$aAssociation->toKeys());
				if(!$aAssociatedModel->delete()){
					return false;
				}
				break;
				
			case Association::belongsTo :
				// nothing todo ...
				break;
				
			case Association::hasMany :
				$this->setAssociatedModelData($aModel,$aAssociatedModel,$aAssociation->fromKeys(),$aAssociation->toKeys());
				if(!$aAssociatedModel->delete()){
					return false;
				}
				break;
				
			case Association::hasAndBelongsToMany :
				// delete bridge table
				foreach($aAssociatedModel->childIterator() as $aOneChildModel){
					$this->deleteBridge($aDB,$aAssociation,$aModel,$aOneChildModel);
				}
				break;
			}
		}
		return true;
    }
    
    protected function deleteBridge(DB $aDB,Association $aAssociation,Model $aFromModel,Model $aToModel){
    	$aStatementFactory = $aAssociation->fromPrototype()->statementFactory() ;
    	$aDeleteForBridge = $aStatementFactory->createDelete($aAssociation->bridgeTableName());
    	$aDeleteForBridge->criteria()->where()->add(
    		$this->makeRestrictionForAssocotion($aFromModel,$aAssociation->fromKeys(),null,$aAssociation->toBridgeKeys(),$aStatementFactory)
    	);
    	$aDB->execute($aDeleteForBridge);
    }
    
    private function setAssociatedModelData(Model $aModel,Model $aChildModel,array $arrFromKeys,array $arrToKeys){
    	foreach($arrToKeys as $nIdx=>$sKey){
    		if($aChildModel->isList()){
    			$value = $aModel->data($arrFromKeys[$nIdx]);
    			foreach($aChildModel->childIterator() as $aChildChildModel){
    				$aChildChildModel->setData($sKey,$value,false);
    			}
    		}else{
    			$aChildModel->setData($sKey,$aModel->data($arrFromKeys[$nIdx]),false);
    		}
    	}
    }
}

