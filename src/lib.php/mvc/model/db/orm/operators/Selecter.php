<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\mvc\model\db\Model;
use jc\db\DB;
use jc\mvc\model\db\orm\AssociationPrototype;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\lang\Exception;
use jc\db\sql\Select;

class Selecter extends OperationStrategy
{
	
	public function select( DB $aDB, IModel $aModel, ModelPrototype $aPrototype, $primaryKeyValues=null, $sWhere=null )
	{
		// 对所有1对1关系，进行递归联合查询 
		// ---------------------------
		//  生成 sql
		$aStatement = new Select( $aPrototype->tableName(), $aPrototype->name() ) ;
		$this->makeAssociationQuerySql($aPrototype,$aStatement) ;
		$aStatement->setLimit(1,0) ;
		
		// 设置主键查询条件
		if($primaryKeyValues)
		{
			$this->setCondition($aStatement->criteria(),$aPrototype->primaryKeys(),$primaryKeyValues) ;
		}
		
		// 设置查询条件
		if( $sWhere )
		{
			$aStatement->criteria()->add($sWhere) ;
		}

		//  执行
		$aRecordSet = $aDB->query($aStatement) ;
		if(!$aRecordSet)
		{
			return false ;
		}
		
		// 加载 sql
		$aModel->loadData($aRecordSet,$aPrototype->name().'.') ;
		
		
		
		// 分别处理1对多关系
		// ---------------------------
		
		
		
		return true ;
	}
	
	/**
	 * @return jc\mvc\model\db\Model
	 */
	public function createModelByPrototype(ModelPrototype $aPrototype)
	{
		$aModel = new Model() ;
		$aModel->setPrototype($aPrototype) ;
		
		return $aModel ;
	}
	
	protected function loadModel( IModel $aModel, IRecordSet $aRecordSet, $sClmPrefix )
	{
		if(!$sClmPrefix)
		{
			$sClmPrefix = $aPrototype->name() ;
		}
		
		// load 自己
		$aModel->loadData($aRecordSet,$sClmPrefix) ;
		
		// load association modal
		$aPrototype = $aModel->prototype() ;
		foreach($aPrototype->associations() as $aAssoPrototype)
		{
			// 一对一关联
			if( in_array($aAssoPrototype->type(), array(
					AssociationPrototype::hasOne
					, AssociationPrototype::belongsTo
			)) )
			{
				$aChildModel = $aModel->child( $aAssoPrototype->modelProperty() ) ;
				if(!$aChildModel)
				{
					$aChildModel = $aAssoPrototype->toPrototype()->createModel() ;
					$aModel->addChild($aModel,$aAssoPrototype->modelProperty()) ;
				}
				
				$this->loadModel($aChildModel,$aRecordSet,$aAssoPrototype->toPrototype(),$aAssoPrototype->modelProperty()) ;
			}
			
			// 一对多关联
			else if( $aAssoPrototype->type()==AssociationPrototype::hasMany )
			{
				
			}
		}
	}
}

?>