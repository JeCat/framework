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
			$arrOrm = ModelPrototype::assertCnfValid($arrOrm) ;
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
				throw new Exception("正在请求一个无效的orm 模型原型(name：%s)",$sName) ;
			}
			$arrOrm = $this->arrOrms[$sName] ;

			// 创建/保存 模型原型
			$aPrototype = ModelPrototype::createFromCnf($arrOrm,false,false) ;
			$this->aModelPrototypes->set($sName,$aPrototype) ;
			
			// 为模型原型 创建关联原型
			foreach(AssociationPrototype::allAssociationTypes() as $sAssoType)
			{
				if( !empty($arrOrm[$sAssoType]) )
				{
					foreach($arrOrm[$sAssoType] as $arrAsso)
					{
						$aAssociation = AssociationPrototype::createFromCnf($arrAsso,$aPrototype,$sAssoType,false) ;
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
	
	public function fragment($sPrototypeName,array $arrAssocFragment=array(),$bRetPrototype=true)
	{
		if( !isset($this->arrOrms[$sPrototypeName]) )
		{
			throw new Exception("正在请求不存在的模型原型：%s",$sPrototypeName) ;
		}
		
		// 将 $arrAssocFragment 整理成易于访问的架构
		foreach( $arrAssocFragment as $key=>$item )
		{
			if( is_string($item) )
			{
				unset( $arrAssocFragment[$key] ) ;
				$key = $item ;
				$arrAssocFragment[$key] = array() ;
			}
			
			if( !is_array($arrAssocFragment[$key]) )
			{
				throw new Exception('从原型%s中截取关系片段时，遇到无效的原型关系片段设定：%s',array($sPrototypeName,$key)) ;
			}
		}
		
		$arrCnf = $this->arrOrms[$sPrototypeName] ;
		
		foreach(AssociationPrototype::allAssociationTypes() as $sAssoType)
		{
			if( empty($arrCnf[$sAssoType]) )
			{
				continue ;
			}
			
			foreach($arrCnf[$sAssoType] as $nAssocIdx=>$arrAssocCnf)
			{
				if( array_key_exists($arrAssocCnf['prop'],$arrAssocFragment) )
				{
					// 递归关联
					$arrCnf[$sAssoType][$nAssocIdx]['model']
							 = $this->fragment($arrAssocCnf['model'],$arrAssocFragment[$arrAssocCnf['prop']],false) ;

					unset($arrAssocFragment[$arrAssocCnf['prop']]) ;
				}

				else 
				{
					unset($arrCnf[$sAssoType][$nAssocIdx]) ;
				}
			}
			
			if( empty($arrCnf[$sAssoType]) )
			{
				unset($arrCnf[$sAssoType]) ;
			}
		}
		
		if(!empty($arrAssocFragment))
		{
			throw new Exception(
					'从无法从原型%s中截取指定的关系片段：%s'
					, array( $sPrototypeName, implode(',',array_keys($arrAssocFragment)) )
			) ;
		}
		
		if($bRetPrototype)
		{
			return ModelPrototype::createFromCnf($arrCnf,false) ;
		}
		else 
		{
			return $arrCnf ;
		}
	}
	
	private $arrOrms = array() ;
	
	private $aModelPrototypes ;
}

?>