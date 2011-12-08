<?php
namespace org\jecat\framework\mvc\model\db ;

use org\jecat\framework\bean\BeanConfException;

use org\jecat\framework\pattern\composite\INamable;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\model\db\orm\Deleter;
use org\jecat\framework\mvc\model\db\orm\Selecter;
use org\jecat\framework\mvc\model\db\orm\Inserter;
use org\jecat\framework\mvc\model\db\orm\Updater;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\recordset\IRecordSet;
use org\jecat\framework\db\sql\Criteria;
use org\jecat\framework\mvc\model\db\orm\Association;
use org\jecat\framework\mvc\model\AbstractModel ;
use org\jecat\framework\mvc\model\db\orm\Prototype;

class Model extends AbstractModel implements IModel, IBean
{
	public function __construct(Prototype $aPrototype=null,$bList=false)
	{
	    parent::__construct($bList);
	    $this->setPrototype($aPrototype);
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
	 * @return org\jecat\framework\mvc\model\db\orm\Prototype
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
		// Model List ------------------------
		if($this->isList())
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
		}
		
		// Model ------------------------
		else
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
		}
		
		if($bSetSerialized)
		{
			$this->setSerialized(true) ;
		}
	}
	
	
	public function load($values=null,$keys=null)
	{
		if($this->isList())
		{
			$this->nTotalCount = -1 ;
		}
		
		return Selecter::singleton()->execute(
					$this , null , self::buildCriteria($this->prototype(),$values,$keys), $this->isList(), $this->db()
		) ;
	}
	
	public function save()
	{
		if( $this->isList() )
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
		if( $this->isList() )
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
				return Deleter::singleton()->execute($this->db(), $this) ;	
			}
			
			else 
			{
				return true ;
			}
		}
	}
	
	static public function buildCriteria(Prototype $aPrototype,$values=null,$keys=null)
	{
		if($values===null)
		{
			return $aPrototype->criteria() ;
		}
		
		$keys = $keys? (array)$keys: $aPrototype->keys() ;
		
		if($values instanceof Criteria)
		{
			return $values;
		}
		else if($values instanceof Restriction)
		{
			$aSelectCriteria = clone $aPrototype->criteria() ;
			
			$aSelectCriteria->where()->add($values) ;
			return $aSelectCriteria ;
		}
		else
		{
			$aSelectCriteria = clone $aPrototype->criteria() ;
			
			$values = array_values((array) $values) ;
			foreach($keys as $nIdx=>$sKey)
			{
				$aSelectCriteria->where()->eq( $sKey, $values[$nIdx] ) ;
			}
			return $aSelectCriteria ;
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
	
	public function setPagination($iPerPage,$iPageNum){
	    $this->prototype()->criteria()->setLimit( $iPerPage, $iPerPage*($iPageNum-1) ) ;
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
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
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
			if(empty($arrConfig['orm']['model-class']))
			{
				$arrConfig['orm']['model-class'] = $arrConfig['class'] ;
			}
			if( !$aPrototype = BeanFactory::singleton()->createBean($arrConfig['orm'],$sNamespace) )
			{
				throw new BeanConfException("无法创建orm bean: %s , %s",array($sNamespace,var_export($arrConfig['orm'],true))) ;
			}
			
			$aBean = $aPrototype->createModel( !empty($arrConfig['list']) ) ;
		}
		else
		{
			$sClass = get_called_class() ;
			$aBean = new $sClass() ;
		}
		
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$this->setList(!empty($arrConfig['list'])) ;
		$this->arrBeanConfig = $arrConfig ;
	}
	
	public function beanConfig()
	{
		$this->arrBeanConfig ;
	}
	
	public function db()
	{
		return DB::singleton() ;
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
	
	/**
	 * @var org\jecat\framework\mvc\model\db\orm\Prototype
	 */
	private $aPrototype ;
	private $aCriteria ;
	
	private $arrBeanConfig ;
}

?>
