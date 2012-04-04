<?php
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\db\sql\SQL;

use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\sql\StatementFactory ;

class Updater extends Object{
    public function execute(DB $aDB, Model $aModel){
        $aPrototype = $aModel->prototype();
        $aUpdate = $aPrototype->sharedStatementUpdate() ;
        
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
        //$aCriteria = StatementFactory::singleton()->createCriteria() ;
        //$aUpdate->setCriteria($aCriteria);
        
		// update当前model
        $aUpdate->clearData() ;
        
        // 处理主键
        foreach( $aPrototype->keys() as $sKey)
        {
        	//主键发生修改
	        if($aModel->changed($sKey))
	        {
	        	throw new ORMException('org\jecat\framework\mvc\model\db\orm\Updater : Key 有修改，无法进行Update操作');
	        }
	        
	        //用主键作为查询条件
	        else
	        {
	        	$aUpdate->where()->expression( array(
	        			SQL::createRawColumn(null,$sKey)
	        			, '=', SQL::transValue($aModel->data($sKey))
	        	), true, true ) ;
	        }
        }
        
		$bFlagChanged = false;//当前表是否有修改
		foreach($aPrototype->columns() as $sClmName)
		{
			//只update发生修改的项
			if($aModel->changed($sClmName))
			{
				$aUpdate->setData($sClmName,$aModel->data($sClmName),false);
				$bFlagChanged = true;
			}
		}
		
		//只有当有修改的时候才发生更新
		if($bFlagChanged)
		{
			$aDB->execute($aUpdate);
		}
		
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
				
			case Association::hasAndBelongsToMany :
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
    protected function buildBridge(DB $aDB,Association $aAssociation,Model $aFromModel,Model $aToModel){
    	// nothing to do ...
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


