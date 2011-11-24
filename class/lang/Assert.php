<?php

namespace org\jecat\framework\lang ;

class Assert
{
	static public function enable($bEnable=true)
	{
		self::$bEnableAssert = $bEnable? true: false ;
	}

	static public function notNull($Types,$sMessage=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		if($Types===null)
		{
			if( !$sMessage )
			{
				$sMessage = "程序中的某个位置触发了异常：表达式的值不应该为 null " ;
			}
			throw new Exception($sMessage) ;
		}
	}

	static public function isNull($Types,$sMessage=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		if($Types!==null)
		{
			if( !$sMessage )
			{
				$sMessage = "程序中的某个位置触发了异常：表达式的值不是预期的 null " ;
			}
			throw new Exception($sMessage) ;
		}
	}
	
	static public function wrong($sMessage=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		if( !$sMessage )
		{
			$sMessage = "程序中的某个位置触发了异常" ;
		}
		throw new Exception($sMessage) ;
	}
	
	static public function type($Types,& $Variable,$sVarName=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		$Types = (array) $Types ;
		if( !Type::check($Types,$Variable) )
		{
			throw new TypeException($Variable,$Types,$sVarName) ;
		}
	}
	
	
	static private $bEnableAssert = true ;
}

?>