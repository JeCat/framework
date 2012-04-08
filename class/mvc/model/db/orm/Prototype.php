<?php
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\db\sql\parser\BaseParserFactory;
use org\jecat\framework\db\sql\compiler\SqlNameCompiler;
use org\jecat\framework\db\sql\compiler\SqlCompiler;
use org\jecat\framework\db\sql\SQL;
use org\jecat\framework\db\sql\Criteria;
use org\jecat\framework\db\sql\Update;
use org\jecat\framework\lang\Object;
use org\jecat\framework\mvc\model\db\ModelList;
use org\jecat\framework\db\sql\Order;
use org\jecat\framework\util\serialize\IIncompleteSerializable;
use org\jecat\framework\util\serialize\ShareObjectSerializer;
use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\lang\Type;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\db\sql\Statement;
use org\jecat\framework\db\sql\StatementState;
use org\jecat\framework\db\sql\name\NameTransferFactory;
use org\jecat\framework\mvc\model\db\orm\Association;
use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\db\sql\name\NameTransfer;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\db\reflecter\AbstractReflecterFactory;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\DB;
use org\jecat\framework\db\sql\StatementFactory;

/**
 * 
 * @author qusong
 * @wiki /MVC模式/数据库模型/数据表原型
 * ==数据表原型(ProtoType)==
 * 
 * 蜂巢充分的利用面向对象的思想，这里原型就是将数据表抽象为对象，对数据表进行封装，用户可以直接通过对原型对象的操作，来对数据进行操作。
 * 所有的Model的创建方法的实现都是原型创建出来的，原型是和数据表结合最近的环节。
 *
 */

class Prototype extends Object implements IBean, \Serializable, IIncompleteSerializable
{
	const youKnow = null ;
	
	const MODEL_IMPLEMENT_CLASS_NS = 'org\\jecat\\framework\\mvc\\model\\db\\imp' ;
	const MODEL_IMPLEMENT_CLASS_BASE = 'org\\jecat\\framework\\mvc\\model\\db\\Model' ;
	
	const PROTOTYPE_IMPLEMENT_CLASS_NS = 'org\\jecat\\framework\\mvc\\model\\db\\prototype' ;
	const PROTOTYPE_IMPLEMENT_CLASS_BASE = __CLASS__ ;
	
	
	// static creator
	/**
	 * @return Prototype
	 */
	/**
	 * @wiki /MVC模式/数据库模型/数据表原型
	 *	==原型的创建==
	 *	
	 *  原型的创建方式有两种.
	 *  1.通过原型的静态方法create直接创建ProtoType对象,这种方法会通过对传入的参数(数据表名,字段名,)来构造一个ProtoType,这个ProtoTpe对象是有血有肉的。
	 *  2.通过new Prototype创建一个ProtoType对象,这种方法则不会对构造出的ProtoType对象,这个ProtoType对象没有制定哪个数据表,相对于create的创建方法，这个ProtoType对象，如同一个躯壳。
	 */
	static public function create( $sTableName, $keys=self::youKnow, $columns=self::youKnow , $aDB = self::youKnow )
	{
		$aPrototype = new Prototype ;
		
		$aPrototype->setTableName($sTableName) ;
		$aPrototype->setName($sTableName) ;
		$aPrototype->arrColumns = $columns ;
		$aPrototype->arrKeys = self::youKnow ;
		
		$aPrototype->aDB = $aDB===self::youKnow? DB::singleton(): $aDB ;
		
		return $aPrototype;
	}
	
	// getter and setter
	
	public function name()
	{
		return $this->sName;
	}
	/**
	 * @return Prototype
	 */
	public function setName($sName)
	{
		$this->sName = $sName;
		return $this;
	}
	
	public function keys()
	{
		if( $this->arrKeys===self::youKnow )
		{
			$this->arrKeys = $this->tableReflecter()->primaryName() ;
			if( $this->arrKeys )
			{
				$this->arrKeys = (array) $this->arrKeys ;
			}
		}
		return $this->arrKeys;
	}
	
	/**
	 *   键可以为多个。本函数接受一个数组（多个键）或一个字符串（一个键）。
	 * @return Prototype
	 */
	public function setKeys( $keys )
	{
		$this->arrKeys = (array)$keys ;
		return $this;
	}
	
	/**
	 *   数据表定义的主键
	 */
	public function devicePrimaryKey()
	{
		if( $this->sDevicePrimaryKey===self::youKnow )
		{
			if( !$this->sDevicePrimaryKey = $this->tableReflecter()->primaryName() )
			{
				$this->sDevicePrimaryKey = '' ;
			}
		}
		
		return $this->sDevicePrimaryKey ?: null ;
	}
	
	public function tableName()
	{
		return $this->sTableName;
	}
	/**
	 * @return Prototype
	 */
	public function setTableName($sTableName)
	{
		$this->sTableName = $sTableName;
		return $this;
	}
	
	public function associatedBy()
	{
		return $this->aAssociationBy;
	}
	
	// columns
	public function columns()
	{
		if( $this->arrColumns===self::youKnow or $this->arrColumns=='*' )
		{
			$this->arrColumns = $this->tableReflecter()->columns() ;
		}
		return $this->arrColumns;
	}
	
	/**
	 *  本函数接受一个数组（多个列）或一个字符串（一个列）。
	 * @return Prototype
	 */
	public function addColumns($columnNames)
	{
		if( $this->arrColumns===self::youKnow or $this->arrColumns=='*' )
		{
			$this->arrColumns = array() ;
		}
		
		foreach(Type::toArray($columnNames) as $sColumnName)
		{
			if( $sColumnName and !in_array($sColumnName,$this->arrColumns) )
			{
				$this->arrColumns[] = $sColumnName ;
			}
		}
		
		return $this;
	}
	
	public function removeColumn($sColumn)
	{
		$key = array_search($sColumn,$this->arrColumns) ;
		
		if($key!==false)
		{
			unset($this->arrColumns[$key]);
		}
		
		return $this;
	}
	
	public function clearColumns()
	{
		$this->arrColumns=array() ;
		return $this ;
	}
	
	public function columnIterator()
	{
		return new \ArrayIterator($this->arrColumns) ;
	}
	
	public function columnAliases()
	{
		return $this->arrColumnAliases ;
	}
	public function getColumnByAlias($sAlias)
	{
		return isset($this->arrColumnAliases[$sAlias])? 
					$this->arrColumnAliases[$sAlias]: null ;
	}
	/**
	 * @return Prototype
	 */
	public function addColumnAlias($column,$sAlias=null)
	{
		if( is_array($column) )
		{
			$this->arrColumnAliases = array_merge($this->arrColumnAliases,$column) ;
		}
		else
		{
			$this->arrColumnAliases[$sAlias] = $column;
		}
		
		return $this;
	}
	
	/**
	 * @return Prototype
	 */
	public function removeColumnAlias($sAlias)
	{
		unset($this->arrColumnAliases[$sAlias]);
		return $this;
	}
	
	/**
	 * @return Prototype
	 */
	public function clearColumnAliases(){
		$this->arrColumnAliases=array();
		return $this;
	}
	
	public function aliasColumnMapIterator(){
		return new \ArrayIterator($this->arrColumnAliases);
	}
	
	// association
	public function associations(){
		return $this->arrAssociations;
	}
	
	/**
	 * @return Association
	 * @wiki /MVC模式/数据库模型/数据表关联
	 * ==hasOne==
	 * 
	 * 一对一关系,当两个模型创建为一对一关系时，对于一个数据表的操作，也会影响到另一个数据表.打个比方，假如有两个Model，一个为主Model，一个为从Model。
	 * 当主Model向从Model建立hasOne关系后，如果设置了关联的字段，当对主Model的数据进行操作时，从Model也会发生相应的改变。
	 */
	
	public function hasOne($toTable,$fromKeys=self::youKnow,$toKeys=self::youKnow){
		return $this->createAssociation(Association::hasOne,$toTable,$fromKeys,$toKeys);
	}

	/**
	 * @return Association
	 * @wiki /MVC模式/数据库模型/数据表关联
	 * ==hasMany==
	 *
	 * 一对多关系,当两个模型创建为一对多关系时，假如说主表是唯一，从表是主表的附属关系，也就是主表为一，从表为多，当设置关键字段时，主表的增，删，改都会直接影响到从表.
	 */
	
	public function hasMany($toTable,$fromKeys=self::youKnow,$toKeys=self::youKnow){
		return $this->createAssociation(Association::hasMany,$toTable,$fromKeys,$toKeys);
	}

	/**
	 * @return Association
	 * @wiki /MVC模式/数据库模型/数据表关联
	 * ==belongsTo==
	 *
	 * 一对一关系,belongsTo有别于hasOne，只有当查询数据时候，belongsTo才是一对一关系，当对数据修改，或者删除的时候，被belongsTo的从Model是不会受到影响的。
	 */
	 
	public function belongsTo($toTable,$fromKeys=self::youKnow,$toKeys=self::youKnow){
		return $this->createAssociation(Association::belongsTo,$toTable,$fromKeys,$toKeys);
	}
	
	/**
	 * @return Association
	 * @wiki /MVC模式/数据库模型/数据表关联
	 * ==hasAndBelongsToMany==
	 *
	 * 多对多关系。这里举一个简单的例子，一个作家会有很多个作品，一个作品有时候往往会有多个作家一起编写，这就产生了多对多的关系。
	 * 多对多的关系的建立要有一个中间的表，这里我们吧中间表称为桥接表,桥接表一方面记录着作家的关键字段，另一方面又记录着作品的关键字段
	 * 通过对桥接表的关联，来对数据进行操作。
	 */
	
	public function hasAndBelongsToMany($toTable,$sBridgeTableName,$fromKeys=self::youKnow,$toBridgeKeys=self::youKnow,$fromBridgeKeys=self::youKnow,$toKeys=self::youKnow){
		return $this->createAssociation(Association::hasAndBelongsToMany,$toTable,$fromKeys,$toKeys,$sBridgeTableName,$toBridgeKeys,$fromBridgeKeys);
	}
	
	/**
	 * $toTable 可以是一个字符串，也可以是一个Prototype对象，表示关联的表。
	 * @return Association
	 */
	public function createAssociation($nType,$to,$fromKeys=self::youKnow,$toKeys=self::youKnow,$sBridgeTable=null,$toBridgeKeys=self::youKnow,$fromBridgeKeys=self::youKnow)
	{
		if(is_string($to))
		{
			$aToPrototype = self::create($to,self::youKnow,'*',$this->db()) ;
		}
		else if( $to instanceof Prototype)
		{
			$aToPrototype = $to ;
			
			if($aToPrototype -> aAssociationBy !== null)
			{
				throw new Exception('函数 Prototype::createAssociation() 的参数 $to 已经被关联，不能再关联到其他原型');
			}
		}
		else
		{
			throw new Exception('函数 Prototype::createAssociation() 的参数 $to 必须是数据表名称或Prototype对象');
		}
		
		$aAsso = new Association(
				$this->db()
				, $nType
				, $this 
				, $aToPrototype
				, $fromKeys
				, $toKeys
				, $sBridgeTable
				, $toBridgeKeys
				, $fromBridgeKeys
		) ;

		$this->arrAssociations[] = $aAsso;
		$aToPrototype->aAssociationBy = $aAsso;
		
		return $aAsso->toPrototype();
	}
	
	/**
	 * @return Association
	 */
	public function associationByName($sName)
	{
		foreach($this->arrAssociations as $aAssoc)
		{
			if($aAssoc->name()==$sName)
			{
				return $aAssoc ;
			}
		}
	}
	
	/**
	 * @return Prototype
	 */
	public function removeAssociation($aAssociation)
	{
		$key=array_search($aAssociation,$this->arrAssociations,true);
		if($key!==false)
		{
			unset($this->arrAssociations[$key]);
		}
		return $this;
	}
	/**
	 * @return Prototype
	 */
	public function clearAssociations()
	{
		$this->arrAssociations=array();
		return $this;
	}
	public function associationNames($nType=Association::total)
	{
		$arrAssocNames = array();
		foreach($this->arrAssociations as $ass)
		{
			if( $nType==Association::total or $ass->isType($nType) )
			{
				$arrAssocNames[] = $ass->toPrototype()->name() ;
			}
		}
		return $arrAssocNames ;		
	}
	public function associationIterator($nType=Association::total)
	{
		$arrAssocs = array();
		foreach($this->arrAssociations as $ass)
		{
			if($ass->isType($nType))
			{
				$arrAssocs[] = $ass;
			}
		}
		return new \ArrayIterator($arrAssocs);
	}
	
	// done and check
	/**
	 * @return Prototype
	 */
	public function done()
	{
		$this->isValid() ;
		
		// 
		if( $aAssociatedBy=$this->associatedBy() )
		{
			$aAssociatedBy->done() ;
		}
		
		return $aAssociatedBy? $this->associatedBy()->fromPrototype(): $this ;
	}
	
	public function isValid()
	{
		// 检查 table
		if(!$this->tableName())
		{
			throw new Exception('ORM原型table属性不能为空。');
		}
		
		// 检查主键
		if(!$this->keys())
		{
			throw new Exception('ORM原型 %s (db table:%s)的主键不能为空; ORM原型既没有设置主键，也无法通过数据表反射到主键。',array($this->path(),$this->tableName()));
		}
		
		// 检查同名的关联原型
		$arrPrototypeNames = array() ;
		foreach($this->associationIterator() as $aAssoc)
		{
			$sPrototypeName = $aAssoc->toPrototype()->name() ;
			if( array_key_exists($sPrototypeName,$arrPrototypeNames) )
			{
				throw new Exception("ORM原型(%s)中配置了同名的关联原型：%s;",array($this->path(),$sPrototypeName)) ;
			}
			$arrPrototypeNames[] = $sPrototypeName ;
		}
	}
	
	/**
	 * 
	 * @param bool 	$bFull		是否省略关系片段中的第一个原型的名称
	 */
	public function path($bFull=true)
	{
		if( !$this->sPathCache )
		{
			$this->sPathCache = '' ;
			
			if($aAssoc=$this->associatedBy())
			{
				$this->sPathCache = $aAssoc->fromPrototype()->path() ;
			}
			
			if($bFull)
			{
				if($this->sPathCache)
				{
					$this->sPathCache.= '.' ;
				}
				$this->sPathCache.= $this->name() ;
			}
		}
				
		return $this->sPathCache ;
	}
	
	/**
	 * @return org\jecat\framework\db\reflecter\AbStractTableReflecter
	 */
	public function tableReflecter()
	{
		$aTableReflecter = $this->db()->reflecterFactory()->tableReflecter($this->sTableName) ;
		
		if( !$aTableReflecter->isExist() )
		{
			throw new Exception('ORM原型(%s)的数据表表名无效：%s',array($this->path(),$this->tableName())) ;
		}
		
		return $aTableReflecter ;
	}
	
	/**
	 * @return org\jecat\framework\db\reflecter\AbStractColumnReflecter
	 */
	public function columnReflecter($sColumn)
	{
		$sColumn = $this->getColumnByAlias($sColumn)?: $sColumn ;
		
		$aColumnReflecter = $this->db()->reflecterFactory()->columnReflecter($this->sTableName,$sColumn) ;
		
		if( !$aColumnReflecter->isExist() )
		{
			throw new Exception('ORM原型(%s)的数据表字段无效：%s.%s',array($this->path(),$this->tableName(),$sColumn)) ;
		}
		
		return $aColumnReflecter ;
	}
	
	// for sql statement
	public function sqlTableAlias()
	{
		return $this->path() ;
	}
	public function sqlColumnAlias($sColumnName)
	{
		$sTableAlias = $this->sqlTableAlias() ;
		return ($sTableAlias?$sTableAlias.'.':'').$sColumnName ;
	}
	
	/**
	 * @return org\jecat\framework\mvc\model\db\Model
	 */
	public function createModel($bList=false)
	{
		if($bList)
		{
			return new ModelList($this) ;			
		}
		else 
		{
			$sModelClass = $this->modelClass() ;
			return new $sModelClass($this)  ;
		}
	}
	public function modelClass()
	{
		if(!$this->sModelClass)
		{
			$this->sModelClass = self::modelShadowClassName($this->tableName()) ;
		}
		return $this->sModelClass ;
	}
	
	static public function modelShadowClassName($sTableName)
	{
		$sModelClass = self::MODEL_IMPLEMENT_CLASS_NS .'\\_'.preg_replace('/[^\\w_]/','_',$sTableName) ;
		return class_exists($sModelClass)? $sModelClass: 'org\\jecat\\framework\\mvc\\model\\db\\Model' ;
	}
	
	static public function prototypeShadowClassName($sTableName)
	{
		$sClass = self::PROTOTYPE_IMPLEMENT_CLASS_NS .'\\_'.preg_replace('/[^\\w_]/','_',$sTableName) ;
		return class_exists($sClass)? $sClass: __CLASS__ ;
	}
	
	// criteria setter
	/**
	 * @return Prototype
	 */
	public function setLimit($nLen,$nFrom=0)
	{
		$this->criteria(true)->setLimit($nLen,$nFrom) ;
		return $this ;
	}
	
	/**
	 * @return Prototype
	 */
	public function addOrderBy($sColumnName,$bAsc=true)
	{
		$this->criteria(true)->orders(true)->add($sColumnName,$bAsc) ;
		return $this ;
	}
	
	/**
	 * @return Association
	 */
	public function associationBy()
	{
		return $this->aAssociationBy ;
	}
	public function setAssociationBy(Association $aAssociationBy)
	{
		return $this->aAssociationBy = $aAssociationBy ;
	}
	
	// implements IBean
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		// 根据 table 自动生成影子class
		if( !empty($arrConfig['table']) )
		{
			$sClass = self::prototypeShadowClassName($arrConfig['table']) ;
		}
		
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
 	 * @wiki /MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)
	 * ==模型的创建==
	 * 模型的创建有两中方式，基于bean的创建以及基于原型(ProtoType)的直接创建,两中方式的创建原理其实都是基于原型的创建.
	 * 
	 * 
	 * =Bean配置数组=
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
	 * [example title="/MVC模式/数据库模型/模型的基本操作(新建、保存、删除、加载)/新建(Bean)"]
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if(!empty($arrConfig['model-class']))
		{
			$this->sModelClass = $arrConfig['model-class'] ;
		}
		
		// table
		if( !empty($arrConfig['table']) )
		{
			$this->setTableName($arrConfig['table']) ;
		}
		else if( !empty($arrConfig['name']) )
		{
			$this->setTableName($arrConfig['name']) ;
		}
		
		// name
		if( !empty($arrConfig['name']) )
		{
			$this->setName($arrConfig['name']) ;
		}
		else if( !empty($arrConfig['table']) )
		{
			$this->setName($arrConfig['table']) ;
		}
		
		// columns
		if( key_exists('columns',$arrConfig) )
		{
			call_user_func(array($this,'addColumns'),$arrConfig['columns']) ;
		}
		// keys
		if( !empty($arrConfig['keys']) )
		{
			$this->setKeys($arrConfig['keys']) ;
		}
		// alias
		if( !empty($arrConfig['alias']) )
		{
			$this->addColumnAlias($arrConfig['alias']) ;
		}
		// limit ----------------
		if( !empty($arrConfig['limit']) )
		{
			$this->nLimitLen = $arrConfig['limit'] ;
		}
		// limitLen
		if( !empty($arrConfig['limitLen']) )
		{
			$this->nLimitLen = $arrConfig['limitLen'] ;
		}
		// limitFrom
		if( !empty($arrConfig['limitFrom']) )
		{
			$this->limitFrom = $arrConfig['limitFrom'] ;
		}
		if($this->nLimitLen===-1)
		{
			$this->criteria()->clearLimit() ;
		}
		else 
		{
			$this->criteria()->setLimit($this->nLimitLen,$this->limitFrom) ;
		}
		
		// order ----------------
		if( !empty($arrConfig['order']) )
		{
			foreach((array)$arrConfig['order'] as $sColumn)
			{
				list($sTable,$sColumn) = SQL::splitColumn($sColumn) ;
				$this->criteria()->addOrderBy($sColumn,true,$sTable) ;
			}
		}
		// orderDesc
		if( !empty($arrConfig['orderDesc']) )
		{
			foreach((array)$arrConfig['orderDesc'] as $sColumn)
			{
				list($sTable,$sColumn) = SQL::splitColumn($sColumn) ;
				$this->criteria()->addOrderBy($sColumn,true,$sTable) ;
			}
		}
		// orderAsc
		if( !empty($arrConfig['orderAsc']) )
		{
			foreach((array)$arrConfig['orderAsc'] as $sColumn)
			{
				list($sTable,$sColumn) = SQL::splitColumn($sColumn) ;
				$this->criteria()->addOrderBy($sColumn,false,$sTable) ;
			}
		}
		// orderRand
		if( !empty($arrConfig['orderRand']) && $arrConfig['orderRand']=true )
		{
			$this->criteria()->orders()->add(null,Order::rand) ;
		}
		// groupby
		if( !empty($arrConfig['groupby']) )
		{
			foreach((array)$arrConfig['groupby'] as $sColumn)
			{
				$this->criteria()->addGroupBy($sColumn);
			}
		}
		// where
		if(!empty($arrConfig['where']))
		{
			$arrRawWhere =& $this->criteria()->rawClause(SQL::CLAUSE_WHERE) ;
			$aSqlParser = BaseParserFactory::singleton()->create(true,null,'where') ;
			
			if( is_array($arrConfig['where']) )
			{
				$arrOnFactors = $arrConfig['where'] ;
				$arrRawWhere['subtree'] = $aSqlParser->parse(array_shift($arrOnFactors),true) ;
				call_user_func_array(array($this->criteria()->where(),'addFactors'), $arrOnFactors) ;
			}
			else
			{
				$arrRawWhere['subtree'] = $aSqlParser->parse($arrConfig['where'],true) ;
			}
			// self::buildBeanRestriction($arrConfig['where'],$this->criteria()->where()->createRestriction()) ;
		}
		
		// associations
		$aBeanFactory = BeanFactory::singleton() ;
		foreach($arrConfig as $sConfigKey=>&$item)
		{
			if( strpos($sConfigKey,'hasOne:')===0 )
			{
				$item['type'] = Association::hasOne ;
				$item['name'] = substr($sConfigKey,7) ;
			}
			else if( strpos($sConfigKey,'belongsTo:')===0 )
			{
				$item['type'] = Association::belongsTo ;
				$item['name'] = substr($sConfigKey,10) ;
			}
			else if( strpos($sConfigKey,'hasMany:')===0 )
			{
				$item['type'] = Association::hasMany ;
				$item['name'] = substr($sConfigKey,8) ;
			}
			else if( strpos($sConfigKey,'hasAndBelongsToMany:')===0 )
			{
				$item['type'] = Association::hasAndBelongsToMany ;
				$item['name'] = substr($sConfigKey,20) ;
			}
			else
			{
				continue ;
			}
			
			if(empty($item['class']))
			{
				$item['class'] = 'association' ;
			}
			
			$item['fromPrototype'] = $this ;
			$aAssociation = $aBeanFactory->createBean($item,$sNamespace,$aBeanFactory) ;
			unset($item['fromPrototype']) ;
			
			$aAssociation->setDB($this->db()) ;
			
			$this->arrAssociations[] = $aAssociation ;
		}
		
		$this->done() ;
		
		$this->arrBeanConfig = $arrConfig ;
	}
	
	public function beanConfig()
	{
		$this->arrBeanConfig ;
	}
	
	public function criteria($bAutoCreate=true)
	{
		if( !$this->aCriteria and $bAutoCreate )
		{
			$this->aCriteria = new Criteria() ;
		}
		return $this->aCriteria ;
	}

	public function limitLength()
	{
		return $this->nLimitLen ;
	}
	public function limitFrom()
	{
		return $this->limitFrom ;
	}
	
	// statement
	/**
	 * @return org\jecat\framework\db\sql\Insert
	 */
	public function sharedStatementInsert()
	{
		$this->aStatementInsert ;
	}
	/**
	 * @return org\jecat\framework\db\sql\Delete
	 */
	public function sharedStatementDelete()
	{
		$this->aStatementDelete ;
	}
	/**
	 * @return org\jecat\framework\db\sql\Select
	 */
	public function sharedStatementSelect()
	{
		if(!$this->aStatementSelect)
		{
			$this->aStatementSelect = Selecter::buildSelect($this) ;
			
			$arrTokenTree =& $this->aStatementSelect->rawSql() ;
			$arrTokenTree['omited_first_table'] = $this->sqlTableAlias() ;
			$arrTokenTree['omited_first_table_len'] = strlen($arrTokenTree['omited_first_table']) ;
		}
		return $this->aStatementSelect ;
	}
	/**
	 * return org\jecat\framework\db\sql\Update
	 */
	public function sharedStatementUpdate()
	{
		if( !$this->aStatementUpdate )
		{
			$this->aStatementUpdate = new Update($this->tableName()) ;

			$arrTokenTree =& $this->aStatementUpdate->rawSql() ;
			$arrTokenTree['omited_first_table'] = $this->name() ;
			$arrTokenTree['omited_first_table_len'] = strlen($arrTokenTree['omited_first_table']) ;
		}

		return $this->aStatementUpdate ;
	}
	
	
	// -----------------------------------------------
	public function serializableProperties()
	{
		return array(
			__CLASS__ => array(
				'sName' ,
				'sTableName' ,
				'arrColumns' ,
				'arrColumnAliases' ,
				'arrKeys' ,
				'sDevicePrimaryKey' ,
				'sModelClass' ,
				'nLimitLen' ,
				'limitFrom' ,
				'aCriteria' ,
				'aAssociationBy' ,
				'arrAssociations' ,
				'arrBeanConfig' ,
			)
		) ;
	}
	public function serialize ()
	{
		return ShareObjectSerializer::singleton()->serialize($this) ;
	}
	public function unserialize ($serialized)
	{
		ShareObjectSerializer::singleton()->unserialize($serialized,$this) ;
	}
	
	// constructor
	public function __construct(){}

	private function __clone()
	{
		$this->this->sPathCache = null ;
		$this->aStatementInsert = null ;
		$this->aStatementDelete = null ;
		$this->aStatementSelect = null ;
		$this->aStatementUpdate = null ;
	}
	
	public function db()
	{
		if(!$this->aDB)
		{
			$this->aDB = DB::singleton() ;
		}
		return $this->aDB ;
	}
	
	/**
	 * @return org\jecat\framework\db\sql\compiler\SqlCompiler
	 */
	static public function sqlCompiler()
	{
		if( !self::$aSqlCompiler )
		{
			$aNameCompiler = new SqlNameCompiler() ;
			$aNameCompiler->registerColumnNameTranslaters(array(__CLASS__,'translateColumnName')) ;
			
			self::$aSqlCompiler = new SqlCompiler(true) ;			
			self::$aSqlCompiler->registerTokenCompiler('column',$aNameCompiler) ;
			self::$aSqlCompiler->registerTokenCompiler('table',$aNameCompiler) ;
		}
		
		return self::$aSqlCompiler ;
	}
	
	static public function translateColumnName($sTable,$sColumn,$sAlias,array & $arrToken,array & $arrTokenTree)
	{		
		if( empty($arrToken['declare']) and !empty($arrTokenTree['omited_first_table'])
				and $arrTokenTree['omited_first_table']!=substr($sTable,0,$arrTokenTree['omited_first_table_len'])
				and '.'!=substr($sTable,$arrTokenTree['omited_first_table_len']) 
		) {
			if($sTable)
			{
				$sTable = $arrTokenTree['omited_first_table'].'.'.$sTable ;
			}
			else
			{
				$sTable = $arrTokenTree['omited_first_table'] ;
			}
		}
		
		return array( $sTable, $sColumn, $sAlias ) ;
	}
	
	// 固有属性 ----------------------------
	private $sName;// 如果不提供，用表名作名字。
	private $sTableName='';
	private $arrColumns ;
	private $arrColumnAliases = array();
	private $arrKeys ;
	private $sDevicePrimaryKey = null ;
	private $nLimitLen = 30 ;
	private $limitFrom = 0 ;
	private $aCriteria = null;
	private $arrAssociations =  array();
	private $aAssociationBy = null;
	private $sModelClass = null ;
	private $arrBeanConfig ;
	
	// 共享状态
	private $aDB ;
	private $aStatementNameTransfer ;
	
	// 临时状态(clone时重置) ----------------------------
	private $sPathCache ;
	private $aStatementInsert ;
	private $aStatementDelete ;
	private $aStatementSelect ;
	private $aStatementUpdate ;
	
	static private $aSqlCompiler ;
	
}
