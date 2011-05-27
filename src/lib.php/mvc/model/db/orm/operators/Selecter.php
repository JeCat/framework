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
	public function execute( DB $aDB, IModel $aModel=null, ModelPrototype $aPrototype=null, $primaryKeyValues=null, $sWhere=null )
	{
		if(!$aPrototype)
		{
			if( !$aModel and !$aPrototype= $aModel->prototype() )
			{
				throw new Exception( "缺少有效的模型原型" ) ;
			}
		}

		// 联合表查询 
		// ---------------------------
		//  生成 sql
		$aStatement = new Select( $aPrototype->tableName(), $aPrototype->name() ) ;
		$this->makeAssociationQuerySql($aPrototype,$aStatement) ;
		$aStatement->setLimit(1,0) ;
		
		//  执行
		$aRecordSet = $aDB->query($aStatement->makeStatement()) ;
		
		// 加载 sql
		$aModel->loadData($aRecordSet,$aPrototype->name().'.') ;
		
		// 穿件
		
	}
	
	public function makeSelectSql($aPrototype,)
	{
		
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
					$aChildModel = $this->createModelByPrototype( $aAssoPrototype->toPrototype() ) ;
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