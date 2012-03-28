<?php
namespace org\jecat\framework\db\sql2\parser ;

class ColumnParser extends NameParser
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
		
		else if( $columnName=$this->parseName($sToken) or $sToken==='*' )
		{
			$aTokenTree->arrTree[] = array(
					'expr_type' => 'column' ,
					'column' => $columnName ?: $sToken ,
					'subtree' => array($sToken)
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
					or $sToken==='*'
					or (!$this->dialect()->isReserved($sToken) and !$this->dialect()->isOperator($sToken) ) ;
	}
}

?>