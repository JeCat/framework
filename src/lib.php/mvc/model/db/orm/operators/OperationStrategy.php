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
	/*abstract public function execute(
				DB $aDB
				, IModel $aModel=null
				, ModelPrototype $aPrototype=null
				, $primaryKeyValues=null
				, $sWhere=null
			) ;*/
			

	protected function makeAssociationQuerySql(ModelPrototype $aPrototype,MultiTableStatement $aStatement)
	{
		$sTableName = $aStatement->realTableName($aPrototype->tableName()) ;
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
				$sAssoTableName = $aStatement->realTableName($aAssoPrototype->toPrototype()->tableName()) ;
				
				$aTables->join( $sAssoTableName, null, $aAssoPrototype->modelProperty() ) ;
				
				$arrToKeys = $aAssoPrototype->toKeys() ;
				foreach($aAssoPrototype->fromKeys() as $nIdx=>$sFromKey)
				{
					$aJoin->criteria()->add( "{$sTableName}.{$sFromKey} = {$sAssoTableName}.{$arrToKeys[$nIdx]}" ) ;
				}
				
				// 
				$this->makeAssociationQuerySql($aAssoPrototype->toPrototype(),$aStatement) ;
			}
		}
	}
	
	protected function setCondition(Criteria $aCriteria,$keys,$values)
	{
		$keys = array_values((array)$keys) ;
		$values = array_values((array)$values) ;
		
		foreach($keys as $nIdx=>$sKey)
		{
			$aCriteria->add($sKey,$values[$nIdx]) ;
		}
	}
}

?>