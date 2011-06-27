<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\DB;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\AssociationPrototype;
use jc\db\sql\Update;

class Updater extends OperationStrategy
{
	public function update(DB $aDB, IModel $aModel) 
	{
		$aPrototype = $aModel->prototype() ;
		$aUpdate = new Update($aPrototype->tableName()) ;
		
		// -----------------------------------
		// insert 当前model
		foreach($aPrototype->columnIterator() as $sClmName)
		{
			$aUpdate->setData($sClmName,$aModel->data($sClmName)) ;
		}
		
		$this->setCondition($aUpdate->criteria(), $aPrototype->primaryKeys(), null, $aModel) ;
		
		$aDB->execute( $aUpdate ) ;
		
		// -----------------------------------
		// update 关联model
		foreach($aPrototype->associations() as $aAssoPrototype)
		{
			$aChildModel = $aModel->child( $aAssoPrototype->modelProperty() ) ;
			if(!$aChildModel)
			{
				continue ;
			}
			
			// 
			if( !$aChildModel->save() )
			{
				return false ;
			}
		}
		
		return true ;
	}
}

?>