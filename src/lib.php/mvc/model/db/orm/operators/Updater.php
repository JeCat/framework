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
		if( $aModel->isAggregarion() )
		{
			foreach($aModel->childIterator() as $aChildModel)
			{
				if( !$this->update($aDB, $aChildModel) )
				{
					return false ;
				}
			}
			
			return true ;
		}
		
		else 
		{
			$aPrototype = $aModel->prototype() ;
			$aUpdate = new Update($aPrototype->tableName()) ;
			
			// -----------------------------------
			// insert 当前model
			foreach($aPrototype->columnIterator() as $sClmName)
			{
				$aUpdate->setData($sClmName,$aModel->data($sClmName)) ;
			}
			
			$this->setCondition($aUpdate->criteria(), $aPrototype->primeryKeys(), null, $aModel) ;
			
			if( $aDB->execute( $aUpdate )===false )
			{
				return false ;
			}
			
			// -----------------------------------
			// update 关联model
			foreach($aPrototype->associations() as $aAssoPrototype)
			{
				$aChildModel = $aModel->child( $aAssoPrototype->propertyName() ) ;
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
		}
		
		return true ;
	}
}

?>