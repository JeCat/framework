<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\mvc\model\IModelList;
use jc\db\sql\StatementFactory ;
use jc\lang\Exception;

class Updater extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $aUpdate = $aPrototype->statementUpdate() ;
        
        // 从 belongs to model 中设置外键值
        foreach($aPrototype->associations() as $aAssociation){
        	if($aAssociation->isType(Association::belongsTo)){
        		if( !$aAssociatedModel = $aModel->child( $aAssociation->name() ) ){
        			continue;
        		}
        		if( !$aAssociatedModel->save() ){
        			return false;
        		}
        		$this->setAssociatedModelData($aAssociatedModel,$aModel,$aAssociation->toKeys(),$aAssociation->fromKeys());
        	}
        }
        
        // 产生一个criteria并设置给$aUpdate
        $aCriteria = StatementFactory::singleton()->createCriteria() ;
        $aUpdate->setCriteria($aCriteria);
        
		// update当前model
        $aUpdate->clearData() ;
		$bFlagChanged = false;//当前表是否有修改
		foreach($aModel->dataNameIterator() as $sClmName)
		{
			if(in_array($sClmName,$aPrototype->keys())){//是主键
				if($aModel->changed($sClmName)){//主键发生修改
					throw new Exception('jc\mvc\model\db\orm\Updater : Key 有修改，无法进行Update操作');
				}else{//用主键作为查询条件
					$aCriteria->restriction()->eq($sClmName,$aModel->data($sClmName));
				}
			}else{//主键以外的项
				if($aModel->changed($sClmName)){//只update发生修改的项
					$aUpdate->setData($sClmName,$aModel->data($sClmName));
					$bFlagChanged = true;
				}
			}
		}
		if($bFlagChanged){//只有当有修改的时候才发生更新
			$aDB->execute($aUpdate->makeStatement());
		}
		
		$aModel->setSerialized(true);
		
		// update关联model
		foreach($aPrototype->associations() as $aAssociation){
			$aAssociatedModel=$aModel->child($aAssociation->name());
			if(!$aAssociatedModel){
				continue;
			}
			
			switch($aAssociation->type()){
			case Association::hasOne :
				$this->setAssociatedModelData($aModel,$aAssociatedModel,$aAssociation->fromKeys(),$aAssociation->toKeys());
				if(!$aAssociatedModel->save()){
					return false;
				}
				break;
				
			case Association::belongsTo :
				// nothing todo ...
				break;
				
			case Association::hasMany :
				$this->setAssociatedModelData($aModel,$aAssociatedModel,$aAssociation->fromKeys(),$aAssociation->toKeys());
				if(!$aAssociatedModel->save()){
					return false;
				}
				break;
				
			case Association::hasAndBelongsTo :
				if(!$aAssociatedModel->save()){
					return false;
				}
				// update bridge table
				foreach($aAssociatedModel->childIterator() as $aOneChildModel){
					$this->buildBridge($aDB,$aAssociation,$aModel,$aOneChildModel);
				}
				break;
			}
		}
		return true;
    }
    
    /**
     * 在现在的版本中，没有跟踪修改前数据的机制。
     * 所以无法找到桥接表上需要修改的是哪一行。
     * 所以这个函数什么也不做。
     */
    protected function buildBridge(DB $aDB,Association $aAssociation,IModel $aFromModel,IModel $aToModel){
    	// nothing to do ...
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
