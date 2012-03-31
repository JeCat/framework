<?php
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Type;

class TableJoinParser extends AbstractParser
{
	
	public function examineStateChange(& $sToken,ParseState $aParseState)
	{
		if($this->aDialect->isJoinType($sToken))
		{
			if(next($aParseState->arrTokenList)==='JOIN')
			{
				prev($aParseState->arrTokenList) ;
				return true ;
			}
			else
			{
				throw new SqlParserException($aParseState,"LEFT token 后面必须是 JOIN") ;
			}
		}
		else
		{
			return strtoupper($sToken)==='JOIN' ;
		}
	}
	
	public function processToken(&$sToken,ParseState $aParseState)
	{
		$aParseState->arrTree[] = $sToken ;
	}
	
	public function active(& $sToken,ParseState $aParseState)
	{
		// 追溯对应的 table
		$arrTableToken =& $this->findJoinedTable($sToken,$aParseState) ;
		
		if($sToken!=='JOIN')
		{
			$sJoinType = $sToken ;
			$sToken = next($aParseState->arrTokenList) ;
		}
		else
		{
			$sJoinType = null ;
		}
		
		$arrJoinToken = array(
				'expr_type' => 'join_expression' ,
				'type' => $sJoinType ,
				'subtree' => $sJoinType? array($sJoinType): array() ,
		) ;
		
		//$aParseState->arrTree[] =& $arrJoinToken ;
		$arrTableToken['subtree'][] =& $arrJoinToken ;
		
		$this->switchToSubTree($aParseState,$arrJoinToken['subtree']) ;
	}
	public function finish(& $sToken,ParseState $aParseState)
	{
		$this->restoreParentTree($aParseState) ;
	}
	
	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		return false ;
	}
	
	private function & findJoinedTable(& $sToken,ParseState $aParseState)
	{
		for( end($aParseState->arrTree); $prevToken=current($aParseState->arrTree); prev($aParseState->arrTree) )
		{
			if( !is_array($prevToken) )
			{
				throw new SqlParserException($aParseState, "join 前的 token 类型无效：%s",Type::reflectType($prevToken)) ;
			}
			
			else
			{
				switch ($prevToken['expr_type'])
				{
					// bingo
					case 'table' :
					case 'subquery' :
						$pos = key($aParseState->arrTree) ;
						return $aParseState->arrTree[ $pos ] ;
						break ;
						
					case 'join_expression' :	// nothing todo
						break ;
					
					default :
						break ;
				}
			}
		}

		throw new SqlParserException($aParseState, "遇到无效的join，无法为 join 子句确定对应的数据表表达式") ;
	}
}

?>