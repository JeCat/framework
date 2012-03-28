<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\lang\Exception;

class SqlParserException extends Exception
{
	public function __construct(TokenTree $aTokenTree,$sMessage,$argvs=null)
	{
		$argvs = Type::toArray($argvs,Type::toArray_emptyForNull) ;
		
		$sMessage.= "\r\n遇到问题的地方：%s"  ;
		$argvs[] = implode(' ',array_slice($aTokenTree->arrTokenList, key($aTokenTree->arrTokenList), 30)) ;
		
		$sMessage.= "\r\n完整的SQL：%s"  ;
		$argvs[] = implode(' ',$aTokenTree->arrTokenList) ;
		
		parent::__construct($sMessage,$argvs) ;
	}
}

?>