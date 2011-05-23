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
		if( $aStatement->tables()->tableName() )
		{
			$aStatement->tables()->setTableName($aPrototype->tableName()) ;
		}
		
	
		foreach($aPrototype->associations()->iterator() as $aAssoPrototype)
		{
			// 联合sql查询
			if( in_array($aAssoPrototype->type(), array(
					AssociationPrototype::hasOne
					, AssociationPrototype::belongsTo
			)) )
			{
				
				$aStatement->tables()->join($aAssoPrototype->toPrototype()->tableName(),array(
					
				)) ;
			}
		}
	}
}

?>