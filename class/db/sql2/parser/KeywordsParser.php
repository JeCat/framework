<?php
namespace org\jecat\framework\db\sql2\parser ;

class KeywordsParser extends AbstractParserState
{
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		return $this->aDialect->isFunction($sToken)
				or $this->aDialect->isOperator($sToken)
				or $this->aDialect->isReserved($sToken) ;
	}
	
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		$aTokenTree->arrTree[] = strtoupper($sToken) ;
	}
	
	public function examineStateFinish(& $sToken,TokenTree $aTokenTree)
	{
		return true ;
	}
}

?>