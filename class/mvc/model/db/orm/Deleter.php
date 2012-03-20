<?php
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\lang\Object;
use org\jecat\framework\db\DB;
use org\jecat\framework\mvc\model\db\Model ;
use org\jecat\framework\db\sql\StatementFactory ;

class Deleter extends Object{
    public function execute(DB $aDB, Model $aModel){
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
					throw new ORMException('org\jecat\framework\mvc\model\db\orm\Updater : Key 有修改，无法进行Delete操作');
				}else{//用主键作为查询条件
					$aDelete->criteria()->where()->eq('`'.$sClmName.'`',$aModel->data($sClmName));
				}
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

?>
