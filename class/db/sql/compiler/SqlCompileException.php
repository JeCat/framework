<?php
namespace org\jecat\framework\db\sql\compiler ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\lang\Exception;

class SqlCompileException extends Exception
{
	public function __construct(&$arrTokenTree,&$token,$sMessage,$argvs=null)
	{
		$argvs = Type::toArray($argvs,Type::toArray_emptyForNull) ;
		
		$sMessage.= "\r\n 正在处理的Sql Raw：" . var_export($arrTokenTree,true) ;
		
		parent::__construct($sMessage,$argvs) ;
	}
}

?>