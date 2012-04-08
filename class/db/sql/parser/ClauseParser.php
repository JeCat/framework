<?php
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\db\sql\SQL ;

class ClauseParser extends AbstractParser
{
	public function __construct($scommand)
	{
		$this->scommand = strtoupper($scommand) ;
		$this->scommandLower = strtolower($scommand) ;
	}

	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		if( $sToken===$this->scommand )
		{
			return true ;	
		}
		else
		{
			return false ;
		}
	}
	
	public function processToken(&$sToken,ParseState $aParseState)
	{
		if( $sToken===$this->scommand )
		{
			return ;
		}
		$aParseState->arrTree[] = $sToken ;
	}
	
	public function active(& $sToken,ParseState $aParseState)
	{
		$arrToken = array(
				'expr_type' => 'clause_' . $this->scommandLower ,
				'pretree' => array($this->scommand) ,
				'subtree' => array() ,
		) ;
		
		if( $this->aDialect->isCommand($sToken) )
		{
			$aParseState->arrStatement['command'] = $sToken ;
		}
		
		if( ($nIdx=array_search($this->scommand,SQL::$mapClauses))!==false )
		{
			$aParseState->arrTree[$nIdx] =& $arrToken ;
		}
		else
		{
			$aParseState->arrTree[] =& $arrToken ;
		}
		
		$this->switchToSubTree($aParseState,$arrToken) ;
	}
	public function finish(& $sToken,ParseState $aParseState)
	{
		$this->restoreParentTree($aParseState) ;
	}
	
	private $scommand ;
	private $scommandLower ;
	
}

?>