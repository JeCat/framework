<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Exception;

class SqlParserException extends Exception
{
	public function __construct(TokenTree $aTokenTree,$sMessage,$argvs=null)
	{
		parent::__construct($sMessage,$argvs) ;
	}
}

?>