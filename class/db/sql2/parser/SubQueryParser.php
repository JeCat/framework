<?php
namespace org\jecat\framework\db\sql2\parser ;

class SubQueryParser extends AbstractParserState
{
	public function __construct(Parser $aParser)
	{
		$this->aParser = $aParser ;
	}
	
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		if($sToken==='(')
		{
			$nextToken = next($aTokenTree->arrTokenList) ;
			if($nextToken!==false)
			{
				prev($aTokenTree->arrTokenList) ;
				
				return strtolower($nextToken)==='select' ;
			}
		}

		return false ;
	}
	
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		$arrTokenSlice = self::closeTokens($aTokenTree->arrTokenList) ;
		$arrSubTree = $this->aParser->parseStatement( $arrTokenSlice ) ;
		array_unshift($arrSubTree,'(') ;
		array_push($arrSubTree,')') ;
		
		$aTokenTree->arrTree[] = array(
				'expr_type' => 'subquery' ,
				'subtree' => $arrSubTree ,
		) ;
	}
	
	public function examineStateFinish(& $sToken,TokenTree $aTokenTree)
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