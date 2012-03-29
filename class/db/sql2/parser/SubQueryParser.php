<?php
namespace org\jecat\framework\db\sql2\parser ;

class SubQueryParser extends AbstractParser
{
	public function __construct(AbstractParser $aParser)
	{
		$this->aParser = $aParser ;
	}
	
	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		if($sToken==='(')
		{
			$nextToken = next($aParseState->arrTokenList) ;
			if($nextToken!==false)
			{
				prev($aParseState->arrTokenList) ;
				
				return strtolower($nextToken)==='select' ;
			}
		}

		return false ;
	}
	
	public function processToken($sToken,ParseState $aParseState)
	{
		$arrTokenSlice = self::closeTokens($aParseState->arrTokenList) ;
		$arrSubTree = $this->aParser->parseStatement( $arrTokenSlice ) ;
		array_unshift($arrSubTree,'(') ;
		array_push($arrSubTree,')') ;
		
		$aParseState->arrTree[] = array(
				'expr_type' => 'subquery' ,
				'subtree' => $arrSubTree ,
		) ;
	}
	
	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		return true ;
	}
	
	static public function closeTokens(array & $arrTokenList)
	{
		if( current($arrTokenList) !== '(' )
		{
			return array() ;
		}
		
		$arrTokenSlice = array() ; 
		$nDepth = 0 ;
		for( ; $sToken=current($arrTokenList); next($arrTokenList) )
		{
			if(!$sToken)
			{
				return $arrTokenSlice ;
			}			
						
			if( $sToken==='(' )
			{
				$nDepth ++ ;
			}
			else if( $sToken===')' )
			{
				$nDepth -- ;
				
				if($nDepth<1)
				{
					return $arrTokenSlice ;
				}
			}
			
			else
			{
				$arrTokenSlice[] = $sToken ;
			}
		}
	}
	
	private $aParser ;
}

?>