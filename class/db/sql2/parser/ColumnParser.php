<?php
namespace org\jecat\framework\db\sql2\parser ;

class ColumnParser extends AbstractParserState
{
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		if( $columnName=$this->parseName($sToken) or $sToken==='*' )
		{
			$aTokenTree->arrTree[] = array(
					'expr_type' => 'column' ,
					'column' => $columnName ?: $sToken ,
			) ;
		}
		
		// unknow
		else
		{
			$aTokenTree->arrTree[] = array(
					'expr_type' => 'unknow' ,
					'subtree' => array( $sToken ) ,
			) ;
		}		
	}
	
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		return $sToken==='*' or (!$this->dialect()->isReserved($sToken) and !$this->dialect()->isOperator($sToken) ) ;
	}
	
	public function examineStateFinish(& $sToken,TokenTree $aTokenTree)
	{
		return true ;
	}
}

?>