<?php
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\lang\Exception;

class SqlParserException extends Exception
{
	public function __construct(ParseState $aParseState,$sMessage,$argvs=null)
	{
		$argvs = Type::toArray($argvs,Type::toArray_emptyForNull) ;
		
		$sMessage.= "\r\n遇到问题的地方：%s"  ;
		$argvs[] = implode(' ',array_slice($aParseState->arrTokenList, key($aParseState->arrTokenList), 30)) ;
		
		$sMessage.= "\r\n完整的SQL：%s"  ;
		$argvs[] = implode(' ',$aParseState->arrTokenList) ;
		
		parent::__construct($sMessage,$argvs) ;
	}
}

?>