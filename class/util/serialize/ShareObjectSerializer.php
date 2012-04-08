<?php
namespace org\jecat\framework\util\serialize ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Object;

class ShareObjectSerializer extends Object
{
	public function serialize($variable)
	{
		$this->arrSharedInstances = array() ;
		$this->arrSharedInstances['root'] = $this->serializeVariable($variable) ;
		
		return serialize($this->arrSharedInstances) ;
	}
	
	protected function & serializeVariable(& $variable)
	{
		if( is_object($variable) )
		{
			$sObjId = spl_object_hash($variable) ;
			
			if( !array_key_exists($sObjId,$this->arrSharedInstances) )
			{
				$this->arrSharedInstances[$sObjId]['class'] = get_class($variable) ;
				$this->arrSharedInstances[$sObjId]['props'] =& $this->reflectionObjectData($variable) ;
			}
			
			$variable = '~objid~:'.$sObjId ;
		}
		else if( is_array($variable) )
		{
			foreach($variable as &$item)
			{
				$this->serializeVariable($item) ;
			}	
		}
		
		return $variable ;
	}
	
	protected function & reflectionObjectData($aObject)
	{
		$arrData = array() ;
		
		// 针对实现 IIncompleteSerializable 接口的对像
		if( $aObject instanceof IIncompleteSerializable )
		{
			foreach($aObject->serializableProperties() as $sClassName=>$arrPropNames)
			{
				$aRefClass = self::shareClassReflection($sClassName) ;
				
				foreach($arrPropNames as $sPropName)
				{
					if( !$aRefClass->hasProperty($sPropName) )
					{
						throw new Exception('%s::serializableProperties() 提供了不属于类的属性名称 %s',array($aRefClass->getName(),$sPropName)) ; 
					}
					
					$aRefProp = $aRefClass->getProperty($sPropName) ;
					$aRefProp->setAccessible(true) ;
					$arrData[$sClassName][$sPropName] =& $this->serializeVariable($aRefProp->getValue($aObject)) ;
				}
			}
		}
		
		// 针对普通对像
		else
		{
			$bForParentClass = false ;
			$aRef=new \ReflectionObject($aObject) ;
			
			do  {
				$sClass = $aRef->getName() ;
								
				foreach($aRef->getProperties() as $aRefProp)
				{
					// 过滤静态属性
					if( $aRefProp->isStatic() )
					{
						continue ;
					}
					
					// 只处理父类的 private 属性
					if( !$aRefProp->isPrivate() and $bForParentClass )
					{
						continue ;
					}
					
					$aRefProp->setAccessible(true) ;
					$arrData[$sClass][$aRefProp->getName()] =& $this->serializeVariable($aRefProp->getValue($aObject)) ;
				}
				
				$bForParentClass=true ;
				
			} while( $aRef=$aRef->getParentClass() ) ;	
		}
		
		return $arrData ;
	}
	
	// ---------------------	
	
	public function unserialize($serialized,$aWakeupInstance=null)
	{
		$this->arrSharedInstances = unserialize($serialized) ;
			
		// 装配已经new出来的 空对像
		if($aWakeupInstance)
		{
			$sRootId = $this->shareObjectId($this->arrSharedInstances['root']) ;
			$this->restoreShareObject($sRootId,$aWakeupInstance) ;
		}
		// 恢复一个不存在的对像
		else
		{
			return $this->unserializeVariable($this->arrSharedInstances['root']) ;
		}
	}
	
	private function shareObjectId($variableData)
	{
		if( is_string($variableData) and substr($variableData,0,8)=='~objid~:' )
		{
			return substr($variableData,8) ;
		}
		
		return null ;
	}
	
	protected function & unserializeVariable(& $variableData)
	{
		if( $sObjId=$this->shareObjectId($variableData) )
		{
			
			if( array_key_exists($sObjId,$this->arrSharedInstances) )
			{
				if( is_array($this->arrSharedInstances[$sObjId]) )
				{
					$this->restoreShareObject( $sObjId ) ;
				}
				
				$variableData = $this->arrSharedInstances[$sObjId] ;
			}
			else
			{
				$variableData = null ;
			}
		}
		else if( is_array($variableData) )
		{
			foreach($variableData as &$item)
			{
				$this->unserializeVariable($item) ;
			}
		}
		else
		{}
	
		return $variableData ;
	}
	
	protected function restoreShareObject($sObjId,& $aWakeupInstance=null)
	{
		$arrPropsData = $this->arrSharedInstances[$sObjId] ;
		
		if(!$aWakeupInstance)
		{
			$aRefClass = self::shareClassReflection($arrPropsData['class']) ;
			$aWakeupInstance = $aRefClass->newInstance() ;
		}
		
		$this->arrSharedInstances[$sObjId] = $aWakeupInstance ;
		
		foreach( $arrPropsData['props'] as $sClassName=>&$arrProps)
		{
			$aRefClass = self::shareClassReflection($sClassName) ;
				
			foreach($arrProps as $sPropName=>&$propValue)
			{
				if( $aRefClass->hasProperty($sPropName) )
				{
					$this->unserializeVariable($propValue) ;
					
					$aRefProp = $aRefClass->getProperty($sPropName) ;
					$aRefProp->setAccessible(true) ;
					$aRefProp->setValue($aWakeupInstance,$propValue) ;
				}
			}
		}
		
		return $aWakeupInstance ;
	}
	
	/**
	 * @return \ReflectionClass
	 */
	static public function shareClassReflection($sClassName)
	{
		if( !$aRef=Object::flyweight($sClassName,false,'ReflectionClass') )
		{
			/*if( !class_exists($sClassName,true) )
			{
				throw new Exception("ShareObjectSerializer 在序列化时遇到无法加载的类：%s",$sClassName) ;
			}*/
			$aRef = new \ReflectionClass($sClassName) ;
			Object::setFlyweight($aRef,$sClassName,'ReflectionClass') ;
		}
		
		return $aRef ;
	}
	
	private $arrSharedInstances = array() ;
	
}
