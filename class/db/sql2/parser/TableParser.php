<?php
namespace org\jecat\framework\db\sql2\parser ;

class TableParser extends AbstractParserState
{
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		if( $this->dialect()->isReserved($sToken) )
		{
			$aTokenTree->arrTree[] = strtoupper($sToken) ;
		}
		
		else if( $sTableName = $this->parseName($sToken) )
		{
			$aTokenTree->arrTree[] = array(
					'expr_type' => 'table' ,
					'table' => $sTableName ,
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
		return !$this->dialect()->isReserved($sToken) and !$this->dialect()->isOperator($sToken) and $this->parseName($sToken) ;
	}
	
	public function examineStateFinish(& $sToken,TokenTree $aTokenTree)
	{
		return true ;
	}
}

?>