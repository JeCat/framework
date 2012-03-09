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

/**
 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
 *  ==模型的克隆(clone)==
 *
 *  在蜂巢中，Model是一个封装对象，对象是引用传递(址传递)，这里引用了克隆(clone)来完成变量之间的传值，这里要注意clone不是Model的特有函数，而是对象的之间赋值的方法.
 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)模型的克隆"]
 */

class Model extends AbstractModel implements IModel, IBean
{
	/**
	 *  @wiki /MVC模式/数据库模型/模型列表(ModelList)
	 *  ==模型列表(ModelList)==
	 *  
	 *  Model也可以看作成是一个Model容器，可以放置多个Model,是多个Model的集合。
	 *  [example title="/MVC模式/数据库模型/模型列表(ModelList)"]
	 *  
	 */
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

	public function loadData( IRecordSet $aRecordSet )
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
			
				$aModel->loadData($aRecordSet) ;
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
						$this->child($aAssoc->name())->loadData($aRecordSet) ;					
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
	}
	
	/**
 	 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
	 *  ==模型的加载(load)==
	 *
	 *  模型通过加载(load)对数据进行读取,如果不写加载条件，则会对数据表整个进行读取.
	 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)/加载"]
	 */
	
	public function load($values=null,$keys=null)
	{
		// load 前 清理数据
		$this->clearData() ;
		
		if($this->isList())
		{
			$this->nTotalCount = -1 ;
		}
		
		return Selecter::singleton()->execute(
			$this , null , self::buildCriteria($this->prototype(),$values,$keys), $this->isList(), $this->db()
		) ;
	}
<<<<<<< HEAD
	
=======
	
>>>>>>> 405e0f93193f080ed40ad689694a74c0adde8929
	/**
 	 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
	 *  ==模型的保存(save)==
	 *  
	 *  数据的更新，数据的添加是通过模型的保存方法(save)实现的.实际上模型的保存方法是由两个部分集合起来的，insert和update.
	 *  当数据被Model加载过之后，save会自动判断使用update方法，对数据进行更新.当数据没有被Model加载过，save会自动判断使用insert方法,新添加一个数据
	 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)/保存(update)"]
	 */
	
	public function save($bForceCreate=false)
	{
		if( $this->isList() )
		{
			foreach($this->childIterator() as $aChildModel)
			{
				if( !$aChildModel->save($bForceCreate) )
				{
					return false ;
				}
			}
			return true ;
		}
		else
		{
			// update
			if( !$bForceCreate and $this->hasSerialized() )
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
	
	/**
 	 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
	 *  ==模型的删除(delete)==
	 *
	 *  数据的删除，由模型的删除(delete)方法完成.删除数据之前，通过对Model的加载(load)锁定要删除的数据行,这里加载相当与sql中的where的过滤条件
	 *  [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)/删除"]
	 */
	
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
			return Deleter::singleton()->execute($this->db(), $this) ;
		}
	}
	
	/**
	 * @return org\jecat\framework\db\sql\Criteria
	 */
	public function createCriteria(Restriction $aRestriction=null)
	{
		return $this->prototype()->statementFactory()->createCriteria($aRestriction) ;
	}
	
	/**
	 * @return org\jecat\framework\db\sql\Restriction
	 */
	public function createWhere($bLogic=true)
	{
		return $this->prototype()->statementFactory()->createRestriction($bLogic) ;
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
	 * 覆盖父类方法，实现 prototype 字段别名
	 */
	protected function findDataByPath(&$sDataName,&$aModel,&$bDataExist)
	{
		// 原型中的别名
		if( $aPrototype = $this->prototype() )
		{
			$sRealName = $aPrototype->getColumnByAlias($sDataName) ;
			if( $sRealName!==null )
			{
				$sDataName = $sRealName ;
			}
		}
		
		return parent::findDataByPath($sDataName,$aModel,$bDataExist) ;
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
	/**
	 * @wiki /MVC模式/数据库模型/模型的Bean配置数组
	 * ==Bean配置数组==
	 *
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |model-class
	 * |string
	 * |无
	 * |可选
	 * |用哪个类来实现模型对象
	 * |-- --
	 * |table
	 * |string
	 * |无
	 * |可选
	 * |对应的数据库表
	 * |-- --
	 * |name
	 * |string
	 * |无
	 * |可选
	 * |在原型关系中的名字,用来区分不同的原型
	 * |-- --
	 * |columns
	 * |array
	 * |无
	 * |可选
	 * |需要表中哪些列的数据
	 * |-- --
	 * |keys
	 * |array
	 * |无
	 * |可选
	 * |指定表中哪些列为主键,若指定了主键则使用这里的主键而忽略数据库主键,如果未指定则使用数据库指定的主键
	 * |-- --
	 * |alias
	 * |string
	 * |无
	 * |可选
	 * |别名
	 * |-- --
	 * |list
	 * |bool
	 * |false
	 * |可选
	 * |设置此对象是否是其他模型对象的容器
	 * |-- --
	 * |limit
	 * |int
	 * |无
	 * |可选
	 * |设置读取条目数目的上限,下限为0
	 * |-- --
	 * |limitLen
	 * |int
	 * |无
	 * |可选
	 * |设置读取条目数目的上限
	 * |-- --
	 * |limitFrom
	 * |int
	 * |无
	 * |可选
	 * |设置读取条目数目的下限
	 * |-- --
	 * |order
	 * |array
	 * |无
	 * |可选
	 * |指定依据某一列来排序,同时设置正序排列
	 * |-- --
	 * |orderAsc
	 * |string
	 * |无
	 * |可选
	 * |指定依据某一列正序排序
	 * |-- --
	 * |orderDesc
	 * |string
	 * |无
	 * |可选
	 * |指定依据某一列反序排序
	 * |-- --
	 * |orderRand
	 * |bool
	 * |无
	 * |可选
	 * |随机排列结果
	 * |-- --
	 * |where
	 * |array
	 * |无
	 * |可选
	 * |where条件（where的格式很有趣，是对Lisp风格的尝试）
	 * |-- --
	 * |hasOne
	 * |array
	 * |无
	 * |可选
	 * |配置hasone关系
	 * |-- --
	 * |belongsTo
	 * |array
	 * |无
	 * |可选
	 * |配置belongsTo关系
	 * |-- --
	 * |hasMany
	 * |array
	 * |无
	 * |可选
	 * |配置hasMany关系
	 * |-- --
	 * |hasAndBelongsToMany
	 * |array
	 * |无
	 * |可选
	 * |配置hasAndBelongsToMany关系
	 * |}
	 */
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
	
	public function hasSerialized()
	{
		return Selecter::singleton()->hasExists($this) ;
	}
	
	
	
	// ---------------------------------------------
	public function serializableProperties()
	{
		$arrProps = parent::serializableProperties() ;
		$arrProps[__CLASS__] = array('aPrototype') ;
		return $arrProps ;
	}
	
	private $nTotalCount = -1 ;
	
	/**
	 * @var org\jecat\framework\mvc\model\db\orm\Prototype
	 */
	private $aPrototype ;
	private $aCriteria ;
	
	private $arrBeanConfig ;
}

