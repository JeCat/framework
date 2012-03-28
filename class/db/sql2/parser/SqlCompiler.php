<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class SqlCompiler extends Object
{
	public function compile(array & $arrTokenTree)
	{
		$sSql = '' ;
		
		foreach($arrTokenTree as &$token)
		{
			if( is_string($token) )
			{
				$sSql.= ' ' . $token ;
			}
			else if( is_array($token) and isset($token['subtree']) )
			{
				$sSql.= ' ' . $this->compile($token['subtree']) ;
			}
			else
			{
				throw new Exception("遇到类型无效的 sql token: %s",$token) ;
			}
		}
		
		return $sSql? substr($sSql,1): '' ;
	}
}

?>