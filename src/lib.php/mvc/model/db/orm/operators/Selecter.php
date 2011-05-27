<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\IRecordSet;

use jc\mvc\model\db\Model;
use jc\db\DB;
use jc\mvc\model\db\orm\AssociationPrototype;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\lang\Exception;
use jc\db\sql\Select;

class Selecter extends OperationStrategy
{
	
	public function select( DB $aDB, IModel $aModel, Select $aSelect=null, array $keyValues=null, $sWhere=null, $nLimitLen=1, $nLimitFrom=0 )
	{
		$aPrototype = $aModel->prototype() ;
		
		// 对所有1对1关系，进行递归联合查询 
		// ---------------------------
		//  生成 sql select
		if(!$aSelect)
		{
			$aStatement = new Select( $aPrototype->tableName(), $aPrototype->name() ) ;
		}
		
		// 组建 sql select
		$this->makeAssociationQuerySql($aPrototype->name(),$aPrototype,$aStatement) ;
		$aStatement->setLimit($nLimitLen,$nLimitFrom) ;
		
		// 设置主键查询条件
		if( $keyValues )
		{
			$aCriteria = $aStatement->criteria() ;
			$sTableAlias = $aPrototype->name() ;
			foreach($keyValues as $sKey=>$sValue)
			{
				$aCriteria->add($sTableAlias.'.'.$sKey,$sValue) ;
			}
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
		$this->loadModel($aModel, $aRecordSet, $aPrototype->name().'.') ;
		
		return true ;
	}

	
	
	protected function makeAssociationQuerySql($sTableName,ModelPrototype $aPrototype,Select $aStatement)
	{
		$aTables = $aStatement->tables() ;
		$aJoin = $aTables->sqlStatementJoin() ;

		foreach($aPrototype->columnIterator() as $sClmName)
		{
			$aStatement->addColumn($sTableName.'.'.$sClmName,$sTableName.'.'.$sClmName) ;
		}
		
		// 处理关联表
		foreach($aPrototype->associations() as $aAssoPrototype)
		{
			// 联合sql查询
			if( in_array($aAssoPrototype->type(), array(
					AssociationPrototype::hasOne
					, AssociationPrototype::belongsTo
			)) )
			{
				$sAssoTableAlias = $aAssoPrototype->modelProperty() ;
				$aTables->join( $aAssoPrototype->toPrototype()->tableName(), null, $sAssoTableAlias ) ;
				
				$arrToKeys = $aAssoPrototype->toKeys() ;
				foreach($aAssoPrototype->fromKeys() as $nIdx=>$sFromKey)
				{
					$aJoin->criteria()->add( "{$sTableName}.{$sFromKey} = {$sAssoTableAlias}.{$arrToKeys[$nIdx]}" ) ;
				}
				
				$this->makeAssociationQuerySql($sAssoTableAlias,$aAssoPrototype->toPrototype(),$aStatement) ;
			}
		}
	}
	
	protected function loadModel( IModel $aModel, IRecordSet $aRecordSet, $sClmPrefix )
	{
		$aPrototype = $aModel->prototype() ;
		
		if(!$sClmPrefix)
		{
			$sClmPrefix = $aPrototype->name() ;
		}
		
		// load 自己
		$aModel->loadData($aRecordSet,$sClmPrefix) ;
		
		// 加载关联model
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
				
				$this->loadModel($aChildModel,$aRecordSet,$aAssoPrototype->modelProperty()) ;
			}
			
			// 一对多关联
			else if( $aAssoPrototype->type()==AssociationPrototype::hasMany )
			{
				
				$aChidModel = $aToPrototype->createModel() ;
				
				$arrKeys = $aAssoPrototype->toKeys() ;
				$arrKeyValues = array() ;
				foreach($arrKeys as $sKey)
				{
					$arrKeyValues[$sKey] = $aModel->get($sKey) ;
				}
				$this->select($aDB, $aChidModel, null, null, $arrKeyValues, 30 ) ;
				
				$aModel->addChild($aChidModel,$aAssoPrototype->modelProperty()) ;
			}
		}
	}
}

?>