<?php
namespace org\jecat\framework\db\sql\parser;

class IntoParser extends ClauseParser
{
	public function __construct()
	{
		parent::__construct('into') ;
	}

	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		return count($aParseState->arrTree)>1 ;
	}
}

?>