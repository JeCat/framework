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
			if(empty($this->arrOrms[$sName]))
			{
				throw new Exception("正在请求一个无效的orm 模型原型：%s",$sName) ;
			}
			$arrOrm = $this->arrOrms[$sName] ;

			// 创建/保存 模型原型
			$aPrototype = ModelPrototype::createFromCnf($arrOrm,true) ;
			$this->aModelPrototypes->set($sName,$aPrototype) ;
			
			// 为模型原型 创建关联原型
			foreach(AssociationPrototype::allAssociationTypes() as $sAssoType)
			{
				if( !empty($arrOrm[$sAssoType]) )
				{
					foreach($arrOrm[$sAssoType] as $arrAsso)
					{
						$aAssociation = AssociationPrototype::createFromCnf($arrOrm,true) ;
						$aAssociation = new AssociationPrototype(
								$sAssoType
								, $arrAsso['prop']
								, $aPrototype
								, $this->modelPrototype($arrAsso['model'])
								, $arrAsso['fromk'], $arrAsso['tok']
								, $arrAsso['bfromk'], $arrAsso['btok']
						) ;
						
						$aPrototype->addAssociation($aAssociation) ;
					}
				}
			}
			
			return $aPrototype ;
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
	
	
	
	private $arrOrms = array() ;
	
	private $aModelPrototypes ;
}

?>