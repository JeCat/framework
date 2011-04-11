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
	public function create($sClassName,$sNamespace='\\',array $arrArgvs=array())
	{		
		// 创建对象
		$aObject = self::createNewObject($sClassName,$sNamespace,$arrArgvs) ;
				
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
	static public function createNewObject($sClassName,$sNamespace,array $arrArgvs=array())
	{
		$sFullClassName = $sNamespace . (substr($sNamespace,strlen($sNamespace)-1,1)=='\\'? '': '\\') . $sClassName ;
		
		if( !class_exists($sFullClassName) )
		{
			throw new Exception("class无效：".$sFullClassName) ;
		}
		
		$arrArgvs = array_values($arrArgvs) ;
		$arrArgNameList = array() ;
		foreach($arrArgvs as $sKey=>$Item)
		{
			$arrArgNameList[] = "\$arrArgvs[$sKey]" ;
		}
		$sArgList = implode(', ',$arrArgNameList) ;
		
		
		return eval("return new {$sFullClassName}({$sArgList}) ;") ;
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