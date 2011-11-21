<?php

namespace jc\mvc\model\db\orm;

use jc\mvc\model\IModelList;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;

class Deleter extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $aDelete = StatementFactory::singleton()->createDelete($aPrototype ->tableName());
        
        // 产生一个criteria并设置给$aUpdate
        $aDelete->setCriteria(StatementFactory::singleton()->createCriteria());
        
        // 设置limit
        $aDelete->criteria()->setLimit(1);
        
        // delete 当前model
        foreach($aModel->dataNameIterator() as $sClmName){
        	if(in_array($sClmName,$aPrototype->keys())){//是主键
				if($aModel->changed($sClmName)){//主键发生修改
					throw new ORMException('jc\mvc\model\db\orm\Updater : Key 有修改，无法进行Delete操作');
				}else{//用主键作为查询条件
					$aDelete->criteria()->where()->eq($sClmName,$aModel->data($sClmName));
				}
			}
        }
        $aDB->execute($aDelete->makeStatement());
        
        $aModel->setSerialized(true);
        
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
    
    protected function deleteBridge(DB $aDB,Association $aAssociation,IModel $aFromModel,IModel $aToModel){
    	$aStatementFactory = $aAssociation->fromPrototype()->statementFactory() ;
    	$aDeleteForBridge = $aStatementFactory->createDelete($aAssociation->bridgeTableName());
    	$aDeleteForBridge->criteria()->where()->add(
    		$this->makeRestrictionForAssocotion($aFromModel,$aAssociation->fromKeys(),null,$aAssociation->toBridgeKeys(),$aStatementFactory)
    	);
    	$aDB->execute($aDeleteForBridge);
    }
    
    private function setAssociatedModelData(IModel $aModel,IModel $aChildModel,array $arrFromKeys,array $arrToKeys){
    	foreach($arrToKeys as $nIdx=>$sKey){
    		if($aChildModel instanceof IModelList){
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

?>
