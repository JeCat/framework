<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\Select;

use jc\db\sql\IDataSettableStatement;
use jc\mvc\model\db\orm\AssociationPrototype;
use jc\db\sql\Criteria;
use jc\db\sql\MultiTableStatement;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\db\DB;
use jc\lang\Object;
use jc\db\sql\Insert;

abstract class OperationStrategy extends Object
{
	protected function setCondition(Criteria $aCriteria,$keys,$names=null,IModel $aDataSource,$sTableName='')
	{
		$keys = array_values((array)$keys) ;
		if($names)
		{
			$names = array_values((array)$names) ;
		}
		else 
		{
			$names = $keys ;
		}
		
		if($sTableName)
		{
			$sTableName.= '.' ;
		}
		
		foreach($keys as $idx=>$sKey)
		{
			$aCriteria->add(
				$sTableName . $sKey
				, $aDataSource->data($names[$idx])
			) ;
		}
	}
	
	protected function setValue(IDataSettableStatement $aStatement,$keys,$names=null,IModel $aDataSource,$sTableName='')
	{
		$keys = array_values((array)$keys) ;
		if($names)
		{
			$names = array_values((array)$names) ;
		}
		else 
		{
			$names = $keys ;
		}
		
		if($sTableName)
		{
			$sTableName.= '.' ;
		}
		
		foreach($keys as $idx=>$sKey)
		{
			$aStatement->setData(
				$sTableName . $sKey
				, $aDataSource->data($names[$idx])
			) ;
		}
	}
	
	protected function setAssociationCriteria(Criteria $aCriteria,$sFromTable,$sToTable,array $arrFromKeys,array $arrToKeys)
	{
		foreach($arrFromKeys as $nIdx=>$sFromKey)
		{
			$aCriteria->addExpression( "{$sFromTable}.{$sFromKey} = {$sToTable}.{$arrToKeys[$nIdx]}" ) ;
		}
	}
	
	protected function makeAssociation(MultiTableStatement $aStatement,ModelPrototype $aPrototype,$arrAssoTypes=array(AssociationPrototype::hasOne, AssociationPrototype::belongsTo))
	{
		$aTables = $aStatement->tables() ;
		$aJoin = $aTables->sqlStatementJoin() ;
		
		// 处理关联表
		foreach($aPrototype->associations() as $aAssoPrototype)
		{
			// 联合sql查询
			if( in_array($aAssoPrototype->type(), $arrAssoTypes) )
			{
				$sAssoTableAlias = $aAssoPrototype->modelProperty() ;
				$aTables->join( $aAssoPrototype->toPrototype()->tableName(), null, $sAssoTableAlias ) ;
				
				$this->setAssociationCriteria(
						$aJoin->criteria()
						, $aAssoPrototype->fromPrototype()->name()
						, $sAssoTableAlias
						, $aAssoPrototype->fromKeys()
						, $aAssoPrototype->toKeys()
				) ;
				
				// 递归关联
				$this->makeAssociation($aStatement,$aAssoPrototype->toPrototype(),$arrAssoTypes) ;
			}
		}
	}
	
	protected function buildBridge(DB $aDB,AssociationPrototype $aAssoPrototype,IModel $aFromModel,IModel $aToModel)
	{
		$aSelect = new Select($aAssoPrototype->bridgeTableName()) ;
		$this->setCondition($aSelect->criteria(),$aAssoPrototype->bridgeToKeys(),$aAssoPrototype->fromKeys(),$aFromModel) ;
		$this->setCondition($aSelect->criteria(),$aAssoPrototype->bridgeFromKeys(),$aAssoPrototype->toKeys(),$aFromModel) ;
		
		// 检查对应的桥接表记录是否存在
		if( !$aDB->queryCount($aSelect) )
		{
			$aInsertForBridge = new Insert( $aAssoPrototype->bridgeTableName() ) ;
			
			// from table key vale
			$this->setValue($aInsertForBridge,$aAssoPrototype->bridgeToKeys(),$aAssoPrototype->fromKeys(),$aFromModel) ;
			
			// to table key vale
			$this->setValue($aInsertForBridge,$aAssoPrototype->bridgeFromKeys(),$aAssoPrototype->toKeys(),$aToModel) ;
			
			$aDB->execute($aInsertForBridge) ;
		}
		
	}
	
	protected function destroyBridge()
	{
		
	}
	
}

?>