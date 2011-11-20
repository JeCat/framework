<?php
namespace jc\mvc\model\db\orm;

use jc\db\sql\Statement;

use jc\db\sql\StatementState;

use jc\db\sql\name\NameTransferFactory;

use jc\mvc\model\db\orm\Association;
use jc\bean\BeanFactory;
use jc\bean\IBean;
use jc\db\sql\name\NameTransfer;
use jc\mvc\model\db\ModelList;
use jc\mvc\model\db\Model;
use jc\db\reflecter\AbstractReflecterFactory;
use jc\lang\Exception;
use jc\db\DB;
use jc\db\sql\StatementFactory;

class Prototype extends StatementFactory implements IBean
{
	const youKnow = null ;
	
	// static creator
	/**
	 * @return Prototype
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
	
	/**
	 * @return jc\db\sql\Criteria
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
	public function addColumns($sColumnName,$_=self::youKnow)
	{
		if( $this->arrColumns===self::youKnow or $this->arrColumns=='*' )
		{
			$this->arrColumns = array() ;
		}
		
		$this->arrColumns = array_merge($this->arrColumns,func_get_args()) ;
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
		return $this->createAssociation(Association::hasAndBelongsTo,$toTable,$fromKeys,$toKeys,$sBridgeTableName,$toBridgeKeys,$fromBridgeKeys);
	}
	
	/**
	 * $toTable 可以是一个字符串，也可以是一个Prototype对象，表示关联的表。
	 * @return Association
	 */
	public function createAssociation($nType,$to,$fromKeys=self::youKnow,$toKeys=self::youKnow,$sBridgeTable=null,$toBridgeKeys=self::youKnow,$fromBridgeKeys=self::youKnow)
	{
		if(is_string($to))
		{
			$aToPrototype = self::create($to,self::youKnow,'*',$this->aDB) ;
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
				$this->aDB
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
		// 检查主键
		if(!$this->keys())
		{
			throw new Exception('ORM原型(%s)的主键不能为空',$this->path());
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
	 * @return jc\db\reflecter\AbstractReflecterFactory
	 */
	public function tableReflecter()
	{
		$aTableReflecter = $this->aDB->reflecterFactory()->tableReflecter($this->sTableName) ;
		
		if( !$aTableReflecter->isExist() )
		{
			throw new Exception('ORM原型(%s)的数据表表名无效：%s',array($this->path(),$this->tableName())) ;
		}
		
		return $aTableReflecter ;
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
	 * @return jc\mvc\model\db\IModel
	 */
	public function createModel($bList=false)
	{
		return $bList? new ModelList($this): new $this->sModelClass($this) ;
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
	public function build(array & $arrConfig,$sNamespace='*')
	{
		if( !$this->aDB )
		{
			$this->aDB = DB::singleton() ;
		}
		
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
		if( !empty($arrConfig['columns']) )
		{
			call_user_func_array(array($this,'addColumns'),$arrConfig['columns']) ;
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
		// restrication
		// foreach(array('eq'))
		
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
			else if( strpos($sConfigKey,'hasAndBelongsTo:')===0 )
			{
				$item['type'] = Association::hasAndBelongsTo ;
				$item['name'] = substr($sConfigKey,16) ;
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
			
			$aAssociation = $aBeanFactory->createBean($item,$sNamespace) ;
			$aAssociation->setDB($this->aDB) ;
			
			$this->arrAssociations[] = $aAssociation ;
		}
		
		$this->done() ;
		
		$this->arrBeanConfig = $arrConfig ;
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
	 * return jc\db\sql\Update
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
	 * @return jc\db\sql\name\NameTransfer
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
	 * @return jc\db\sql\StatementFactory ;
	 */
	public function statementFactory()
	{
		return $this ;
	}
	/**
	 * @return jc\db\sql\Table ;
	 */
	public function createTable()
	{
		return parent::createTable($this->tableName(),$this->sqlTableAlias()) ;
	}
	public function statementColumnNameHandle($sName,Statement $aStatement,StatementState $sState)
	{
		// delete和Update不支持别名，不做表别名转换
		if( !$sState->supportTableAlias() )
		{
			return array($this->getColumnByAlias($sName),$aStatement,$sState) ;
		}
		
		// 切分 原型名称 和 字段名称
		$nPos = strrpos($sName,'.') ;
		if($nPos!==false)
		{
			$sTableName = '.'.substr($sName,0,$nPos) ;
			$sColumn = substr($sName,$nPos+1) ;
		}
		else 
		{
			$sTableName = null ;
			$sColumn = $sName ;
		}
		$sColumn = $this->getColumnByAlias($sColumn)?: $sColumn ;

		$sName = '`'.$this->path()."{$sTableName}`.`{$sColumn}`" ;
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
	private $sModelClass = 'jc\\mvc\\model\\db\\Model' ;
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
?>
