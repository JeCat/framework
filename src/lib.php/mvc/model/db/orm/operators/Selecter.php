<?php
namespace jc\mvc\model\db\orm\operators ;

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
		$aStatement = new Select( $aPrototype->tableName() ) ;
		$aStatement->tables()->setTableAlias( $aPrototype->name() ) ;

		$this->makeStatementAssociationQuery($aPrototype,$aStatement) ;
		
		// $aStatement

		//
		
	}
}

?>