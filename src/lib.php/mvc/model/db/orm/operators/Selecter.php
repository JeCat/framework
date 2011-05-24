<?php
namespace jc\mvc\model\db\orm\operations ;

use jc\mvc\model\db\orm\AssociationPrototype;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\db\IDriver;
use jc\lang\Exception;
use jc\db\sql\Select;

class Selecter extends OperationStrategy
{
	public function execute( IDriver $aDB, IModel $aModel, ModelPrototype $aPrototype=null, $primaryKeyValues=null, $sWhere=null )
	{
		if(!$aPrototype)
		{
			$aPrototype = $aModel->prototype() ;
			if(!$aPrototype)
			{
				throw new Exception( "缺少有效" ) ;
			}
		}

		// 联合表查询 
		$aStatement = new Select( $aPrototype->tableName() ) ;
		$aStatement->tables()->setTableAlias( $aPrototype->name() ) ;

		$this->makeStatementAssociationQuery($aPrototype,$aStatement) ;

		//
		
	}
}

?>