<?php
namespace jc\mvc\model\db\orm ;

use jc\io\IOutputStream;
use jc\lang\Assert;
use jc\db\DB;
use jc\lang\Type;
use jc\lang\Exception;
use jc\util\HashTable;
use jc\pattern\composite\Container;
use jc\lang\Object;

class Prototype extends Object implements \Serializable
{
	public function __construct($sName,$sTable,$primaryKeys=null,array $arrClms=null)
	{
		$this->sName = $sName ;
		
		$arr = explode('.', $sTable) ;
		if(count($arr)==2)
		{
			$this->sDatabaseName = $arr[0] ;
		}
		
		$this->sTableName = $sTable ;
		
		$this->arrPrimaryKeys = (array)$primaryKeys ;
		
		$this->arrClms = (array)$arrClms ;

		parent::__construct() ;
	}
	
	/**
	 * @return Prototype
	 */
	static function createFromCnf(array $arrCnf,$bCheckValid=true,$bCreateAssoc=true,$bInFragment=false)
	{
		if( $bCheckValid )
		{
			$arrCnf = self::assertCnfValid($arrCnf) ;
		}
		
		$sClass = $bInFragment? __NAMESPACE__.'\\PrototypeInFragment': __CLASS__ ;
		
		$aPrototype = new $sClass($arrCnf['name'],$arrCnf['table'],$arrCnf['keys'],$arrCnf['clms']) ;
		$aPrototype->sModelClass = $arrCnf['class'] ;
		
		// 通过反射设置model prototype
		if( empty($aPrototype->arrClms) or empty($aPrototype->arrPrimaryKeys) or empty($aPrototype->sDevicePrimaryKey) )
		{
			$aPrototype->reflectTableInfo(DB::singleton()) ;
		}
	
		// 为模型原型 创建关联原型
		if($bCreateAssoc)
		{
			foreach(Association::allAssociationTypes() as $sAssoType)
			{
				if( !empty($arrCnf[$sAssoType]) )
				{
					foreach($arrCnf[$sAssoType] as $arrAsso)
					{
						$aAssociation = Association::createFromCnf(
								$arrAsso, $aPrototype, $sAssoType, $bCheckValid, true
						) ;
						$aPrototype->addAssociation($aAssociation) ;
					}
				}
			}
		}
		
		return $aPrototype ;
	}

	public function serialize ()
	{
		foreach(array(
				'sName',
				'sTableName',
				'sDatabaseName',
				'sModelClass',
				'arrPrimaryKeys',
				'sDevicePrimaryKey',
				'arrClms',
				'aAssociations'
		) as $sPropName)
		{
			$arrData[$sPropName] =& $this->$sPropName ;
		}
		return serialize( $arrData ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;
				
		foreach(array(
				'sName',
				'sTableName',
				'sDatabaseName',
				'sModelClass',
				'arrPrimaryKeys',
				'sDevicePrimaryKey',
				'arrClms',
				'aAssociations'
		) as $sPropName)
		{
			if( array_key_exists($sPropName, $arrData) )
			{
				$this->$sPropName =& $arrData[$sPropName] ;
			}
		}
	}
	
	public function name()
	{
		return $this->sName ;
	}
	
	public function tableName() 
	{
		return $this->sTableName ;
	}
	public function setTableName($sTable) 
	{
		$this->sTableName = $sTable ;
	}
	
	public function databaseName() 
	{
		return $this->sDatabaseName ;
	}
	public function setDatabaseName($sDatabase) 
	{
		$this->sDatabaseName = $sDatabase ;
	}
	
	public function modelClass() 
	{
		return $this->sModelClass ;
	}
	public function setModelClass($sModelClass) 
	{
		$this->sModelClass = $sModelClass ;
	}
	
	public function devicePrimaryKey()
	{
		return $this->sDevicePrimaryKey ;
	}
	
	public function setDevicePrimaryKey($sDevicePrimaryKey)
	{
		$this->sDevicePrimaryKey = $sDevicePrimaryKey ;
	}
	
	public function primaryKeys()
	{
		return $this->arrPrimaryKeys ;
	}
	
	public function setPrimayKeys($keys)
	{
		$this->arrPrimaryKeys = (array)$keys ;
	}
	
	public function addColumn($sName)
	{
		if( !in_array($sName,$this->arrClms) )
		{
			$this->arrClms[] = $sName ;
		}
	}
	public function clearColumn()
	{
		$this->arrClms = array() ;
	}
	public function columns()
	{
		return $this->arrClms ;
	}
	/**
	 * @return jc\pattern\iterate\INonlinearIterator
	 */
	public function columnIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator($this->arrClms) ;
	}
	
	/**
	 * @return jc\util\HashTable
	 */
	public function associations($bCreate=true)
	{
		if( !$this->aAssociations and $bCreate )
		{
			$this->aAssociations = new HashTable() ;
		}
		return $this->aAssociations ;
	}

	public function addAssociation(Association $aAssociation)
	{
		$aAssociations = $this->associations(true) ;
		$aAssociations->set($aAssociation->modelProperty(), $aAssociation) ;
	}

	/** 
	 * array(
	 * 	'name' => 'xxxx' ,
	 * 	'table' => 'xxxx' ,
	 * 	'keys' => array('xxx') ,
	 * 	'columns' => array('xxx') ,
	 * 	'hasOne' => array(
	 * 		array(
	 * 			'model' => 'oooo',
	 * 			'prop' => 'oooo' ,
	 * 			'fromk' => array('xxx') ,
	 * 			'tok' => array('xxx') ,
	 * 		) ,
	 * 	) ,
	 * 	'hasAndBelongsToMany' => array(
	 * 		array(
	 * 			'model' => 'oooo',
	 * 			'fromk' => array('xxx') ,
	 * 			'tok' => array('xxx') ,
	 * 			'bridge' => 'xxx' ,
	 * 			'bfromk' => array('xxx') ,
	 * 			'btok' => array('xxx') ,
	 * 		) ,
	 * 	) ,
	 * 
	 * 
	 * )
	 */
	static public function assertCnfValid(array $arrOrm,$bNestingModel=false)
	{
		// 必须属性
		if( empty($arrOrm['table']) )
		{
			throw new Exception("orm(%s) 缺少 table 属性",$arrOrm['name']) ;
		}
		
		// 可选属性
		if( empty($arrOrm['name']) )
		{
			$arrOrm['name'] = $arrOrm['table'] ;
		}
		if( empty($arrOrm['keys']) )
		{
			$arrOrm['keys'] = array() ;
		}
		if( empty($arrOrm['clms']) )
		{
			$arrOrm['clms'] = array() ;
		}
		if( empty($arrOrm['class']) )
		{
			$arrOrm['class'] = 'jc\\mvc\\model\\db\\Model' ;
		}
	
		// 统一格式
		$arrOrm['columns'] = (array) $arrOrm['clms'] ;
		
		// 关联
		foreach(Association::allAssociationTypes() as $sAssoType)
		{
			if( empty($arrOrm[$sAssoType]) )
			{
				continue ;
			}

			if( !is_array($arrOrm[$sAssoType]) )
			{
				throw new Exception("orm(%s) 的 %s 属性是多项关联的聚合，必须为 array 结构；当前值的类型是：%s",array($arrOrm['name'],$sAssoType,Type::reflectType($arrOrm[$sAssoType]))) ;
			}
			foreach($arrOrm[$sAssoType] as &$arrAsso)
			{
				if( !is_array($arrAsso) )
				{
					throw new Exception("orm(%s)%s属性的成员必须是 array 结构，用以表示一个模型关联；当前值的类型是：%s。",array($arrOrm['name'],$sAssoType,Type::reflectType($arrAsso))) ;
				}				
				
				$arrAsso = Association::assertCnfValid($arrAsso,$sAssoType,$bNestingModel) ;
			}
		}
		
		return $arrOrm ;
	}
	
	public function reflectTableInfo(DB $aDB)
	{
		// 反射字段表 和 主键值
		$aRes = $aDB->query("show columns from ".$this->tableName()) ;
		if(!$aRes)
		{
			return false ;
		}
		
		$arrClms = array() ;
		foreach($aRes as $arrRow)
		{
			$arrClms[] = $arrRow['Field'] ;
			
			if( $arrRow['Key']=='PRI' )
			{
				if(empty($this->arrPrimaryKeys))
				{
					$this->arrPrimaryKeys = array($arrRow['Field']) ;
				}
				
				$this->sDevicePrimaryKey = $arrRow['Field'] ;
			}
		}
		
		if( empty($this->arrClms) )
		{
			$this->arrClms = $arrClms ;
		}
	}
	
	public function cloneObject(array $arrAssocs=array())
	{
		$aNewIns = clone $this ;
		
		foreach($arrAssocs as $sAssocName=>$assoc)
		{
			if( is_string($assoc) )
			{
				$sAssocName = $assoc ;
				$assoc = array() ;
			}
			
			Assert::type('array',$assoc) ;
			
			$aAssoc = $this->associations()->get($sAssocName) ;
			if( !$aAssoc )
			{
				throw new Exception("模型原型(%s)中缺少指定的关系：%s",array($this->name(),$sAssocName)) ;
			}
			$aNewAssoc = clone $aAssoc ;
			
			$aNewAssoc->setFromPrototype($aNewIns) ;
			$aNewAssoc->setToPrototype(
				$aAssoc->toPrototype()->cloneObject($assoc)		// 递归 clone 一个 model prototype
			) ;
			
			$aNewIns->addAssociation($aNewAssoc) ;
		}
		
		return $aNewIns ;
	}
	
	public function __clone()
	{
		$this->aAssociations = null ;
	}
	
	// misc
	public function printStruct(IOutputStream $aOutput=null,$nDepth=0)
	{
		if(!$aOutput)
		{
			$aOutput = $this->application()->response()->printer() ;
		}
		
		$aOutput->write( "<pre>\r\n" ) ;
		
		$aOutput->write( str_repeat("\t", $nDepth)."Prototype: " ) ;
		$aOutput->write( $this->name() ) ;
		$aOutput->write( ":\r\n" ) ;
		
		$aOutput->write( str_repeat("\t", $nDepth)."table: " ) ;
		$aOutput->write( $this->tableName()."\r\n" ) ;
						
		foreach ($this->associations() as $aAssoc) 
		{
			$aAssoc->printStruct($aOutput,$nDepth+1) ;
		}
		
		$aOutput->write( "</pre>" ) ;
	}

	private $sName ;
	
	private $sTableName ;
	
	private $sDatabaseName ;
	
	private $sModelClass = "jc\\mvc\\model\\db\\Model" ;
	
	private $arrPrimaryKeys = array() ;
	
	private $sDevicePrimaryKey ;
	
	private $arrClms = array() ;
	
	private $aAssociations ;
	
}

?>