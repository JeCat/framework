<?php
namespace org\jecat\framework\db\sql2\parser ;

class TableParser extends NameParser
{
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		if($sToken==='.')
		{
			$this->processNameSeparator($sToken, $aTokenTree) ;
		}
		
		else if($sToken==='AS')
		{
			$this->processAlias($sToken, $aTokenTree) ;
		}
		
		else if( $this->dialect()->isReserved($sToken) )
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
			$aTokenTree->arrTree[] = $sToken;
		}
	}	
	
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		return parent::examineStateChange($sToken,$aTokenTree)
					or (!$this->dialect()->isReserved($sToken) and !$this->dialect()->isOperator($sToken) and $this->parseName($sToken)) ;
	}
}

?>