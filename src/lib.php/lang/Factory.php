<?php
namespace jc\lang ;

use jc\lang\Exception;

class Factory extends Object
{
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

}
?>