<?php
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\db\sql\SQL ;

class ClauseParser extends AbstractParser
{
	public function __construct($sCommend)
	{
		$this->sCommend = strtoupper($sCommend) ;
		$this->sCommendLower = strtolower($sCommend) ;
	}

	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		if( $sToken===$this->sCommend )
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
		$aParseState->arrTree[] = $sToken ;
	}
	
	public function active(& $sToken,ParseState $aParseState)
	{
		$arrToken = array(
				'expr_type' => 'clause_' . $this->sCommendLower ,
				'subtree' => array() ,
		) ;
		
		if( $this->aDialect->isCommand($sToken) )
		{
			$aParseState->arrStatement['commend'] = $sToken ;
		}
		
		if( isset(self::$arrCommendIndexes[$this->sCommend]) )
		{
			$aParseState->arrTree[self::$arrCommendIndexes[$this->sCommend]] =& $arrToken ;
		}
		else
		{
			$aParseState->arrTree[] =& $arrToken ;
		}
		
		$this->switchToSubTree($aParseState,$arrToken['subtree']) ;
	}
	public function finish(& $sToken,ParseState $aParseState)
	{
		$this->restoreParentTree($aParseState) ;
	}
	
	private $sCommend ;
	private $sCommendLower ;
	
	static private $arrCommendIndexes = array(
			'SELECT' => SQL::CLAUSE_SELECT ,
			'FROM' => SQL::CLAUSE_FROM ,
			'WHERE' => SQL::CLAUSE_WHERE ,
			'GROUP' => SQL::CLAUSE_GROUP ,
			'ORDER' => SQL::CLAUSE_ORDER ,
			'LIMIT' => SQL::CLAUSE_LIMIT ,
	) ;	
}

?>