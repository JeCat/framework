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
	public function __construct($prototype=null,$bAggregarion=false)
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
		else if( $prototype instanceof ModelPrototype )
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

	public function loadData( IRecordSet $aRecordSet, $nRowIdx=0, $sClmPrefix=null)
	{
		// 聚合模型
		if( $this->isAggregarion() )
		{
			$aPrototype = $this->prototype() ;
			for($nIdx=0; $nIdx<$aRecordSet->rowCount(); $nIdx++)
			{
				$aModel = $aPrototype->createModel() ;
				$aModel->loadData($aRecordSet,$nIdx,$sClmPrefix) ;

				$this->addChild($aModel) ;
			}
		}
		
		// 常规模型
		else 
		{
			foreach( $this->prototype()->columns() as $sClm )
			{
				$this->setData( $sClm, $aRecordSet->field($nRowIdx,$sClmPrefix.$sClm) ) ;
			}
		}
	}
	
	
	public function load($values=null,$keys=null)
	{
		if($values)
		{
			$values = (array) $values ;
			
			if(!$keys)
			{
				$keys = $this->prototype()->primaryKeys() ;
			}
			
			foreach($keys as &$sKey)
			{
				if(strstr($sKey,'.')==false)
				{
					$sKey = $this->prototype()->tableName().'.'.$sKey ;
				}
			}
			
			$values = array_combine($keys,$values) ;
		}
		
		if( Selecter::singleton()->select(
			DB::singleton()
			, $this
			, null
			, null
			, $values
			, null
			, $this->isAggregarion()? 30: 1
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