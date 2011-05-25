<?php
namespace jc\mvc\model\db\orm ;

use jc\lang\Type;
use jc\lang\Exception;
use jc\util\HashTable;
use jc\pattern\composite\Container;
use jc\lang\Object;

class ModelPrototype extends Object
{
	public function __construct($sName,$sTable,$primaryKeys,$clms='*')
	{
		$this->sName = $sName ;
		
		$arr = explode('.', $sTable) ;
		if(count($arr)==2)
		{
			$this->sDatabaseName = $arr[0] ;
		}
		
		$this->sTableName = $sTable ;
		
		$this->arrPrimaryKeys = (array)$primaryKeys ;
		
		$this->arrClms = (array)$clms ;

		parent::__construct() ;
	}
	
	/**
	 * @return ModelPrototype
	 */
	static function createFromCnf(array $arrCnf,$bCheckValid=true)
	{
		if( $bCheckValid )
		{
			$arrCnf = self::assertCnfValid($arrCnf) ;
		}
		
		$aPrototype = new self($arrCnf['name'],$arrCnf['table'],$arrCnf['keys'],$arrCnf['clms']) ;
		
		// 为模型原型 创建关联原型
		foreach(AssociationPrototype::allAssociationTypes() as $sAssoType)
		{
			if( !empty($arrCnf[$sAssoType]) )
			{
				foreach($arrCnf[$sAssoType] as $arrAsso)
				{
					$aAssociation = AssociationPrototype::createFromCnf(
							$arrAsso, $aPrototype, $sAssoType, $bCheckValid
					) ;
					$aPrototype->addAssociation($aAssociation) ;
				}
			}
		}
		
		return $aPrototype ;
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
	 * @return \Iterator
	 */
	public function columnIterator()
	{
		return new \ArrayIterator($this->arrClms) ;
	}
	
	/**
	 * @return \HashTable
	 */
	public function associations($bCreate=true)
	{
		if( !$this->aAssociations and $bCreate )
		{
			$this->aAssociations = new HashTable() ;
		}
		return $this->aAssociations ;
	}

	public function addAssociation(AssociationPrototype $aAssociation)
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
	 * 	'hasAndBelongsMany' => array(
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
		if( empty($arrOrm['name']) )
		{
			throw new Exception("orm 缺少 name 属性") ;
		}
		if( empty($arrOrm['table']) )
		{
			throw new Exception("orm(%s) 缺少 table 属性",$arrOrm['name']) ;
		}
		if( empty($arrOrm['keys']) )
		{
			throw new Exception("orm(%s) 缺少 keys 属性",$arrOrm['name']) ;
		}
		
		// 关联
		foreach(AssociationPrototype::allAssociationTypes() as $sAssoType)
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
				
				$arrAsso = AssociationPrototype::assertCnfValid($arrAsso,$sAssoType,$bNestingModel) ;
				
				if( $arrAsso['model'] == $arrOrm['name'] )
				{
					throw new Exception("遇到orm 配置错误：关联的两端不能是相同的模型原型(%s)。",$arrOrm['name']) ;
				}
			}
		}
		
		// 可选属性
		if( empty($arrOrm['clms']) )
		{
			$arrOrm['clms'] = '*' ;
		}
		if( empty($arrOrm['class']) )
		{
			$arrOrm['class'] = 'jc\\mvc\\model\\db\\Model' ;
		}
		
		// 统一格式
		$arrOrm['columns'] = (array) $arrOrm['clms'] ;
		
		return $arrOrm ;
	}
	
	//////////////////////////////
	
	/**
	 * @return jc\db\sql\Insert
	 */
	public function buildSqlForInsert()
	{
		
	}
	
	/**
	 * @return jc\db\sql\Delete
	 */
	public function buildSqlForDelete()
	{
	}

	/**
	 * @return jc\db\sql\Select
	 */
	public function buildSqlForSelect()
	{
	}

	/**
	 * @return jc\db\sql\Update
	 */
	public function buildSqlForUpdate()
	{
	}
	
	private $sName ;
	
	private $sTableName ;
	
	private $sDatabaseName ;
	
	private $arrPrimaryKeys = array() ;
	
	private $arrClms = array() ;
	
	private $aAssociations ;
	
}

?>