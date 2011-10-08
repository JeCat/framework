<?php
namespace jc\mvc\model\db ;

use jc\db\sql\Statement;

use jc\mvc\model\db\orm\operators\SelectForAssocQuery;
use jc\db\sql\Select;
use jc\mvc\model\db\orm\PrototypeAssociationMap;
use jc\lang\Exception;
use jc\mvc\model\db\orm\operators\Deleter;
use jc\mvc\model\db\orm\operators\Selecter;
use jc\mvc\model\db\orm\operators\Inserter;
use jc\mvc\model\db\orm\operators\Updater;
use jc\db\DB;
use jc\db\recordset\IRecordSet;
use jc\db\sql\MultiTableStatement;
use jc\db\sql\Criteria;
use jc\mvc\model\db\orm\Association;
use jc\mvc\model\db\orm\PrototypeInFragment ;
use jc\mvc\model\AbstractModel ;
use jc\db\sql\IDriver ;
use jc\lang\Object;
use jc\mvc\model\IPaginal;
use jc\mvc\view\widget\Paginator;

class Model extends AbstractModel implements IModel , IPaginal
{
	private function __construct()
	{
	}
	
	public static function create($prototype=null)
	{
		// orm config
		if( is_array($prototype) )
		{
			$aPrototype = PrototypeInFragment::createFromCnf($prototype,true,true,true) ;
		}
		
		// Prototype
		else if( $prototype instanceof PrototypeInFragment )
		{
			$aPrototype = $prototype ;
		}
		
		// 字符串做为数据表的表名
		else if( is_string($prototype) )
		{
			$aPrototype = PrototypeInFragment::createFromCnf(
				array('table'=>$prototype),true,true,true
			) ;
		}
		
		else 
		{
			throw new Exception("创建模型时传入的模型原型类型无效") ;
		}
		
		$this->setPrototype($aPrototype) ;
		
		
		$this->criteria()->setLimitLen( $bAggregation? 30: 1 ) ;
	}

	public function serialize ()
	{
		return serialize( array(
		
				'__parent' => parent::serialize() ,
		
				'aPrototype' => &$this->aPrototype ,
		) ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;
		
		parent::unserialize($arrData['__parent']) ;
		
		$this->aPrototype =& $arrData['aPrototype'] ;
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
			if( $aAssocs=$this->prototype()->associations(false) and $aAssociation=$aAssocs->get($sName) )
			{
				$aChild = $aAssociation->toPrototype()->createModel(false) ;
				$this->addChild($aChild,$aAssociation->modelProperty()) ;
				
				// 多属关系
				if( !$aAssociation->isOneToOne() )
				{
					$aChild->setAggregation(true) ;
				}
				
				$aChild->criteria()->setLimit( $aAssociation->count() ) ;
			}
		}
		
		return $aChild ;
	}
	
	/**
	 * @return jc\mvc\model\db\orm\PrototypeInFragment
	 */
	public function prototype()
	{
		return $this->aPrototype ;
	}

	public function setPrototype(PrototypeInFragment $aPrototype=null)
	{
		$this->aPrototype = $aPrototype ;
	}

	public function loadData( IRecordSet $aRecordSet, $bSetSerialized=false )
	{
		// 聚合模型
		if( $this->isAggregation() )
		{
			$aPrototype = $this->prototype() ;
			
			while( $aRecordSet->valid() )
			{
				$aModel = $aPrototype? $aPrototype->createModel(false): new self() ;
				$aModel->loadData($aRecordSet,$bSetSerialized) ;

				$this->addChild($aModel) ;
				
				$aRecordSet->next() ;
			}
		}
		
		// 常规模型
		else 
		{
			// 通过 prototype 加载各字段数据
			if( $aPrototype=$this->prototype() )
			{
				foreach( $aPrototype->columns() as $sClm )
				{
					$this->setData( $sClm, $aRecordSet->field($aPrototype->columnAlias($sClm)) ) ;
				}
			}
			
			// 通过 数据集 加载各字段数据
			else 
			{
				$arrRow = $aRecordSet->current() ;
				foreach ($arrRow as $sClmName=>&$sValue)
				{
					$this->setData($sClmName,$sValue) ;
				}
			}
			
			if($bSetSerialized)
			{
				$this->setSerialized(true) ;
			}
		}
	}
	
	
	public function load($values=null,$keys=null)
	{
		$aPrototypeB = Prototype::create('tableB') 
								->hasOne('tableC') ;
		
		Prototype::create('tableA','')
					->hasOne($aPrototypeB,'uid','pid') ;
		
				
		
		
		if($values){
			if(!$keys){
				$keys = $this->prototype()->primaryKeys() ;
			}else{
				$keys = (array)$keys;
			}
			if($values instanceof Criteria){
				$this->aCriteria = $values;
			}else if($values instanceof Restriction){
				$aCriteria = $this->criteria() ;
				$aCriteria->restriction()->add($values);
			}else{
				$values = array_values((array) $values) ;
				$aCriteria = $this->criteria() ;
				foreach($keys as $nIdx=>$sKey)
				{
					$aCriteria->restriction()->eq( $sKey, $values[$nIdx] ) ;
				}
			}
		}
		
		return Selecter::singleton()->select( $this->db(), $this, $this->aCriteria ) ;
	}
	
	public function save()
	{
		if($this->isAggregation())
		{
			foreach($this->childIterator() as $aChildModel)
			{
				if( !$aChildModel->save() )
				{
					return false ;
				}
			}
			
			return true ;
		}
		
		else 
		{
			// update
			if( $this->hasSerialized() )
			{
				return $this->update() ;
			}
			
			// insert
			else 
			{
				return $this->insert() ;
			}
		}
	}

	public function insert()
	{
		return Inserter::singleton()->insert($this->db(), $this) ;
	}
	
	public function update()
	{
		return Updater::singleton()->update($this->db(), $this) ;
	}
	
	public function delete()
	{
		if($this->isAggregation())
		{
			foreach($this->childIterator() as $aChildModel)
			{
				if( !$aChildModel->delete() )
				{
					return false ;
				}
			}
			
			return true ;
		}
		
		else 
		{
			if( $this->hasSerialized() )
			{
				return Deleter::singleton()->delete($this->db(), $this) ;	
			}
			
			else 
			{
				return true ;
			}
		}
	}
	
	
	
	public function createChild($bAdd=true,$bTearoutPrototype=true)
	{
		if( !$this->aPrototype )
		{
			throw new Exception("模型没有缺少对应的原型，无法为其创建子模型") ;
		}
		if( !$this->isAggregation() )
		{
			throw new Exception("模型(%s)不是一个聚合模型，无法为其创建子模型",$this->aPrototype->name()) ;
		}
		
		$aChild = $this->aPrototype->createModel($bTearoutPrototype) ;
		
		if($bAdd)
		{
			$this->addChild($aChild) ;
		}
		
		return $aChild ;
	}
	
	public function loadChild($values=null,$keys=null)
	{
		$aChild = $this->createChild(false,true) ;
		
		$arrArgvs = func_get_args() ;
		call_user_func_array( array($aChild,'load'), $arrArgvs ) ;

		if( $aChild->hasSerialized() )
		{
			$this->addChild($aChild) ;
			return $aChild ;
		}
		else
		{
			return null ;
		}
	}
	
	/**
	 * @return jc\db\sql\Criteria
	 */
	public function criteria($bAutoCreate=true)
	{
		if( !$this->aCriteria and $bAutoCreate )
		{
			if(!$this->aPrototype)
			{
				throw new Exception("无效的db\\Model,缺少原型对象") ;
			}
			
			$this->aCriteria = $this->aPrototype->sqlFactory()->createCriteria() ;
		}
		
		return $this->aCriteria ;
	}
	
	/**
	 * @return jc\db\sql\Restriction
	 */
	public function createRestriction($bLogic=true)
	{
		return $this->criteria(true)->restriction(true)->createRestriction($bLogic) ;
	}
	
	public function findChildBy($values,$keys=null)
	{
		if(!$keys)
		{
			$keys = $this->prototype()->primaryKeys() ;
		}
		$keys = (array)$keys ;
		$values = (array)$values ;
		
		$keys = array_values($keys) ;
		$values = array_values($values) ;
		
		foreach( $this->childIterator() as $aChild )
		{
			foreach($values as $nIdx=>$sValue)
			{
				if( isset($keys[$nIdx]) and $aChild->data($keys[$nIdx])!=$sValue )
				{
					continue(2) ;
				}
			}
			return $aChild ;
		}
		
		return null ;
	}
	
	public function buildChild($values=null,$keys=null)
	{
		if( !$aChildModel=$this->findChildBy($values,$keys) and !$aChildModel=$this->loadChild($values,$keys) )
		{
			$aChildModel = $this->createChild(true,true) ;
			
			if( $keys )
			{
				$values = (array) $values ;
				foreach((array) $keys as $i=>$sKey)
				{
					$aChildModel->setData($sKey,$values[$i]) ;
				}
			}
		}
		
		return $aChildModel ;
	}
	
	public function totalCount()
	{
		$aSelect = new SelectForAssocQuery($this->prototype()) ;

		if( $this->aCriteria )
		{
		    $ilimitLen = $this->aCriteria->limitLen();
		    $ilimitFrom = $this->aCriteria->limitFrom();
		    $this->aCriteria->setLimit(1000);
			$aSelect->setCriteria($this->aCriteria) ;
		}
		
		$aSelect->setOnlyCount('_cnt',true) ;
	
		$aRecordSet = $this->db()->query($aSelect) ;
		if( !$aRecordSet or !$aRecordSet->rowCount() )
		{
			return 0 ;
		}
		
		if( $this->aCriteria ){
		    $this->aCriteria->setLimit($ilimitLen,$ilimitFrom);
		}
		return intval($aRecordSet->field('_cnt')) ;		
	}
	
	public function setPagination($iPerPage,$iPageNum){
	    $this->criteria()->setLimit( $iPerPage, $iPerPage*($iPageNum-1) ) ;
	}
	
	/**
	 * @return jc\db\DB
	 */
	public function db()
	{
		return DB::singleton() ;
	}
	
	/**
	 * @var jc\mvc\model\db\orm\PrototypeInFragment
	 */
	private $aPrototype ;
	private $aCriteria ;
}

?>
