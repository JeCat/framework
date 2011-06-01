<?php

namespace jc\lang ;

class Assert
{
	static public function enable($bEnable=true)
	{
		self::$bEnableAssert = $bEnable? true: false ;
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
	
	
	static private $bEnableAssert = false ;
}

?>