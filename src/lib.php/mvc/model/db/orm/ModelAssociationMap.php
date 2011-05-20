<?php
namespace jc\mvc\model\db\orm ;

use jc\lang\Type;

use jc\lang\Exception;

use jc\util\HashTable;
use jc\lang\Object;


/**
 * 模型关系图
 * @author alee
 *
 */
class ModelAssociationMap extends Object
{
	public function __construct()
	{
		parent::__construct() ;
		
		$this->aModelPrototypes = new HashTable() ;		
	}
	
	public function addOrm(array $arrOrm,$bCheck=true)
	{
		if($bCheck)
		{
			$arrOrm = self::assertOrmValid($arrOrm) ;
		}

		$this->arrOrms[$arrOrm['name']] = $arrOrm ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function modelNameIterator()
	{
		return new \ArrayIterator(array_keys($this->arrOrms)) ;
	}
	
	public function modelPrototype($sName)
	{
		if( !$this->aModelPrototypes->has($sName) )
		{
			// return 
		}
		
		return $this->aModelPrototypes->get($sName) ;
	}
	
	public function addModelPrototype(ModelPrototype $aPrototype) 
	{
		$this->aModelPrototypes->set(
			$aPrototype->name()
			, $aPrototype
		) ;
	}
	
	public function modelPrototypes()
	{
		return $this->aModelPrototypes ;
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
	static public function assertOrmValid(array $arrOrm)
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
		
			if( !in_array($arrOrm[$sAssoType]) )
			{
				throw new Exception("orm(%s) 的 %s 属性是多项关联的聚合，必须为 array 结构；当前值的类型是：%s",$arrOrm['name'],$sAssoType,Type::reflectType($arrOrm[$sAssoType])) ;
			}
			foreach($arrOrm[$sAssoType] as &$arrAsso)
			{
				if( !in_array($arrAsso) )
				{
					throw new Exception("orm(%s)%s属性的成员必须是 array 结构，用以表示一个模型关联；当前值的类型是：%s。",$arrOrm['name'],$sAssoType,Type::reflectType($arrAsso)) ;
				}
				
				$arrAsso = self::assertOrmAssocValid($arrAsso,$sAssoType) ;
			}
		}
		
		// 可选属性
		if( empty($arrOrm['columns']) )
		{
			$arrOrm['columns'] = '*' ;
		}
		
		// 统一格式
		$arrOrm['columns'] = (array) $arrOrm['columns'] ;
		
		return $arrOrm ;
	}
	
	static public function assertOrmAssocValid(array $arrAsso,$sType)
	{
		if( in_array($sType, AssociationPrototype::allAssociationTypes()) )
		{
			throw new Exception("遇到无效的orm关联类型：%s；orm关联类型必须为：%s",$sType,implode(", ", AssociationPrototype::allAssociationTypes())) ;
		}

		// 必须属性
		if( empty($arrAsso['model']) )
		{
			throw new Exception("orm %s 关联缺少 name 属性",$sType) ;
		}
		
		if($sType==AssociationPrototype::hasAndBelongsMany)
		{
			if( empty($arrAsso['bridge']) )
			{
				throw new Exception("orm %s(%s) 缺少 bridge 属性",$sType,$arrAsso['model']) ;
			}
		}
		
		// 可选属性
		if( empty($arrAsso['prop']) )
		{
			$arrAsso['prop'] = $arrAsso['model'] ;
		}

		// 统一格式
		foreach( array('fromk','tok','bfromk','btok') as $sName )
		{
			if(!empty($arrAsso[$sName]))
			{
				$arrAsso[$sName] = (array) $arrAsso[$sName] ;
			}
		}
		
		return $arrAsso ;
	}
	
	private $arrOrms = array() ;
	
	private $aModelPrototypes ;
}

?>