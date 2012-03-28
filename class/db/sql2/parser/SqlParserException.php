<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Exception;

class SqlParserException extends Exception
{
	public function __construct(TokenTree $aTokenTree,$sMessage,$argvs=null)
	{
		if( $sToken=current($aTokenTree->arrTokenList) )
		{
			$nPosToken = array_search($sToken,$aTokenTree->arrTokenList) ;
			$sMessage.= '；遇到问题的token：' . implode(' ',array_slice($aTokenTree->arrTokenList, $nPosToken, 30)) ; 	
		}
		
		parent::__construct($sMessage,$argvs) ;
	}
}

?>