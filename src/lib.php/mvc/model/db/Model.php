<?php
namespace jc\mvc\model\db ;

use jc\pattern\composite\INamable;
use jc\bean\BeanFactory;
use jc\bean\IBean;
use jc\db\sql\Restriction;
use jc\mvc\model\db\orm\SelectForAssocQuery;
use jc\lang\Exception;
use jc\mvc\model\db\orm\Deleter;
use jc\mvc\model\db\orm\Selecter;
use jc\mvc\model\db\orm\Inserter;
use jc\mvc\model\db\orm\Updater;
use jc\db\DB;
use jc\db\recordset\IRecordSet;
use jc\db\sql\Criteria;
use jc\mvc\model\db\orm\Association;
use jc\mvc\model\AbstractModel ;
use jc\mvc\model\IPaginal;
use jc\mvc\model\db\orm\Prototype;

class Model extends AbstractModel implements IModel , IPaginal, IBean
{
	public function __construct(Prototype $aPrototype=null)
	{
	    parent::__construct();
	    $this->setPrototype($aPrototype);
	}
	
	public static function create($prototype=null)
	{
		// orm config
		if( is_array($prototype) )
		{
			$aPrototype = PrototypeInFragment::createFromCnf($prototype,true,true,true) ;
		}
		
		// Prototype
		else if( $prototype instanceof Prototype )
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
		
		$object = new self($aPrototype);
		$object->criteria()->setLimitLen( 1 ) ;
		return $object;
	}

	public function name()
	{
		return $this->aPrototype? $this->aPrototype->name(): null ; 
	}
		
	/**
	 * @return IModel
	 */
	public function child($sName,$bCreateByAssoc=true)
	{
		$aChild = parent::child($sName) ;
		if( !$aChild and $bCreateByAssoc )
		{
			// 根据 原型 自动创建子模型
			if( $aAssoc=$this->prototype()->associationByName($sName) )
			{
				$aChild = $aAssoc->toPrototype()->createModel( !$aAssoc->isType(Association::oneToOne) ) ;
				$this->addChild($aChild,$sName) ;
			}
		}
		
		return $aChild ;
	}
	
	/**
	 * @return jc\mvc\model\db\orm\Prototype
	 */
	public function prototype()
	{
		return $this->aPrototype ;
	}

	public function setPrototype(Prototype $aPrototype=null)
	{
		$this->aPrototype = $aPrototype ;
	}

	public function loadData( IRecordSet $aRecordSet, $bSetSerialized=false )
	{
		// 通过 prototype 加载各字段数据
		if( $aPrototype=$this->prototype() )
		{
			$arrColumns = array_merge($aPrototype->columns(),$aPrototype->keys());
			foreach( $arrColumns as $sClm )
			{
				$this->setData( $sClm, $aRecordSet->field($aPrototype->sqlColumnAlias($sClm)) ,false) ;
			}
			
			// 加载所有单属关系的子模型
			foreach($aPrototype->associations() as $aAssoc)
			{
				if( $aAssoc->isType(Association::oneToOne) )
				{
					$this->child($aAssoc->name())->loadData($aRecordSet,$bSetSerialized) ;					
				}
			}
		}
		
		// 通过 数据集 加载各字段数据
		else 
		{
			$arrRow = $aRecordSet->current() ;
			foreach ($arrRow as $sClmName=>&$sValue)
			{
				$this->setData($sClmName,$sValue,false) ;
			}
		}
		
		
		if($bSetSerialized)
		{
			$this->setSerialized(true) ;
		}
	}
	
	
	public function load($values=null,$keys=null)
	{
		$selectCriteria = null;//一个临时的Criteria对象，仅在此次load中有效。load结束后立即销毁。
		if($values){
			if(!$keys){
				$keys = $this->prototype()->keys() ;
			}else{
				$keys = (array)$keys;
			}
			if($values instanceof Criteria){
				$selectCriteria = $values;
			}else if($values instanceof Restriction){
				$selectCriteria = clone $this->criteria() ;
				$selectCriteria->restriction()->add($values);
			}else{
				$values = array_values((array) $values) ;
				$selectCriteria = clone $this->criteria();
				foreach($keys as $nIdx=>$sKey)
				{
					$selectCriteria->restriction()->eq( $sKey, $values[$nIdx] ) ;
				}
			}
		}
		$this->setSerialized(true);
		$this->clearChanged();
		Selecter::singleton()->execute( $this->db(), $this, null,$selectCriteria ) ;
		
		return !$this->isEmpty() ;
	}
	
	public function save()
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

	protected function insert()
	{
		return Inserter::singleton()->execute($this->db(), $this) ;
	}
	
	protected function update()
	{
		return Updater::singleton()->execute($this->db(), $this) ;
	}
	
	public function delete()
	{
		if( $this->hasSerialized() )
		{
			return Deleter::singleton()->execute($this->db(), $this) ;	
		}
		
		else 
		{
			return true ;
		}
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
			
			$this->aCriteria = $this->aPrototype->criteria() ;
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
		return 1;
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
	 * @notice 不会发生级连操作。
	 * 既，如果$sName是外键，这个函数不会修改关联表中相应外键的值。
	 * 需要开发者手动保持外键的同步问题。
	 * 或者调用save()方法来保持同步。
	 */
	public function setData($sName,$sValue, $bStrikeChange=true)
	{
		// 原型中的别名
		if( $this->aPrototype )
		{
			$sRealName = $this->aPrototype->getColumnByAlias($sName) ;
			if( $sRealName!==null )
			{
				$sName = $sRealName ;
			}
		}
		
		parent::setData($sName,$sValue, $bStrikeChange) ;
	}
	
	public function setChanged($sName,$bChanged=true)
	{
		// 原型中的别名
		if( $this->aPrototype && $sRealName=$this->aPrototype->getColumnByAlias($sName) )
		{
			$sName = $sRealName ;
		}
		parent::setChanged($sName,$bChanged) ;
	}
	
	/**
	 * @param string $sName	$sName=null返回一个数组，或返回指定数据项的“是否变化”状态
	 */
	public function changed($sName=null)
	{
		// 原型中的别名
		if( $this->aPrototype && $sRealName=$this->aPrototype->getColumnByAlias($sName) )
		{
			$sName = $sRealName ;
		}
		return parent::changed($sName) ;
	}
	
	protected function _data(&$sName)
	{
		$data = parent::_data($sName) ;
		
		// 原型中的别名
		if( $data===null and $this->aPrototype )
		{
			$sAlias = $this->aPrototype->getColumnByAlias($sName) ;
			if( $sAlias!==null )
			{
				return parent::_data($sAlias) ;
			}
		}
		
		return $data ;
	}
	
	// implements IBean
	
	public function build(array & $arrConfig,$sNamespace='*')
	{
		if( !empty($arrConfig['orm']) )
		{
			if( !empty($arrConfig['name']) )
			{
				$arrConfig['orm']['name'] = $arrConfig['name'] ;
			}
			
			if(empty($arrConfig['orm']['class']))
			{
				$arrConfig['orm']['class'] = 'prototype' ;
			}
			if( $aPrototype = BeanFactory::singleton()->createBean($arrConfig['orm'],$sNamespace) )
			{
				$this->setPrototype($aPrototype) ;
			}
		}
		
		$this->arrBeanConfig = $arrConfig ;
	}
	
	public function beanConfig()
	{
		$this->arrBeanConfig ;
	}
	
	
	/**
	 * @var jc\mvc\model\db\orm\Prototype
	 */
	private $aPrototype ;
	private $aCriteria ;
	
	private $arrBeanConfig ;
}

?>
