<?php
namespace jc\mvc\model\db ;

use jc\db\DB;
use jc\mvc\model\db\orm\operators\Selecter;
use jc\db\IRecordSet;
use jc\db\sql\MultiTableStatement;
use jc\mvc\model\db\orm\AssociationPrototype;
use jc\mvc\model\db\orm\ModelPrototype ;
use jc\mvc\model\Model as BaseModel ;
use jc\db\sql\IDriver ;

class Model extends BaseModel implements IModel
{
	public function __construct($prototype,$bAggregarion=false)
	{
		parent::__construct($bAggregarion) ;
		
		// orm config
		if( is_array($prototype) )
		{
			$this->setPrototype(
				ModelPrototype::createFromCnf($prototype)
			) ;
		}
		
		// Prototype
		else if( $prototype instanceof Prototype )
		{
			$this->setPrototype( $prototype ) ;
		}
		
		// db table name
		else if( is_string($prototype) )
		{
			
		}
	}
	
	/**
	 * @return jc\mvc\model\db\orm\ModelPrototype
	 */
	public function prototype()
	{
		return $this->aPrototype ;
	}

	public function setPrototype(ModelPrototype $aPrototype=null)
	{
		$this->aPrototype = $aPrototype ;
	}

	public function loadData( IRecordSet $aRecordSet, $sClmPrefix=null)
	{
		// 聚合模型
		if( $this->isAggregarion() )
		{
			$aPrototype = $this->prototype() ;
			foreach($aRecordSet as $arrRow)
			{
				$aModel = $aPrototype->createModel() ;
				$aModel->loadArrayData($arrRow,$sClmPrefix) ;
				
				$this->addChild($aModel) ;
			}
		}
		
		// 常规模型
		else 
		{
			$arrRow = $aRecordSet->row() ;
			if(!$arrRow)
			{
				return ;
			}
			
			$this->loadArrayData($arrRow,$sClmPrefix) ;
		}
	}
	public function loadArrayData( array $arrRow, $sClmPrefix=null)
	{
		$nPreLen = $sClmPrefix? strlen($sClmPrefix): 0 ;
		foreach($arrRow as $sClm=>&$sValue)
		{
			if( $sClmPrefix and substr($sClm,0,$nPreLen)!=$sClmPrefix )
			{
				continue ;
			}
			
			$this->set(
				substr($sClm,$nPreLen), $sValue
			) ;
		}
	}
	
	public function load($values=null,$keys=null)
	{
		if( Selecter::singleton()->select(
			DB::singleton()
			, $this
			, $this->prototype()
			, $values
		) )
		{
			$this->setSerialized(true) ;
			
			return true ;
		}
		
		else 
		{
			return false ;
		}
	}
	
	public function totalCount()
	{
		
	}
	
	public function save()
	{
		// update
		if( $this->hasSerialized() )
		{
			
		}
		
		// insert
		else 
		{
			
		}
	}
	
	public function delete()
	{
		if( !$this->hasSerialized() )
		{
			return ;
		}
		
	}
	
	
}

?>