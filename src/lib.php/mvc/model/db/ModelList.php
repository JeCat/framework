<?php
namespace jc\mvc\model\db ;

use jc\db\recordset\IRecordSet;

class ModelList extends Model
{
	public function loadData( IRecordSet $aRecordSet, $bSetSerialized=false )
	{
		$aPrototype = $this->prototype() ;

		for( ;$aRecordSet->valid(); $aRecordSet->next() )
		{
			if( $aPrototype )
			{
				$aModel = $aPrototype->createModel(false) ;
			}
			else 
			{
				$aModel = new Model() ;
			}
			
			$this->addChild($aModel) ;
			
			$aModel->loadData($aRecordSet,$bSetSerialized) ;
		}
		
		if($bSetSerialized)
		{
			$this->setSerialized(true) ;
		}
	}
	
}

?>