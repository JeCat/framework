<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\mvc\model\db\orm\AssociationPrototype;

use jc\db\sql\Criteria;
use jc\db\sql\MultiTableStatement;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\db\DB;
use jc\lang\Object;

abstract class OperationStrategy extends Object
{
	abstract public function execute(
				DB $aDB
				, IModel $aModel=null
				, ModelPrototype $aPrototype=null
				, $primaryKeyValues=null
				, $sWhere=null
			) ;
			

	protected function makeAssociationQuerySql(ModelPrototype $aPrototype,MultiTableStatement $aStatement)
	{
		$sTableName = $aPrototype->tableName() ;
		$aTables = $aStatement->tables() ;
		$aJoin = $aTables->sqlStatementJoin() ;

		foreach($aPrototype->associations() as $aAssoPrototype)
		{
			// 联合sql查询
			if( in_array($aAssoPrototype->type(), array(
					AssociationPrototype::hasOne
					, AssociationPrototype::belongsTo
			)) )
			{
				$sAssoTableName = $aAssoPrototype->toPrototype()->tableName() ;
				
				$aTables->join( $sAssoTableName, null, $aAssoPrototype->modelProperty() ) ;
				
				$arrToKeys = $aAssoPrototype->toKeys() ;
				foreach($aAssoPrototype->fromKeys() as $nIdx=>$sFromKey)
				{
					$aJoin->criteria()->addExpression( "%a.%c=%a.%c", $sTableName, $sFromKey, $sAssoTableName, $arrToKeys[$nIdx] ) ;
				}
				
				// 
				$this->makeAssociationQuerySql($aAssoPrototype->toPrototype(),$aStatement) ;
			}
		}
	}
}

?>