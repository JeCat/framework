<?php
namespace jc\lang ;

use jc\lang\Exception;

class Factory extends Object
{
	/**
	 * Enter description here ...
	 * 
	 * @return stdClass
	 */
	public function create($sClassName,array $arrArgvs=array())
	{
		// 创建对象
		$aObject = self::createNewObject($sClassName,$arrArgvs) ;
				
		// 设置工厂对象
		if( $aObject instanceof Object)
		{
			$aObject->setFactory($this) ;
		}
		
		return $aObject ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	static public function createNewObject($sClassName,array $arrArgvs=array())
	{
		$sClassName = strval($sClassName) ;
		if( !class_exists($sClassName) )
		{
			throw new Exception("class无效：".$sClassName) ;
		}
		
		$aObject = new $sClassName ;
		
		if( $aObject instanceof Object )
		{
			// 初始化对象
			if(count($arrArgvs))
			{
				if( call_user_func_array(array($aObject,'initialize'),$arrArgvs)===false )
				{
					return null ;
				}
			}
		}
		
		return $aObject ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return Factory
	 */
	public function rootFactory()
	{
		$aFactory = $this ;
		
		while( $aParentFactory=$aFactory->factory() )
		{
			$aFactory = $aParentFactory ;
		}
		
		return $aFactory ;
	}
}
?>