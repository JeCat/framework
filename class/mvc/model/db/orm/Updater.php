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
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\lang\Object;
use org\jecat\framework\db\DB;

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




