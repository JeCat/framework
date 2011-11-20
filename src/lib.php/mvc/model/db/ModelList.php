<?php
namespace jc\mvc\model\db ;

use jc\mvc\model\db\orm\Selecter;

use jc\mvc\model\IPaginal;
use jc\db\recordset\IRecordSet;
use jc\mvc\model\IModelList ;

class ModelList extends Model implements IModelList, IPaginal
{
	
	public function load($values=null,$keys=null)
	{
		$this->nTotalCount = -1 ;
		
		return Selecter::singleton()->execute(
						$this
						, null
						, parent::buildCriteria($this->prototype(),$values,$keys)
						, true
		) ;
	}
	public function load($values=null,$keys=null)
	{
		
		return parent::load($values,$keys) ;
	}
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
	
	public function delete(){
		foreach($this->childIterator() as $aChildModel){
			if( !$aChildModel->delete() ){
				return false ;
			}
		}
		return true ;
	}
	
	public function save()
	{
		foreach($this->childIterator() as $aChildModel){
			if( !$aChildModel->save() ){
				return false ;
			}
		}
		return true ;
	}
	
	public function createChild($bAdd=true)
	{
		if( !$this->prototype() )
		{
			throw new Exception("模型没有缺少对应的原型，无法为其创建子模型") ;
		}
		
		$aChild = $this->prototype()->createModel(false) ;
		
		if($bAdd)
		{
			$this->addChild($aChild) ;
		}
		
		return $aChild ;
	}
	
	public function totalCount()
	{
		if($this->nTotalCount<0)
		{
			$this->nTotalCount =Selecter::singleton()->totalCount(DB::singleton(),$this->prototype()) ;
		}
		return $this->nTotalCount ;
	}
	
	private $nTotalCount = -1 ;
}

?>
