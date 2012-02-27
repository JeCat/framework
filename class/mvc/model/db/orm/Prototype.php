<?php
namespace org\jecat\framework\mvc\model\db\orm;

use org\jecat\framework\db\sql\Order;
use org\jecat\framework\util\serialize\IIncompleteSerializable;
use org\jecat\framework\util\serialize\ShareObjectSerializer;
use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\lang\Type;
use org\jecat\framework\fs\FileSystem;
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

class Prototype extends StatementFactory implements IBean, \Serializable, IIncompleteSerializable
{
	const youKnow = null ;
	
	const MODEL_IMPLEMENT_CLASS_NS = 'org\\jecat\\framework\\mvc\\model\\db\\imp' ;
	const MODEL_IMPLEMENT_CLASS_BASE = 'org\\jecat\\framework\\mvc\\model\\db\\Model' ;
	static public $sModelImpPackage = '/data/class/db/model' ;
	
	const PROTOTYPE_IMPLEMENT_CLASS_NS = 'org\\jecat\\framework\\mvc\\model\\db\\prototype' ;
	const PROTOTYPE_IMPLEMENT_CLASS_BASE = __CLASS__ ;
	static public $sPrototypeImpPackage = '/data/class/db/prototype' ;
	
	
	// static creator
	/**
	 * @return Prototype
	 */
	/**
	 * @wiki /MVC模式/模型/模型(Model)
	 *
	 * {| ==原型创建==
	 *  | 原型的创建方法
	 *  |}
	 */
	static public function create( $sTableName, $keys=self::youKnow, $columns=self::youKnow , $aDB = self::youKnow )
	{
		$aPrototype = new Prototype ;
		
		$aPrototype->setTableName($sTableName) ;
		$aPrototype->setName($sTableName) ;
		$aPrototype->arrColumns = $columns ;
		$aPrototype->arrKeys = self::youKnow ;
		
		$aPrototype->aDB = $aDB ;
		
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
	
	/**
	 * @return org\jecat\framework\db\sql\Criteria
	 */
	/**
	 * @wiki /MVC模式/模型/模型(Model)
	 *
	 * {| ==原型属性的获得==
	 *  | 通过criteria（）对原型的属性进行设置，例如where
	 *  |}
	 */
	public function criteria($bCreate=true)
	{
		if( !$this->aCriteria and $bCreate )
		{
			$this->aCriteria = $this->statementFactory()->createCriteria() ;
		}
		
		return $this->aCriteria;
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
	 */
	public function hasOne($toTable,$fromKeys=self::youKnow,$toKeys=self::youKnow){
		return $this->createAssociation(Association::hasOne,$toTable,$fromKeys,$toKeys);
	}
	/**
	 * @return Association
	 */
	public function hasMany($toTable,$fromKeys=self::youKnow,$toKeys=self::youKnow){
		return $this->createAssociation(Association::hasMany,$toTable,$fromKeys,$toKeys);
	}
	/**
	 * @return Association
	 */
	public function belongsTo($toTable,$fromKeys=self::youKnow,$toKeys=self::youKnow){
		return $this->createAssociation(Association::belongsTo,$toTable,$fromKeys,$toKeys);
	}
	/**
	 * @return Association
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
	 * @return org\jecat\framework\mvc\model\db\IModel
	 */
	public function createModel($bList=false)
	{
		$sModelClass = $this->modelClass() ;
		return new $sModelClass($this,$bList) ;
	}
	public function modelClass()
	{
		if(!$this->sModelClass)
		{
			$sModelShortClass = '_'.preg_replace('[^\w_]','_',$this->tableName()) ;
			$this->sModelClass = self::MODEL_IMPLEMENT_CLASS_NS .'\\'. $sModelShortClass ;
		}
		return $this->sModelClass ;
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
			$sClassNamespace = self::PROTOTYPE_IMPLEMENT_CLASS_NS ;
			$sPackageFolder = self::$sPrototypeImpPackage ;
			
			$sShortClass = '_'.preg_replace('[^\w_]','_',$arrConfig['table']) ;
			$sClass = $sClassNamespace .'\\'. $sShortClass ;
		}
		
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /MVC模式/模型/原型(Prototype)
	 * ==Bean配置数组==
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
		// limit
		if( !empty($arrConfig['limit']) )
		{
			$this->criteria()->setLimitLen($arrConfig['limit']) ;
		}
		// limitLen
		if( !empty($arrConfig['limitLen']) )
		{
			$this->criteria()->setLimitLen($arrConfig['limitLen']) ;
		}
		// limitFrom
		if( !empty($arrConfig['limitFrom']) )
		{
			$this->criteria()->setLimitFrom($arrConfig['limitFrom']) ;
		}
		// order
		if( !empty($arrConfig['order']) )
		{
			foreach((array)$arrConfig['order'] as $sColumn)
			{
				$this->criteria()->orders()->add($sColumn,false) ;
			}
		}
		// orderDesc
		if( !empty($arrConfig['orderDesc']) )
		{
			foreach((array)$arrConfig['orderDesc'] as $sColumn)
			{
				$this->criteria()->orders()->add($sColumn,false) ;
			}
		}
		// orderAsc
		if( !empty($arrConfig['orderAsc']) )
		{
			foreach((array)$arrConfig['orderAsc'] as $sColumn)
			{
				$this->criteria()->orders()->add($sColumn,true) ;
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
			$this->buildBeanRestriction($arrConfig['where'],$this->criteria()->where()) ;
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
	
	private function buildBeanRestriction(array $arrRestrictionConfig,Restriction $aRestriction)
	{
		// 第一项 'and' 或 'or'
		$sLogic = array_shift($arrRestrictionConfig) ;
		if( $sLogic )
		{
			if(is_string($sLogic))
			{
				$aRestriction->setLogic(strtoupper($sLogic)!='or') ;
			}
			else 
			{
				array_unshift($arrRestrictionConfig,$sLogic) ;
			}
		}
		
		foreach ($arrRestrictionConfig as &$arrCondition)
		{
			if( !is_array($arrCondition) or count($arrCondition)>3 )
			{
				throw new BeanConfException('无效的orm bean config 内容：%s，做为sql条件，必须是一个数组',var_export($arrCondition,true)) ;
			}
			
			$sOperator = array_shift($arrCondition) ;
			if( !is_string($sOperator) or !method_exists($aRestriction,$sOperator))
			{
				throw new BeanConfException(
						'无效的orm bean config 内容：%s，做为sql条件，数组的第一个元素必须是一个有效的表示运算符的字符串'
						, var_export($arrCondition,true)
				) ;
			}
			$sOperator = strtolower($sOperator) ;
			
			// 递归 条件分组
			if( $sOperator=='restriction' )
			{
				$this->buildBeanRestriction($arrCondition,$aRestriction->createRestriction()) ;
			}
			else 
			{
				call_user_func_array(array($aRestriction,$sOperator),$arrCondition) ;
			}
		}
	}
	
	public function beanConfig()
	{
		$this->arrBeanConfig ;
	}
	
	// statement
	public function statementInsert()
	{
		$this->aStatementInsert ;
	}
	public function statementDelete()
	{
		$this->aStatementDelete ;
	}
	public function sharedStatementSelect()
	{
		if(!$this->aStatementSelect)
		{
			$this->aStatementSelect = Selecter::buildSelect($this) ;
		}
		return $this->aStatementSelect ;
	}
	/**
	 * return org\jecat\framework\db\sql\Update
	 */
	public function statementUpdate()
	{
		if( !$this->aStatementUpdate )
		{
			$this->aStatementUpdate = $this->statementFactory()->createUpdate($this ->tableName()) ;
		}

		return $this->aStatementUpdate ;
	}
	
	/**
	 * override parent class StatementFactory
	 */
	protected function initStatement(Statement $aStatement)
	{
		$aStatement->setNameTransfer($this->nameTransfer()) ;
		$aStatement->setStatementFactory($this) ;
		return $aStatement ;
	}

	/**
	 * @return org\jecat\framework\db\sql\name\NameTransfer
	 */
	public function nameTransfer()
	{
		if(!$this->aStatementNameTransfer)
		{
			$this->aStatementNameTransfer = NameTransferFactory::singleton()->create() ;
			$this->aStatementNameTransfer->addColumnNameHandle(array($this,'statementColumnNameHandle')) ;
		}
		return $this->aStatementNameTransfer ;
	}

	/**
	 * @return org\jecat\framework\db\sql\StatementFactory ;
	 */
	public function statementFactory()
	{
		return $this ;
	}
	/**
	 * @return org\jecat\framework\db\sql\Table ;
	 */
	public function createSqlTable()
	{
		return $this->createTable($this->tableName(),$this->sqlTableAlias()) ;
	}
	public function statementColumnNameHandle($sName,Statement $aStatement,StatementState $sState)
	{
		// delete和Update不支持别名，不做表别名转换
		if( !$sState->supportTableAlias() )
		{
			return array($this->getColumnByAlias($sName),$aStatement,$sState) ;
		}
		
		// 切分 原型名称 和 字段名称
		$sColumn = $this->getColumnByAlias($sName)?: $sName ;
		
		$nPos = strrpos($sColumn,'.') ;
		if($nPos!==false)
		{
			$sTableName = '.'.substr($sColumn,0,$nPos) ;
			$sColumn = substr($sColumn,$nPos+1) ;
		}
		else 
		{
			$sTableName = '' ;
		}

		return '`'.$this->path()."{$sTableName}`.`{$sColumn}`" ;
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
	
	// 固有属性 ----------------------------
	private $sName;// 如果不提供，用表名作名字。
	private $sTableName='';
	private $arrColumns ;
	private $arrColumnAliases = array();
	private $arrKeys ;
	private $sDevicePrimaryKey = null ;
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
	
}
