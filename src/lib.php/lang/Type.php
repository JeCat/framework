<?php

namespace jc\lang ;

class Type
{
	/*const STRING = 1 ;
	const INT = 2 ;
	const FLOAT = 4 ;
	const NUMERIC = 6 ;
	const BOOL = 8 ;
	const NULL = 16 ;
	const RESOURCE = 32 ;
	const MAP = 64 ;
	const ARR = 64 ;
	const OBJECT = 128 ;*/

	const STRING = "string" ;
	const INT = "int" ;
	const FLOAT = "float" ;
	const NUMERIC = "numeric" ;
	const ARR = "array" ;
	const MAP = "array" ;
	const BOOL = "bool" ;
	const NULL = "null" ;
	const RESOURCE = "resource" ;
	const OBJECT = "object" ;
	
	static private $arrTypes = array(
			self::STRING ,
			self::INT ,
			self::FLOAT ,
			self::NUMERIC ,
			self::BOOL ,
			self::NULL ,
			self::RESOURCE ,
			self::ARR ,
			self::OBJECT ,
	) ;
	
	
	static public function check($Types,& $Variable,$bThrowException=true,$sVarName=null)
	{
		if( is_string($Types) )
		{
			$Types = array($Types) ;
		}
		else if( !is_array($Types) )
		{
			throw new Exception('参数错误：$Types传入的数据类型无效：%s。',$Types) ;
		}
		
		$sVarType = self::reflectType($Variable) ;

		foreach($Types as $RequireType) 
		{
			// 基本类型
			if( in_array($sVarType,self::$arrTypes) )
			{
				if($sVarType===$RequireType)
				{
					return true ;
				}
			}
			
			// 
			else
			{
				if( is_a($Variable, $RequireType) )
				{
					return true ;
				}
			}
		}
		
		if($bThrowException)
		{
			throw new TypeException(&$Variable,$Types,$sVarName) ;
		}
		
		return false ;
	}
	
	static public function reflectType($Variable)
	{
		$sType = self::detectType($Variable) ;
		if( $sType==self::OBJECT )
		{
			return get_class($Variable) ;
		}
		else
		{
			return $sType ;
		}
	}
	
	static public function detectType($Variable)
	{
		if( is_string($Variable) )
		{
			return self::STRING ;
		}
		else if( is_int($Variable) )
		{
			return self::INT ;
		}
		else if( is_float($Variable) )
		{
			return self::FLOAT ;
		}
		else if( is_array($Variable) )
		{
			return self::ARR ;
		}
		else if( is_bool($Variable) )
		{
			return self::BOOL ;
		}
		else if( is_resource($Variable) )
		{
			return self::RESOURCE ;
		}
		else if( is_object($Variable) )
		{
			return self::OBJECT ;
		}
	}
}

?>