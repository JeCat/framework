<?php
namespace jc\mvc\model\db ;

use jc\lang\Exception;

use jc\mvc\model\db\orm\operators\Deleter;
use jc\mvc\model\db\orm\operators\Selecter;
use jc\mvc\model\db\orm\operators\Inserter;
use jc\mvc\model\db\orm\operators\Updater;
use jc\db\DB;
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
			$aPrototype = ModelPrototype::createFromCnf($prototype) ;
		}
		
		// Prototype
		else if( $prototype instanceof ModelPrototype )
		{
			$aPrototype = $prototype ;
		}
		
		else if( $prototype===null )
		{
			$aPrototype = null ;
		}
		
		else 
		{
			throw new Exception("创建模型时传入的模型原型无效") ;
		}
		
		$this->setPrototype($aPrototype) ;
	}

	/**
	 * @return IModel
	 */
	public function child($sName)
	{
		$aChild = parent::child($sName) ;
		if(!$aChild)
		{
			// 根据 原型 自动创建子模型
			if( $aAssocs=$this->prototype()->associations(false) and $aAssocPrototype=$aAssocs->get($sName) )
			{
				$aChild = $aAssocPrototype->toPrototype()->createModel() ;
				$this->addChild($aChild,$aAssocPrototype->modelProperty()) ;
				
				// 多属关系
				if( in_array( $aAssocPrototype->type(), array(AssociationPrototype::hasMany,AssociationPrototype::hasAndBelongsToMany) ) )
				{
					$aChild->setAggregarion(true) ;
				}
			}
		}
		
		return $aChild ;
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
	
	public function createChild()
	{
		if( !$this->aPrototype )
		{
			throw new Exception("模型没有缺少对应的原型，无法为其创建子模型") ;
		}
		if( !$this->isAggregarion() )
		{
			throw new Exception("模型(%s)不是一个聚合模型，无法为其创建子模型",$this->aPrototype->name()) ;
		}
		
		$aChild = $this->aPrototype->createModel() ;
		$this->addChild($aChild) ;
		
		return $aChild ;
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
		$keys = (array) $keys ;
		
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
		
		return Selecter::singleton()->select(
			DB::singleton()
			, $this
			, null
			, null
			, $values
			, null
			, $this->isAggregarion()? 30: 1
		) ;
	}
	
	public function totalCount()
	{
		
	}
	
	public function save()
	{
		// update
		if( $this->hasSerialized() )
		{
			return Updater::singleton()->update(DB::singleton(), $this) ;
		}
		
		// insert
		else 
		{
			return Inserter::singleton()->insert(DB::singleton(), $this) ;
		}
	}
	
	public function delete()
	{
		if( $this->hasSerialized() )
		{
			return Deleter::singleton()->delete(DB::singleton(), $this) ;	
		}
		
		else 
		{
			return true ;
		}
	}
	
	/**
	 * @var jc\mvc\model\db\orm\ModelPrototype
	 */
	private $aPrototype ;
}

?>