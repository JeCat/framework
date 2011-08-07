<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\TablesJoin;

use jc\db\sql\Table;
use jc\db\sql\Criteria;
use jc\db\recordset\IRecordSet;
use jc\mvc\model\db\Model;
use jc\db\DB;
use jc\mvc\model\db\orm\Association;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\Prototype;
use jc\lang\Exception;
use jc\db\sql\Select;

class Selecter extends OperationStrategy
{
	public function select(DB $aDB, IModel $aModel, Criteria $aCriteria=null, SelectForAssocQuery $aSelect=null )
	{
		// ----------------------------------------
		// 对所有1对1关系，进行递归联合查询 
		if(!$aSelect)
		{
			$aSelect = new SelectForAssocQuery($aModel->prototype()) ;
		}
		
		$aSelect->setLimit( $aModel->limitLength(), $aModel->limitFrom() ) ;
	
		// 设置主键查询条件
		if( $aCriteria )
		{
			$aSelect->setCriteria($aCriteria) ;
		}

		//  执行
		$aRecordSet = $aDB->query($aSelect) ;
		if( !$aRecordSet or !$aRecordSet->rowCount() )
		{
			return false ;
		}
		
		// 加载聚合模型
		if($aModel->isAggregation())
		{
			for( $aRecordSet->rewind(); $aRecordSet->valid(); $aRecordSet->next() )
			{
				$aChildModel = $aModel->prototype()->createModel() ;
				$aModel->addChild($aChildModel) ;
				
				if(!$this->loadModel($aDB,$aChildModel,$aRecordSet))
				{
					return false ;
				}
			}
		}
		
		// 加载独立模型
		else 
		{
			return $this->loadModel($aDB,$aModel,$aRecordSet) ;
		}
	}

	protected function loadModel( DB $aDB, IModel $aModel, IRecordSet $aRecordSet )
	{		
		// load 自己
		$aModel->loadData($aRecordSet,true) ;
		
		////////////////////////////////////////////////////////////
		// 加载关联model
		foreach($aModel->prototype()->associations() as $aAssociation)
		{						
			// -------------------------------------------------------------
			// 一对一关联（从传入的recordset里加载数据）
			if( $aAssociation->isOneToOne() )
			{
				if( !$this->loadModel($aDB,$aModel->child($aAssociation->modelProperty()),$aRecordSet) )
				{
					return false ;
				}
			}
			
			// -------------------------------------------------------------
			// 一对多关联
			else if( $aAssociation->type()==Association::hasMany )
			{
				$aCriteria = new Criteria() ;
				$arrFromKeys = $aAssociation->fromKeys() ;
				foreach($aAssociation->toKeys() as $nIdx=>$sKey)
				{
					$aCriteria->add($sKey,$aModel->data($arrFromKeys[$nIdx])) ;
				}
				
				// 加载 child 类
				if( !$this->select($aDB, $aModel->child($aAssociation->modelProperty()), $aCriteria ) )
				{
					return false ;
				}
			}
			
			// -------------------------------------------------------------
			// 多对多关联
			else if( $aAssociation->type()==Association::hasAndBelongsToMany )
			{
				$aSelect = new SelectForAssocQuery($aAssociation->toPrototype()) ;
				
				// bridge 表到 to表的关联条件
				$sBridgeTableAlias = $aAssociation->toPrototype()->tableAlias().'#bridge' ;
				
				$aBridgeCriteria=new Criteria() ;
				$this->setAssociationCriteria(
						$aBridgeCriteria
						, $sBridgeTableAlias, $aAssociation->toPrototype()->tableAlias()
						, $aAssociation->bridgeFromKeys(), $aAssociation->toKeys()
				) ;
				
				// build sql table join by association
				$aTableJoin = new TablesJoin() ;
				$aTableJoin->addTable(
						new Table($aAssociation->bridgeTableName(),$sBridgeTableAlias)
						, $aBridgeCriteria
				) ;
				$aAssociation->toPrototype()->sqlTable()->addJoin($aTableJoin) ;
				
				
				// from 表到 bridge 表的关联条件
				$arrFromKeys = $aAssociation->fromKeys() ;
				foreach($aAssociation->bridgeToKeys() as $nIdx=>$sKey)
				{
					$aSelect->criteria()->add("`{$sBridgeTableAlias}`.`{$sKey}`",$aModel->data($arrFromKeys[$nIdx])) ;
				}
				
				// 加载 child 类
				if( !$this->select($aDB, $aModel->child($aAssociation->modelProperty()), null, $aSelect ) )
				{
					return false ;
				}
			}
		}
		
		return true ;
	}
	
}

?>