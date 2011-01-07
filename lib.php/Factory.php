<?php
namespace jc ;

use jc\system\Exception;

class Factory extends Object
{
	/**
	 * Enter description here ...
	 * 
	 * @return stdClass
	 */
	public function create($sClassName,array $arrArgvs=array())
	{
		$sClassName = strval($sClassName) ;
		if( !class_exists($sClassName) )
		{
			throw new Exception("class无效：".$sClassName) ;
		}
		
		// 创建对象
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
		
			// 设置工厂对象
			$aObject->setFactory($this) ;
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