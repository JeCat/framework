<?php
namespace org\jecat\framework\db\sql2\parser ;

use org\jecat\framework\lang\Type;

class TableJoinParser extends AbstractParserState
{
	
	public function examineStateChange(& $sToken,TokenTree $aTokenTree)
	{
		return strtoupper($sToken)==='JOIN' ;
	}
	
	public function processToken($sToken,TokenTree $aTokenTree)
	{
		$aTokenTree->arrTree[] = $sToken ;
	}
	
	public function active(& $sToken,TokenTree $aTokenTree)
	{				
		// 追溯对应的 table
		list($arrTableToken,$sJoinType) = $this->findJoinedTable($sToken,$aTokenTree) ;
		
		// 删除已经处理过的  left/right ... token
		if( $sJoinType )
		{
			array_pop($aTokenTree->arrTree) ;
		}
		
		$arrJoinToken = array(
				'expr_type' => 'join_expression' ,
				'type' => $sJoinType ,
				'subtree' => $sJoinType? array($sJoinType): array() ,
		) ;
		
		$aTokenTree->arrTree[] =& $arrJoinToken ;
		
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
	
	private function findJoinedTable(& $sToken,TokenTree $aTokenTree)
	{
		$sJoinType = null ;		
		for( end($aTokenTree->arrTree); $prevToken=current($aTokenTree->arrTree); prev($aTokenTree->arrTree) )
		{
			if( is_string($prevToken) )
			{
				if( $this->dialect()->isJoinType(strtolower($prevToken)) )
				{
					if($sJoinType)
					{
						throw new SqlParserException($aTokenTree, "重复定义 join 的类型:%s", $prevToken) ;
					}
					
					$sJoinType = $prevToken ;
				}
				else
				{
					throw new SqlParserException($aTokenTree, "join 前出现无效的内容:%s", $prevToken) ;
				}
			}
			
			else if( !is_array($prevToken) )
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
						return array($prevToken,$sJoinType) ;
						break ;
						
					case 'join' :	// nothing todo
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