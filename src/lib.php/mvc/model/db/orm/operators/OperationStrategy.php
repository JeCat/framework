<?php
namespace jc\mvc\model\db\orm\operations ;

use jc\db\sql\Criteria;

use jc\db\sql\MultiTableStatement;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\db\IDriver;
use jc\lang\Object;

abstract class OperationStrategy extends Object
{
	abstract public function execute(
				IDriver $aDB
				, IModel $aModel
				, ModelPrototype $aPrototype=null
				, $primaryKeyValues=null
				, $sWhere=null
			) ;
			

	protected function makeStatementAssociationQuery(ModelPrototype $aPrototype,MultiTableStatement $aStatement)
	{
		$sTableName = $aPrototype->tableName() ;
		$aTables = $aStatement->tables() ;
		$aJoin = $aTables->sqlStatementJoin() ;

		foreach($aPrototype->associations()->iterator() as $aAssoPrototype)
		{
			// 联合sql查询
			if( in_array($aAssoPrototype->type(), array(
					AssociationPrototype::hasOne
					, AssociationPrototype::belongsTo
			)) )
			{
				$sAssoTableName = $aAssoPrototype->toPrototype()->tableName() ;
				
				$aTables->join( $sAssoTableName ) ;
				$aTables->setTableAlias($sAssoTableName,$aAssoPrototype->modelProperty()) ;
				
				$arrToKeys = $aAssoPrototype->toKeys() ;
				foreach($aAssoPrototype->fromKeys() as $nIdx=>$sFromKey)
				{
					$aJoin->add( "%t.%c=%t.%c", $sTableName, $sFromKey, $sAssoTableName, $arrToKeys[$nIdx] ) ;
				}
				
				// 
				$this->makeStatementAssociationQuery($aAssoPrototype->toPrototype(),$aStatement) ;
			}
		}
	}
}

?>