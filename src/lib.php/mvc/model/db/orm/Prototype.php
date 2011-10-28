<?php
namespace jc\mvc\model\db\orm;

use jc\mvc\model\db\ModelList;
use jc\mvc\model\db\Model;
use jc\db\reflecter\AbstractReflecterFactory;
use jc\lang\Exception;
use jc\db\DB;
use jc\db\sql\StatementFactory;

class Prototype
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
			$this->aCriteria = StatementFactory::singleton()->createCriteria() ;
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
	public function addColumn($sColumnName,$_=self::youKnow)
	{
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
	public function addColumnAlias($sColumn,$sAlias)
	{
		$this->arrColumnAliases[$sAlias] = $sColumn;
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
	
	public function path()
	{
		$arrPath = array() ;
		$aPrototype = $this ;
		
		while( $aPrototype )
		{
			$arrPath[] = $aPrototype->name() ;
			
			if($aAssoc=$aPrototype->associatedBy())
			{
				$aPrototype = $aAssoc->fromPrototype() ;
			}
			else
			{
				break ;
			}
			
		}
				
		return implode('.',array_reverse($arrPath)) ;
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
		if( !$this->sSqlTableAliasCache )
		{
			$this->sSqlTableAliasCache = ($this->aAssociationBy? ($this->aAssociationBy->fromPrototype()->sqlTableAlias().'.'): '') . $this->name() ;
		}
		
		return $this->sSqlTableAliasCache ;
	}
	public function sqlColumnAlias($sColumnName)
	{
		if( !isset($this->arrSqlColumnAliasCaches[$sColumnName]) )
		{
			$this->arrSqlColumnAliasCaches[$sColumnName] = $this->sqlTableAlias() . '.' . $sColumnName ;
		}
		
		return $this->arrSqlColumnAliasCaches[$sColumnName] ;
	}
	
	/**
	 * @return jc\mvc\model\db\IModel
	 */
	public function createModel($bList=false)
	{
		return $bList? new ModelList($this): new Model($this) ;
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
	
	// private constructor
	private function __construct(){}
	
	
	// private data
	private $sName;// 如果不提供，用表名作名字。
	private $sTableName='';
	private $arrColumns ;
	private $arrColumnAliases = array();
	private $arrKeys = array();
	private $aCriteria = null;
	private $arrAssociations =  array();
	private $aAssociationBy = null;
	
	private $sSqlTableAliasCache ;
	private $arrSqlColumnAliasCaches = array() ;
	
	private $aDB ;
}
?>
