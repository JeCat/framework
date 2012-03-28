<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Type;

class TableJoinParser extends AbstractParserState
{
	
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		if($this->dialect()->isJoinType($sToken))
		{
			if(next($aTokenTree->arrTokenList)==='JOIN')
			{
				prev($aTokenTree->arrTokenList) ;
				return true ;
			}
			else
			{
				throw new SqlParserException($aTokenTree,"LEFT token 后面必须是 JOIN") ;
			}
		}
		else
		{
			return strtoupper($sToken)==='JOIN' ;
		}
	}
	
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		$aTokenTree->arrTree[] = $sToken ;
	}
	
	public function active(& $sToken,TokenTree $aTokenTree)
	{
		// 追溯对应的 table
		$arrTableToken =& $this->findJoinedTable($sToken,$aTokenTree) ;
		
		if($sToken!=='JOIN')
		{
			$sJoinType = $sToken ;
			$sToken = next($aTokenTree->arrTokenList) ;
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
		
		//$aTokenTree->arrTree[] =& $arrJoinToken ;
		$arrTableToken['subtree'][] =& $arrJoinToken ;
		
		$this->switchToSubTree($aTokenTree,$arrJoinToken['subtree']) ;
	}
	public function finish(& $sToken,TokenTree $aTokenTree)
	{
		$this->restoreParentTree($aTokenTree) ;
	}
	
	public function examineStateFinish(& $sToken,TokenTree $aTokenTree)
	{
		return false ;
	}
	
	private function & findJoinedTable(& $sToken,TokenTree $aTokenTree)
	{
		for( end($aTokenTree->arrTree); $prevToken=current($aTokenTree->arrTree); prev($aTokenTree->arrTree) )
		{
			if( !is_array($prevToken) )
			{
				throw new SqlParserException($aTokenTree, "join 前的 token 类型无效：%s",Type::reflectType($prevToken)) ;
			}
			
			else
			{
				switch ($prevToken['expr_type'])
				{
					// bingo
					case 'table' :
					case 'subquery' :
						$pos = key($aTokenTree->arrTree) ;
						return $aTokenTree->arrTree[ $pos ] ;
						break ;
						
					case 'join_expression' :	// nothing todo
						break ;
					
					default :
						break ;
				}
			}
		}

		throw new SqlParserException($aTokenTree, "遇到无效的join，无法为 join 子句确定对应的数据表表达式") ;
	}
}

?>