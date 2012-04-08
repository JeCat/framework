<?php
namespace org\jecat\framework\db\sql\parser ;

class ColumnParser extends NameParser
{
	public function processToken(&$sToken,ParseState $aParseState)
	{
		if($sToken==='.')
		{
			$this->processNameSeparator($sToken, $aParseState) ;
		}
		
		else if($sToken==='AS')
		{
			$this->processAlias($sToken, $aParseState) ;
		}
		
		else if( $this->aDialect->isReserved($sToken) or $this->aDialect->isOperator($sToken) )
		{
			$aParseState->arrTree[] = $sToken ;
		}
		
		else if( $columnName=$this->parseName($sToken) or $sToken==='*' )
		{
			$aParseState->arrTree[] = array(
					'expr_type' => 'column' ,
					'column' => $columnName ?: $sToken ,
			) ;
		}
		
		// unknow
		else
		{
			$aParseState->arrTree[] = $sToken;
		}		
	}
	
	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		return parent::examineStateChange($sToken,$aParseState)
					or $sToken==='*'
					or (!$this->aDialect->isReserved($sToken) and !$this->aDialect->isOperator($sToken) ) ;
	}
}

?>